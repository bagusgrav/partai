<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CivicEducationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CivicEducationController extends Controller
{
    public function __construct(private readonly CivicEducationService $education) {}

    public function overview(): JsonResponse
    {
        return response()->json($this->education->overview());
    }

    public function session(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'member', 403, 'Sesi latihan anggota memerlukan akun anggota.');

        return response()->json($this->education->session($request->user()));
    }

    public function answer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
            'question_id' => ['required', 'uuid'],
            'selected_option' => ['required', 'in:A,B,C,D'],
            'confidence' => ['nullable', 'in:ragu,yakin'],
        ]);
        $validated['confidence'] ??= 'ragu';

        $result = $this->education->answer($request->user(), $validated);

        return response()->json($result['response']);
    }
}
