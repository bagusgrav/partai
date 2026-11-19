<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAssistance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAssistanceController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();
        $nik = $user->nik;

        if (! is_string($nik) || $user->nik_verified_at === null) {
            return response()->json([
                'message' => 'NIK belum terverifikasi.',
                'code' => 'nik_not_verified',
            ], 403)->header('Cache-Control', 'no-store, private');
        }

        if (! preg_match('/^[0-9]{16}$/', $nik)) {
            return response()->json([
                'message' => 'NIK harus terdiri dari 16 digit angka.',
            ], 422)->header('Cache-Control', 'no-store, private');
        }

        $assistance = SocialAssistance::query()
            ->where('nik', $nik)
            ->first(['program', 'status', 'period']);

        if ($assistance === null) {
            return response()->json([
                'message' => 'NIK tidak terdaftar sebagai penerima bansos',
            ], 404)->header('Cache-Control', 'no-store, private');
        }

        return response()->json($assistance)
            ->header('Cache-Control', 'no-store, private');
    }
}
