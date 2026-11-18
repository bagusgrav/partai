<?php

namespace App\Services;

use App\Models\BureaucracyReport;
use App\Models\PublicAgencyScorecard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BureaucracyWatchService
{
    public function calculateScore(float $rbIndex, float $meritSystem, float $ombudsmanCompliance): array
    {
        $meritNormalized = max(0, min(100, $meritSystem / 400 * 100));
        $score = round($rbIndex * 0.35 + $ombudsmanCompliance * 0.35 + $meritNormalized * 0.30, 2);

        return [
            'bureaucracy_integrity_score' => $score,
            'performance_tier' => match (true) {
                $score >= 80 => 'sangat_baik',
                $score >= 65 => 'baik',
                $score >= 50 => 'perlu_perbaikan',
                default => 'kritis',
            },
        ];
    }

    public function createReport(array $payload): BureaucracyReport
    {
        $reporterSecret = Str::random(32);
        $ticketCode = $this->generateTicketCode();
        $payloadHash = $payload['payload_hash'] ?? hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $report = DB::transaction(function () use ($payload, $payloadHash, $reporterSecret, $ticketCode): BureaucracyReport {
            $report = new BureaucracyReport;
            $report->forceFill([
                ...$payload,
                'ticket_code' => $ticketCode,
                'reporter_token_hash' => hash('sha256', $reporterSecret),
                'payload_hash' => $payloadHash,
                'verification_status' => 'submitted',
            ]);
            $report->save();

            if ($report->agency_id !== null) {
                PublicAgencyScorecard::query()->whereKey($report->agency_id)->increment('open_complaints_count');
            }

            $report->setAttribute('reporter_secret', $reporterSecret);

            return $report;
        });

        return $report;
    }

    public function changeVerificationStatus(BureaucracyReport $report, string $newStatus, array $updates = [], ?string $oldStatus = null): BureaucracyReport
    {
        return DB::transaction(function () use ($report, $newStatus, $updates, $oldStatus): BureaucracyReport {
            $oldStatus = $oldStatus ?? $report->verification_status;
            $report->fill($updates);
            $report->verification_status = $newStatus;
            $report->save();

            if ($report->agency_id !== null) {
                $agency = PublicAgencyScorecard::query()->whereKey($report->agency_id)->first();
                if ($agency !== null) {
                    $wasOpen = ! in_array($oldStatus, ['resolved', 'rejected'], true);
                    $isOpen = ! in_array($newStatus, ['resolved', 'rejected'], true);
                    $wasResolved = $oldStatus === 'resolved';
                    $isResolved = $newStatus === 'resolved';

                    if ($wasOpen && ! $isOpen) {
                        $agency->decrement('open_complaints_count');
                    } elseif (! $wasOpen && $isOpen) {
                        $agency->increment('open_complaints_count');
                    }
                    if (! $wasResolved && $isResolved) {
                        $agency->increment('resolved_complaints_count');
                    } elseif ($wasResolved && ! $isResolved) {
                        $agency->decrement('resolved_complaints_count');
                    }
                }
            }

            return $report;
        });
    }

    private function generateTicketCode(): string
    {
        do {
            $code = 'BW-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (BureaucracyReport::query()->where('ticket_code', $code)->exists());

        return $code;
    }
}
