<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function guest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'uuid'],
        ]);

        $user = User::query()->firstOrCreate(
            ['guest_request_id' => $validated['request_id']],
            [
                'name' => 'Tamu',
                'email' => 'guest_'.Str::uuid().'@partai.id',
                'password' => Str::random(64),
            ],
        );

        return response()->json([
            'token' => $user->createToken('guest-session')->plainTextToken,
        ], 201);
    }
}
