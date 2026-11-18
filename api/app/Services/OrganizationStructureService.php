<?php

namespace App\Services;

use App\Models\MemberReputation;
use App\Models\OrganizationSeat;
use App\Models\SeatApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OrganizationStructureService
{
    public function getTree(array $filters = []): array
    {
        $query = OrganizationSeat::query()->orderBy('sort_order')->orderBy('title');
        foreach (['layer', 'status', 'territory_level'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('seat_code', 'like', "%{$search}%");
            });
        }

        $seats = $query->get();
        $seatRows = $seats->map(fn (OrganizationSeat $seat): array => $this->serializeSeat($seat))->all();

        $nodes = $seatRows;
        $children = [];
        foreach ($nodes as $node) {
            $children[$node['parent_id'] ?? 'root'][] = $node;
        }
        $build = function (array $nodes, callable $build) use (&$children): array {
            return array_map(function (array $node) use ($build, &$children) {
                $node['children'] = $children[$node['id']] ?? [];
                $node['children'] = $build($node['children'], $build);

                return $node;
            }, $nodes);
        };
        $tree = $build($children['root'] ?? [], $build);

        $allSeats = OrganizationSeat::query()->get();

        return [
            'tree' => $tree,
            'seats' => $seatRows,
            'summary' => [
                'total_seats' => $allSeats->count(),
                'filled_seats' => $allSeats->where('status', 'filled')->count(),
                'open_seats' => $allSeats->whereIn('status', ['open', 'sortition_pool', 'term_expiring'])->count(),
            ],
            'holders' => $allSeats->whereNotNull('holder_user_id')->values()->map(fn (OrganizationSeat $seat): array => [
                'seat_code' => $seat->seat_code,
                'holder_display_name' => $seat->holder_display_name,
                'term_starts_at' => $seat->term_starts_at?->toDateString(),
                'term_ends_at' => $seat->term_ends_at?->toDateString(),
            ]),
            'responsibility_matrix' => [
                ['layer' => 'anggota_berdaulat', 'title' => 'Administrasi Anggota & Mandat', 'answers' => 'Keanggotaan, hak suara, dan koreksi mandat melalui mekanisme recall.'],
                ['layer' => 'pengurus_legal', 'title' => 'Administrasi Legal', 'answers' => 'Ketua, sekretaris, dan bendahara menjalankan mandat terbatas; tidak mengubah hak suara atau reputasi.'],
                ['layer' => 'peran_fungsional_sortisi', 'title' => 'Audit, Etik & Editorial', 'answers' => 'Panel audit menguji keuangan, panel etik menguji integritas, dan panel editorial menjaga kurikulum.'],
                ['layer' => 'perwalian_teknis', 'title' => 'Perwalian Teknis', 'answers' => 'Perwalian menjaga infrastruktur, akses, dan operasi multisig dengan batas teknis.'],
                ['layer' => 'cluster_teritorial', 'title' => 'Koordinasi Cluster', 'answers' => 'Koordinator menjawab laporan lapangan dan menyalurkan layanan tanpa menguasai hak suara.'],
            ],
        ];
    }

    public function evaluateEligibility(User $user, OrganizationSeat $seat): array
    {
        $profile = MemberReputation::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            ['mastery_scores' => app(ReputationService::class)->emptyMasteryScores()],
        );

        $mastery = (int) $profile->overall_mastery_level;
        $participation = (int) $profile->participation_score;
        $reasons = [];

        if ($mastery < $seat->required_mastery_level) {
            $reasons[] = "Mastery Level belum mencukupi ({$mastery}/{$seat->required_mastery_level}).";
        }
        if ($seat->required_domain) {
            $scores = $profile->mastery_scores ?? [];
            $domainScore = (int) ($scores[$seat->required_domain] ?? 0);
            $domainMinimum = match ((int) $seat->required_mastery_level) {
                4 => 80,
                3 => 60,
                2 => 40,
                default => 0,
            };
            if ($domainScore < $domainMinimum) {
                $reasons[] = "Skor domain {$seat->required_domain} belum mencapai {$domainMinimum} (saat ini {$domainScore}).";
            }
        }
        if ($participation < $seat->required_min_participation) {
            $reasons[] = "Participation Score belum mencukupi ({$participation}/{$seat->required_min_participation}).";
        }

        return [
            'eligible' => $reasons === [],
            'reasons' => $reasons,
            'current_mastery_level' => $mastery,
            'current_participation_score' => $participation,
        ];
    }

    public function applyForSeat(User $user, OrganizationSeat $seat, array $payload): array
    {
        $requestId = (string) $payload['request_id'];
        $statement = trim((string) $payload['statement']);

        return DB::transaction(function () use ($user, $seat, $requestId, $statement): array {
            $existingRequest = SeatApplication::query()->where('request_id', $requestId)->first();
            if ($existingRequest) {
                if ($existingRequest->seat_id === $seat->id
                    && $existingRequest->user_id === $user->id
                    && trim($existingRequest->statement) === $statement) {
                    return ['application' => $existingRequest, 'created' => false];
                }
                throw new ConflictHttpException('request_id sudah digunakan dengan payload yang berbeda.');
            }

            $existingApplication = SeatApplication::query()
                ->where('seat_id', $seat->id)
                ->where('user_id', $user->id)
                ->first();
            if ($existingApplication) {
                throw new ConflictHttpException('Anggota sudah terdaftar pada kursi ini.');
            }

            if (! in_array($seat->status, ['open', 'sortition_pool'], true)) {
                throw ValidationException::withMessages([
                    'seat' => 'Kursi belum terbuka untuk pendaftaran.',
                ]);
            }

            $eligibility = $this->evaluateEligibility($user, $seat);
            if (! $eligibility['eligible']) {
                throw ValidationException::withMessages([
                    'eligibility' => $eligibility['reasons'],
                ]);
            }

            $application = SeatApplication::query()->create([
                'seat_id' => $seat->id,
                'user_id' => $user->id,
                'request_id' => $requestId,
                'statement' => $statement,
                'mastery_level_snapshot' => $eligibility['current_mastery_level'],
                'participation_score_snapshot' => $eligibility['current_participation_score'],
                'status' => 'in_pool',
            ]);

            return ['application' => $application, 'created' => true];
        });
    }

    private function serializeSeat(OrganizationSeat $seat): array
    {
        return [
            'id' => $seat->id,
            'seat_code' => $seat->seat_code,
            'parent_id' => $seat->parent_id,
            'layer' => $seat->layer,
            'title' => $seat->title,
            'territory_level' => $seat->territory_level,
            'region_code' => $seat->region_code,
            'province_name' => $seat->province_name,
            'answering_scope' => $seat->answering_scope,
            'selection_method' => $seat->selection_method,
            'required_mastery_level' => $seat->required_mastery_level,
            'required_domain' => $seat->required_domain,
            'required_min_participation' => $seat->required_min_participation,
            'term_duration_months' => $seat->term_duration_months,
            'max_consecutive_terms' => $seat->max_consecutive_terms,
            'holder_user_id' => $seat->holder_user_id,
            'holder_display_name' => $seat->holder_display_name,
            'term_starts_at' => $seat->term_starts_at?->toDateString(),
            'term_ends_at' => $seat->term_ends_at?->toDateString(),
            'status' => $seat->status,
            'sort_order' => $seat->sort_order,
        ];
    }
}
