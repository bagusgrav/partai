<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WelfareData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class WelfareController extends Controller
{
    public function summary(): JsonResponse
    {
        $povertyYear = WelfareData::query()
            ->where('indicator', 'kemiskinan')
            ->where('source', 'BPS')
            ->max('year');
        $stuntingYear = WelfareData::query()
            ->where('indicator', 'stunting')
            ->where('source', 'BPS')
            ->max('year');
        $currentYear = (int) now()->year;

        return response()->json([
            'summary' => [
                'average_poverty' => $povertyYear === null
                    ? null
                    : (float) WelfareData::query()
                        ->where('indicator', 'kemiskinan')
                        ->where('source', 'BPS')
                        ->where('year', $povertyYear)
                        ->avg('value'),
                'poverty_year' => $povertyYear === null ? null : (int) $povertyYear,
                'average_stunting' => $stuntingYear === null
                    ? null
                    : (float) WelfareData::query()
                        ->where('indicator', 'stunting')
                        ->where('source', 'BPS')
                        ->where('year', $stuntingYear)
                        ->avg('value'),
                'stunting_year' => $stuntingYear === null ? null : (int) $stuntingYear,
                'disaster_events' => WelfareData::query()
                    ->where('indicator', 'bencana')
                    ->where('source', 'BNPB')
                    ->where('year', $currentYear)
                    ->sum('value'),
                'disaster_year' => $currentYear,
            ],
        ]);
    }

    public function regions(): JsonResponse
    {
        $currentYear = (int) now()->year;
        $regions = WelfareData::query()
            ->orderBy('region_code')
            ->orderByDesc('year')
            ->get()
            ->groupBy('region_code')
            ->map(function (Collection $records) use ($currentYear): array {
                $bpsRecords = $records->where('source', 'BPS');
                $poverty = $bpsRecords->firstWhere('indicator', 'kemiskinan');
                $stunting = $bpsRecords->firstWhere('indicator', 'stunting');
                $disasterRecords = $records
                    ->where('indicator', 'bencana')
                    ->where('source', 'BNPB')
                    ->where('year', $currentYear);

                return [
                    'region_code' => $records->first()->region_code,
                    'region_name' => $records->first()->region_name,
                    'poverty' => $poverty?->value,
                    'poverty_year' => $poverty?->year,
                    'stunting' => $stunting?->value,
                    'stunting_year' => $stunting?->year,
                    'disaster_events' => $disasterRecords->sum('value'),
                    'disaster_year' => $disasterRecords->isEmpty() ? null : $currentYear,
                ];
            })
            ->values();

        return response()->json(['regions' => $regions]);
    }

    public function region(string $code): JsonResponse
    {
        $records = WelfareData::query()
            ->where('region_code', $code)
            ->orderByDesc('year')
            ->orderBy('indicator')
            ->get();

        if ($records->isEmpty()) {
            return response()->json(['message' => 'Data wilayah tidak ditemukan.'], 404);
        }

        return response()->json([
            'region_code' => $records->first()->region_code,
            'region_name' => $records->first()->region_name,
            'data' => $records,
        ]);
    }
}
