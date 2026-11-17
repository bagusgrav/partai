<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAssistance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialAssistanceController extends Controller
{
    public function check(Request $request, string $nik): JsonResponse
    {
        if (! preg_match('/^[0-9]{16}$/', $nik)) {
            return response()->json([
                'message' => 'NIK harus terdiri dari 16 digit angka.',
            ], 422)->header('Cache-Control', 'no-store, private');
        }

        $userNik = $request->user()->nik;

        if (! is_string($userNik) || ! hash_equals($userNik, $nik)) {
            return response()->json([
                'message' => 'Anda hanya dapat memeriksa NIK yang terdaftar pada akun Anda.',
            ], 403)->header('Cache-Control', 'no-store, private');
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
