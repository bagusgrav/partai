<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReputationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->refresh();

        return response()->json([
            'reputation_points' => (int) $user->reputation_points,
            'reputation_level' => $user->reputation_level,
            'logs' => $user->reputationLogs()
                ->latest()
                ->limit(10)
                ->get(['id', 'action', 'points', 'description', 'created_at']),
        ]);
    }
}
