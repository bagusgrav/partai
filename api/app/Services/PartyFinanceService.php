<?php

namespace App\Services;

use App\Models\PartyFinanceEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PartyFinanceService
{
    private const CANONICAL_FIELDS = [
        'entry_type',
        'channel',
        'fiscal_year',
        'transaction_date',
        'amount_idr',
        'donor_type',
        'user_id',
        'contributor_display_name',
        'identity_hash',
        'masked_identity',
        'legal_declaration_accepted',
        'purpose_or_program',
        'region_code',
        'province_name',
        'evidence_reference',
        'compliance_status',
        'recorded_by',
        'reversal_of_id',
    ];

    public function recordTransaction(array $payload): array
    {
        $requestId = (string) $payload['request_id'];

        return DB::transaction(function () use ($payload, $requestId) {
            $existing = PartyFinanceEntry::query()->where('request_id', $requestId)->first();
            if ($existing) {
                if ($this->payloadMatches($existing, $payload)) {
                    return ['entry' => $existing, 'created' => false];
                }

                throw new ConflictHttpException('request_id sudah digunakan dengan payload yang berbeda.');
            }

            $this->validateDeclarationAndLimits($payload);

            $latest = PartyFinanceEntry::query()->orderByDesc('sequence')->lockForUpdate()->first();
            $sequence = $latest ? ((int) $latest->sequence + 1) : 1;
            $previousHash = $latest?->hash;

            $canonical = [
                'sequence' => $sequence,
                'request_id' => $requestId,
            ];
            foreach (self::CANONICAL_FIELDS as $field) {
                $canonical[$field] = $payload[$field] ?? null;
            }
            $canonical['previous_hash'] = $previousHash;
            $hash = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR));

            $entry = PartyFinanceEntry::query()->create(array_merge($payload, [
                'sequence' => $sequence,
                'previous_hash' => $previousHash,
                'hash' => $hash,
            ]));

            return ['entry' => $entry, 'created' => true];
        });
    }

    private function payloadMatches(PartyFinanceEntry $entry, array $payload): bool
    {
        foreach (self::CANONICAL_FIELDS as $field) {
            $actual = $field === 'transaction_date'
                ? $entry->transaction_date?->toDateString()
                : $entry->{$field};
            $expected = $payload[$field] ?? null;
            if ((string) ($actual ?? '') !== (string) ($expected ?? '')) {
                return false;
            }
        }

        return true;
    }

    private function validateDeclarationAndLimits(array $payload): void
    {
        $donorType = $payload['donor_type'] ?? null;
        if (in_array($donorType, ['member', 'individual', 'corporate'], true)
            && ! ($payload['legal_declaration_accepted'] ?? false)) {
            throw ValidationException::withMessages([
                'legal_declaration_accepted' => 'Pernyataan integritas sumber dana wajib diterima.',
            ]);
        }

        $fiscalYear = (int) ($payload['fiscal_year'] ?? date('Y'));
        $amount = (int) ($payload['amount_idr'] ?? 0);

        if ($donorType === 'member') {
            $userId = $payload['user_id'] ?? null;
            $total = PartyFinanceEntry::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('donor_type', 'member')
                ->where('user_id', $userId)
                ->whereIn('compliance_status', ['pending_audit', 'verified'])
                ->sum('amount_idr');
            $limit = (int) config('finance.max_annual_member_dues_idr');
            if ((int) $total + $amount > $limit) {
                throw ValidationException::withMessages([
                    'amount_idr' => 'Pembayaran melebihi plafon konstitusional iuran anggota Rp 120.000.000 per tahun.',
                ]);
            }
        }

        if (in_array($donorType, ['individual', 'corporate'], true)) {
            $identityHash = $payload['identity_hash'] ?? null;
            $total = PartyFinanceEntry::query()
                ->where('fiscal_year', $fiscalYear)
                ->where('identity_hash', $identityHash)
                ->whereIn('compliance_status', ['pending_audit', 'verified'])
                ->sum('amount_idr');

            if ($donorType === 'individual') {
                $limit = (int) config('finance.max_annual_individual_donation_idr');
                if ((int) $total + $amount > $limit) {
                    throw ValidationException::withMessages([
                        'amount_idr' => 'Akumulasi sumbangan individu melebihi batas maksimal Rp 1 Miliar/tahun UU Parpol.',
                    ]);
                }
            } else {
                $limit = (int) config('finance.max_annual_corporate_donation_idr');
                if ((int) $total + $amount > $limit) {
                    throw ValidationException::withMessages([
                        'amount_idr' => 'Akumulasi sumbangan badan usaha melebihi batas maksimal Rp 7,5 Miliar/tahun UU Parpol.',
                    ]);
                }
            }
        }
    }

    public function verifyChainIntegrity(): array
    {
        $entries = PartyFinanceEntry::query()->orderBy('sequence')->get();
        $previousHash = null;
        $brokenAt = null;

        foreach ($entries as $entry) {
            if ($entry->previous_hash !== $previousHash) {
                $brokenAt ??= (int) $entry->sequence;
            }

            $canonical = ['sequence' => (int) $entry->sequence, 'request_id' => (string) $entry->request_id];
            foreach (self::CANONICAL_FIELDS as $field) {
                $canonical[$field] = $field === 'transaction_date'
                    ? $entry->transaction_date?->toDateString()
                    : $entry->{$field};
            }
            $canonical['previous_hash'] = $previousHash;
            $calculated = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR));

            if (! hash_equals((string) $entry->hash, $calculated)) {
                $brokenAt ??= (int) $entry->sequence;
            }
            $previousHash = $entry->hash;
        }

        return [
            'valid' => $brokenAt === null,
            'total_entries' => $entries->count(),
            'latest_hash' => $entries->last()?->hash,
            'broken_at_sequence' => $brokenAt,
        ];
    }

    public function getPublicSummary(): array
    {
        $verifiedInflow = PartyFinanceEntry::query()
            ->where('entry_type', 'inflow')
            ->where('compliance_status', 'verified')
            ->sum('amount_idr');
        $pendingInflow = PartyFinanceEntry::query()
            ->where('entry_type', 'inflow')
            ->where('compliance_status', 'pending_audit')
            ->sum('amount_idr');
        $verifiedOutflow = PartyFinanceEntry::query()
            ->where('entry_type', 'outflow')
            ->where('compliance_status', 'verified')
            ->sum('amount_idr');
        $reversals = PartyFinanceEntry::query()
            ->where('entry_type', 'reversal')
            ->where('compliance_status', 'verified')
            ->sum('amount_idr');

        $composition = [];
        foreach ([
            'member_dues' => 'member_dues',
            'individual_donation' => 'individual_donations',
            'corporate_donation' => 'corporate_donations',
        ] as $channel => $key) {
            $composition[$key] = (int) PartyFinanceEntry::query()
                ->where('entry_type', 'inflow')
                ->where('channel', $channel)
                ->where('compliance_status', 'verified')
                ->sum('amount_idr');
        }

        $educationOutflow = (int) PartyFinanceEntry::query()
            ->where('channel', 'political_education_expense')
            ->where('compliance_status', 'verified')
            ->sum('amount_idr');
        $netOutflow = max(0, (int) $verifiedOutflow - (int) $reversals);
        $educationRatio = $netOutflow > 0 ? round($educationOutflow / $netOutflow, 4) : 0.0;

        return [
            'verified_inflow_idr' => (int) $verifiedInflow,
            'pending_audit_inflow_idr' => (int) $pendingInflow,
            'verified_outflow_idr' => $netOutflow,
            'net_cash_balance_idr' => (int) $verifiedInflow - $netOutflow,
            'fund_composition' => $composition,
            'political_education_ratio' => $educationRatio,
            'political_education_target_ratio' => (float) config('finance.minimum_political_education_ratio'),
            'political_education_target_met' => $educationRatio >= (float) config('finance.minimum_political_education_ratio'),
            'constitutional_limits' => [
                'individual_annual_idr' => (int) config('finance.max_annual_individual_donation_idr'),
                'corporate_annual_idr' => (int) config('finance.max_annual_corporate_donation_idr'),
                'member_annual_idr' => (int) config('finance.max_annual_member_dues_idr'),
            ],
            'chain_integrity' => $this->verifyChainIntegrity(),
        ];
    }
}
