<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WelfareData;
use Illuminate\Http\JsonResponse;

class MemberVerificationController extends Controller
{
    public function show(string $memberNumber): JsonResponse
    {
        $member = User::query()
            ->with('region')
            ->where('member_number', $memberNumber)
            ->whereNotNull('nik_verified_at')
            ->first();

        if ($member === null) {
            return response()->json([
                'message' => 'Kartu anggota tidak ditemukan.',
            ], 404)->header('Cache-Control', 'no-store');
        }

        $regionName = $member->region?->name;
        if ($regionName === null) {
            $regionCode = $member->region_id ?? $member->cluster_id;
            $regionName = $regionCode === null
                ? null
                : WelfareData::query()->where('region_code', $regionCode)->value('region_name');
        }

        return response()->json([
            'name' => $member->name,
            'member_number' => $member->member_number,
            'region_name' => $regionName,
            'status' => 'verified',
        ])->header('Cache-Control', 'no-store');
    }
}
