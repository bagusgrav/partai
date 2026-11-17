<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberReputation;
use App\Models\ReputationLedger;
use App\Models\User;
use App\Services\ReputationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberReputationController extends Controller
{
    public function __construct(private readonly ReputationService $reputationService) {}

    public function leaderboardStats(): JsonResponse
    {
        $members = User::query()
            ->where('role', 'member')
            ->where('is_verified', true);
        $distribution = (clone $members)
            ->leftJoin('member_reputations', 'users.id', '=', 'member_reputations.user_id')
            ->selectRaw('COALESCE(member_reputations.overall_mastery_level, 1) as overall_mastery_level, COUNT(*) as members')
            ->groupBy('overall_mastery_level')
            ->pluck('members', 'overall_mastery_level');
        $contributionTotals = ReputationLedger::query()
            ->join('users', 'users.id', '=', 'reputation_ledgers.user_id')
            ->where('users.role', 'member')
            ->where('users.is_verified', true)
            ->where('reputation_ledgers.score_type', 'participation')
            ->selectRaw('reputation_ledgers.category, SUM(reputation_ledgers.delta) as total')
            ->groupBy('reputation_ledgers.category')
            ->get()
            ->keyBy('category');
        $fieldPoints = (int) ($contributionTotals->get('lapangan')?->total ?? 0);
        $nonPhysicalPoints = 0;
        foreach (ReputationService::CATEGORIES as $category) {
            if ($category !== 'lapangan') {
                $nonPhysicalPoints += (int) ($contributionTotals->get($category)?->total ?? 0);
            }
        }

        return response()->json([
            'members_count' => $members->count(),
            'mastery_distribution' => collect([1, 2, 3, 4])
                ->map(fn (int $level): array => [
                    'level' => $level,
                    'members' => (int) $distribution->get($level, 0),
                ]),
            'contributions' => [
                'lapangan' => $fieldPoints,
                'non_fisik' => $nonPhysicalPoints,
                'total' => $fieldPoints + $nonPhysicalPoints,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $userId = (string) $request->user()->getKey();
        $profile = $this->reputationService->recalculate($userId);
        $ledger = ReputationLedger::query()
            ->where('user_id', $userId)
            ->orderByDesc('sequence')
            ->paginate(50, [
                'id',
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
                'hash',
                'created_at',
            ]);

        return response()->json([
            'mastery_scores' => $profile->mastery_scores,
            'overall_mastery_level' => $profile->overall_mastery_level,
            'participation_score' => $profile->participation_score,
            'eligible_roles' => $profile->eligible_roles,
            'ledger' => $ledger,
        ]);
    }

    public function storeContribution(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'user_id' => ['required', 'uuid', 'exists:users,id,role,member,is_verified,1'],
            'category' => ['required', Rule::in(ReputationService::CATEGORIES)],
            'source_type' => ['required', 'string', 'max:80'],
            'source_id' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $result = $this->reputationService->record([
            'user_id' => $validated['user_id'],
            'actor_id' => (string) $request->user()->getKey(),
            'score_type' => 'participation',
            'domain' => null,
            'category' => $validated['category'],
            'source_type' => $validated['source_type'],
            'source_id' => $validated['source_id'],
            'delta' => 1,
            'reason' => $validated['reason'],
            'request_id' => $validated['request_id'],
        ]);
        $profile = MemberReputation::query()
            ->where('user_id', $validated['user_id'])
            ->firstOrFail();

        return response()->json([
            'status' => $result['created'] ? 'created' : 'already_processed',
            'ledger_id' => $result['entry']->getKey(),
            'participation_score' => $profile->participation_score,
        ], $result['created'] ? 201 : 200);
    }
}
