<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EconomicIndicator;
use Illuminate\Http\JsonResponse;

class EconomicController extends Controller
{
    public function summary(): JsonResponse
    {
        $indicators = EconomicIndicator::query()
            ->orderByDesc('period')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('indicator_type')
            ->values();

        return response()->json([
            'indicators' => $indicators,
            'last_updated' => $indicators->max('updated_at'),
        ]);
    }

    public function history(string $type): JsonResponse
    {
        $records = EconomicIndicator::query()
            ->where('indicator_type', $type)
            ->orderByDesc('period')
            ->limit(90)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'type' => $type,
            'data' => $records,
        ]);
    }
}
