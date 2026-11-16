<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WelfareService;
use Illuminate\Http\JsonResponse;

class WelfareController extends Controller
{
    public function summary(WelfareService $welfare): JsonResponse
    {
        return response()->json($welfare->summary());
    }

    public function regions(WelfareService $welfare): JsonResponse
    {
        return response()->json($welfare->regions());
    }

    public function region(string $code, WelfareService $welfare): JsonResponse
    {
        $region = $welfare->region($code);

        if ($region === null) {
            return response()->json(['message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        return response()->json($region);
    }
}
