<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Faq::query()
                ->where('is_published', true)
                ->orderBy('created_at')
                ->get(['id', 'question', 'answer']),
        );
    }
}
