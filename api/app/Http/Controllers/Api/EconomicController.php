<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EconomicIndicator;
use App\Models\EconomicIndicatorObservation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class EconomicController extends Controller
{
    public function indicators(): JsonResponse
    {
        $grouped = EconomicIndicatorObservation::query()
            ->where('indicator_name', 'inflasi')
            ->orderBy('period_year')
            ->orderBy('period_month')
            ->get()
            ->groupBy('indicator_name')
            ->map(static fn ($records) => $records->values());

        $exchangeRates = EconomicIndicator::query()
            ->where('indicator_type', 'exchange_rate')
            ->orderBy('period')
            ->get()
            ->map(static function (EconomicIndicator $record): array {
                $period = Carbon::parse($record->period);

                return [
                    'id' => (string) $record->id,
                    'indicator_name' => 'kurs_usd',
                    'period_year' => $period->year,
                    'period_month' => $period->month,
                    'value' => (float) $record->value,
                    'unit' => $record->unit,
                    'source' => $record->source,
                    'updated_at' => $record->updated_at,
                ];
            });

        if ($exchangeRates->isNotEmpty()) {
            $grouped->put('kurs_usd', $exchangeRates->values());
        }

        $lastUpdated = $grouped
            ->flatten(1)
            ->pluck('updated_at')
            ->filter()
            ->max();

        return response()->json([
            'indicators' => $grouped,
            'last_updated' => $lastUpdated,
        ]);
    }

    public function summary(): JsonResponse
    {
        $indicators = EconomicIndicator::query()
            ->orderByDesc('period')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('indicator_type')
            ->values();

        return response()->json([
            'indicators' => $indicators,
            'last_updated' => $indicators->max('updated_at'),
        ]);
    }

    public function history(string $type): JsonResponse
    {
        $records = EconomicIndicator::query()
            ->where('indicator_type', $type)
            ->orderByDesc('period')
            ->limit(90)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'type' => $type,
            'data' => $records,
        ]);
    }
}
