<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostReportRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostReportController extends Controller
{
    public function store(StorePostReportRequest $request, Post $post): JsonResponse
    {
        $report = $post->reports()->create([
            'reporter_id' => $request->user()->getAuthIdentifier(),
            'reason' => $request->validated('reason'),
            'status' => 'open',
        ]);

        return response()->json([
            'id' => $report->id,
            'status' => $report->status,
        ], 201);
    }
}
