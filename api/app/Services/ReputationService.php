<?php

namespace App\Services;

use App\Models\MemberReputation;
use App\Models\ReputationLedger;
use App\Models\ReputationLog;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReputationService
{
    public const QUIZ_CORRECT = 'quiz_correct';

    public const ESSAY_PASSED = 'essay_passed';

    public const ATTENDANCE_VALIDATED = 'attendance_validated';

    public const CITIZEN_JURY = 'citizen_jury';

    public const DOMAINS = [
        'hak_konstitusional',
        'alasan_bernegara',
        'keuangan_negara',
        'hukum',
        'politik_bernegara',
    ];

    public const CATEGORIES = [
        'lapangan',
        'riset',
        'bantuan_hukum',
        'logistik',
        'editorial',
    ];

    public function award(
        string $userId,
        string $action,
        int $points,
        string $description,
        string $requestId,
    ): ?ReputationLog {
        return DB::transaction(function () use ($userId, $action, $points, $description, $requestId) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $existingLog = ReputationLog::query()
                ->where('action', $action)
                ->where('request_id', $requestId)
                ->first();

            if ($existingLog !== null) {
                return null;
            }

            try {
                $log = ReputationLog::query()->create([
                    'user_id' => $userId,
                    'action' => $action,
                    'points' => $points,
                    'description' => $description,
                    'request_id' => $requestId,
                ]);
            } catch (QueryException $exception) {
                $existingLog = ReputationLog::query()
                    ->where('action', $action)
                    ->where('request_id', $requestId)
                    ->first();

                if ($existingLog === null) {
                    throw $exception;
                }

                return null;
            }

            $newTotal = (int) $user->reputation_points + $points;
            $user->forceFill([
                'reputation_points' => $newTotal,
                'reputation_level' => $this->levelForPoints($newTotal),
            ])->save();

            return $log;
        }, 3);
    }

    public function levelForPoints(int $points): string
    {
        return match (true) {
            $points >= 1000 => 'Pengawal Demokrasi',
            $points >= 500 => 'Penggerak',
            $points >= 100 => 'Warga Aktif',
            default => 'Warga Baru',
        };
    }

    /**
     * @param  array{
     *     user_id: string,
     *     actor_id?: ?string,
     *     score_type: 'mastery'|'participation',
     *     domain?: ?string,
     *     category?: ?string,
     *     source_type: string,
     *     source_id: string,
     *     delta: int,
     *     reason: string,
     *     request_id: string
     * }  $attributes
     * @return array{entry: ReputationLedger, created: bool}
     */
    public function record(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $existing = ReputationLedger::query()
                ->where('request_id', $attributes['request_id'])
                ->first();

            if ($existing !== null) {
                if (! $this->matches($existing, $attributes)) {
                    throw new ConflictHttpException('request_id sudah digunakan untuk perubahan reputasi yang berbeda.');
                }

                return ['entry' => $existing, 'created' => false];
            }

            MemberReputation::query()->firstOrCreate(
                ['user_id' => $attributes['user_id']],
                ['mastery_scores' => $this->emptyMasteryScores()],
            );

            $previous = ReputationLedger::query()
                ->where('user_id', $attributes['user_id'])
                ->orderByDesc('sequence')
                ->first();
            $sequence = ($previous?->sequence ?? 0) + 1;
            $record = [
                'user_id' => $attributes['user_id'],
                'actor_id' => $attributes['actor_id'] ?? null,
                'sequence' => $sequence,
                'score_type' => $attributes['score_type'],
                'domain' => $attributes['domain'] ?? null,
                'category' => $attributes['category'] ?? null,
                'source_type' => $attributes['source_type'],
                'source_id' => $attributes['source_id'],
                'delta' => $attributes['delta'],
                'reason' => $attributes['reason'],
                'request_id' => $attributes['request_id'],
                'previous_hash' => $previous?->hash,
            ];
            $record['hash'] = $this->hashRecord($record);
            $entry = ReputationLedger::query()->create($record);

            $this->recalculate($attributes['user_id']);

            return ['entry' => $entry, 'created' => true];
        }, 3);
    }

    public function recalculate(string $userId): MemberReputation
    {
        $profile = MemberReputation::query()->firstOrCreate(
            ['user_id' => $userId],
            ['mastery_scores' => $this->emptyMasteryScores()],
        );
        $totals = ReputationLedger::query()
            ->where('user_id', $userId)
            ->selectRaw('score_type, domain, SUM(delta) as total')
            ->groupBy('score_type', 'domain')
            ->get()
            ->keyBy(fn (object $row): string => $row->score_type.':'.($row->domain ?? ''));

        $scores = $this->emptyMasteryScores();
        foreach (self::DOMAINS as $domain) {
            $scores[$domain] = max(0, min(100, (int) ($totals->get('mastery:'.$domain)?->total ?? 0)));
        }

        $average = intdiv(array_sum($scores), count($scores));
        $level = match (true) {
            $average >= 80 => 4,
            $average >= 60 => 3,
            $average >= 40 => 2,
            default => 1,
        };
        $participation = max(0, (int) ($totals->get('participation:')?->total ?? 0));
        $eligible = [
            'panel_audit' => $level >= 4,
            'panel_etik' => $level >= 4,
            'bakal_caleg' => $level >= 4,
        ];

        $profile->forceFill([
            'mastery_scores' => $scores,
            'overall_mastery_level' => $level,
            'participation_score' => $participation,
            'eligible_roles' => $eligible,
        ])->save();

        return $profile->refresh();
    }

    public function verifyLedger(string $userId): bool
    {
        $entries = ReputationLedger::query()
            ->where('user_id', $userId)
            ->orderBy('sequence')
            ->get();
        $previousHash = null;
        $expectedSequence = 1;

        foreach ($entries as $entry) {
            if (
                $entry->sequence !== $expectedSequence
                || $entry->previous_hash !== $previousHash
                || ! hash_equals($this->hashRecord($entry->only([
                    'user_id',
                    'actor_id',
                    'sequence',
                    'score_type',
                    'domain',
                    'category',
                    'source_type',
                    'source_id',
                    'delta',
                    'reason',
                    'request_id',
                    'previous_hash',
                ])), $entry->hash)
            ) {
                return false;
            }

            $previousHash = $entry->hash;
            $expectedSequence++;
        }

        return true;
    }

    /**
     * @return array<string, int>
     */
    public function emptyMasteryScores(): array
    {
        return array_fill_keys(self::DOMAINS, 0);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function hashRecord(array $record): string
    {
        return hash('sha256', json_encode($record, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function matches(ReputationLedger $entry, array $attributes): bool
    {
        foreach ([
            'user_id',
            'score_type',
            'domain',
            'category',
            'source_type',
            'source_id',
            'delta',
            'reason',
        ] as $key) {
            if ($entry->{$key} !== ($attributes[$key] ?? null)) {
                return false;
            }
        }

        return $entry->actor_id === ($attributes['actor_id'] ?? null);
    }
}
