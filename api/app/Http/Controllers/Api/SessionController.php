<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SessionController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $user = User::query()->where('email', $validated['email'])->first();

        if ($user === null || $user->role === 'guest' || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau kata sandi tidak valid.',
            ], 422);
        }

        return response()->json([
            'token' => $user->createToken('member-session')->plainTextToken,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Anda berhasil keluar.',
        ]);
    }
}
