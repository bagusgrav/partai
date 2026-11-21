<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WelfareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function landing(WelfareService $welfare): JsonResponse
    {
        $now = now();
        $correctReviewAnswers = DB::table('reviews')
            ->where('correct', true)
            ->count();
        $correctCivicAnswers = DB::table('civic_answer_requests')
            ->where('response->correct', true)
            ->count();

        return response()->json([
            'total_members' => User::query()->count(),
            'total_votes_cast' => (int) DB::table('vote_options')->sum('votes_count'),
            'total_quizzes_completed' => (int) ($correctReviewAnswers + $correctCivicAnswers),
            'poverty_rate' => (float) ($welfare->summary()['summary']['average_poverty'] ?? 9.0),
            'active_votes' => DB::table('votes')
                ->where('status', 'active')
                ->where('starts_at', '<=', $now)
                ->where('ends_at', '>=', $now)
                ->count(),
        ]);
    }
}
