<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GovernmentInsight;
use Illuminate\Http\JsonResponse;

class GovernmentInsightController extends Controller
{
    public function news(): JsonResponse
    {
        return response()->json([
            'insights' => GovernmentInsight::query()
                ->where('category', 'berita')
                ->orderByDesc('published_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function cabinet(): JsonResponse
    {
        return response()->json([
            'insights' => GovernmentInsight::query()
                ->where('category', 'kabinet')
                ->orderBy('title')
                ->get(),
        ]);
    }
}
