<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SortitionController extends Controller
{
    public function panels(): JsonResponse
    {
        $panels = DB::table('member_reputations')
            ->join('users', 'users.id', '=', 'member_reputations.user_id')
            ->where('users.role', 'member')
            ->where('users.is_verified', true)
            ->where('member_reputations.overall_mastery_level', '>=', 4)
            ->inRandomOrder()
            ->limit(5)
            ->get([
                'users.id',
                'users.name',
                'member_reputations.overall_mastery_level as mastery',
            ]);

        return response()->json(['panels' => $panels]);
    }
}
