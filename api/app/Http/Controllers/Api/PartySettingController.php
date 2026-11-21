<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartySettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = PartySetting::query()->firstOrFail();

        return response()->json([
            'party_name' => $settings->party_name,
            'logo_path' => $settings->logo_path === null
                ? null
                : route('api.settings.logo'),
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color,
            'tagline' => $settings->tagline,
        ])->header('Cache-Control', 'no-store');
    }

    public function logo(): StreamedResponse
    {
        $settings = PartySetting::query()->firstOrFail();

        abort_if($settings->logo_path === null, 404);

        return Storage::disk('public')->response($settings->logo_path, headers: [
            'Cache-Control' => 'no-store',
        ]);
    }
}
