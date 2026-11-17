<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class InquiryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'question' => ['required', 'string', 'max:5000'],
        ]);

        $inquiry = Inquiry::query()->firstOrCreate(
            ['request_id' => $validated['request_id']],
            [
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'question' => $validated['question'],
                'status' => 'pending',
            ],
        );

        if (
            $inquiry->name !== ($validated['name'] ?? null)
            || $inquiry->email !== ($validated['email'] ?? null)
            || $inquiry->question !== $validated['question']
        ) {
            throw new ConflictHttpException('request_id sudah digunakan untuk pertanyaan yang berbeda.');
        }

        return response()->json([
            'status' => $inquiry->wasRecentlyCreated ? 'created' : 'already_processed',
            'message' => 'Pertanyaan Anda telah dikirim. Pengurus akan segera meninjau.',
        ], $inquiry->wasRecentlyCreated ? 201 : 200);
    }
}
