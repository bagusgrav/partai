<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SortitionController extends Controller
{
    public function panels(): JsonResponse
    {
        $panels = DB::table('users')
            ->join('user_item_states', 'users.id', '=', 'user_item_states.user_id')
            ->where('users.role', 'member')
            ->where('users.is_verified', true)
            ->where('user_item_states.reps', '>=', 3)
            ->groupBy('users.id', 'users.name')
            ->havingRaw('COUNT(DISTINCT user_item_states.item_id) >= ?', [3])
            ->inRandomOrder()
            ->limit(5)
            ->get([
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT user_item_states.item_id) as mastery'),
            ]);

        return response()->json(['panels' => $panels]);
    }
}
