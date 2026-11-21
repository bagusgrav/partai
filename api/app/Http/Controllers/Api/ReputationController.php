<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReputationLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReputationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->refresh();
        $logs = $user->reputationLogs()
            ->latest()
            ->limit(10)
            ->get(['id', 'action', 'points', 'description', 'created_at']);
        $masteryEntries = ReputationLedger::query()
            ->where('user_id', $user->getKey())
            ->where('score_type', 'mastery')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'source_type', 'delta', 'reason', 'created_at'])
            ->map(fn (ReputationLedger $entry): array => [
                'id' => $entry->id,
                'action' => $entry->source_type,
                'score_type' => 'mastery',
                'points' => $entry->delta,
                'description' => $entry->reason,
                'created_at' => $entry->created_at,
            ]);
        $history = collect($logs->all())
            ->merge($masteryEntries)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return response()->json([
            'reputation_points' => (int) $user->reputation_points,
            'reputation_level' => $user->reputation_level,
            'logs' => $history,
        ]);
    }
}
