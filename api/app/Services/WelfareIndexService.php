<?php

namespace App\Services;

use App\Models\RegionWelfareIndex;
use Illuminate\Support\Facades\DB;

class WelfareIndexService
{
    public const METHODOLOGY = [
        'poverty_rate' => ['label' => 'Kemiskinan', 'weight' => 30, 'min' => 0, 'max' => 35, 'unit' => '%'],
        'stunting_rate' => ['label' => 'Stunting', 'weight' => 25, 'min' => 0, 'max' => 45, 'unit' => '%'],
        'gini_ratio' => ['label' => 'Rasio Gini', 'weight' => 15, 'min' => 0.25, 'max' => 0.55, 'unit' => 'rasio'],
        'open_unemployment_rate' => ['label' => 'TPT', 'weight' => 15, 'min' => 0, 'max' => 15, 'unit' => '%'],
        'disaster_risk_score' => ['label' => 'Risiko bencana BNPB', 'weight' => 15, 'min' => 0, 'max' => 100, 'unit' => 'skor'],
    ];

    /**
     * @param  array<string, int|float|string>  $indicators
     * @return array{ikk_score: float, vulnerability_tier: string, sub_scores: array<string, float>}
     */
    public function calculate(array $indicators): array
    {
        $subScores = [];
        $score = 0.0;

        foreach (self::METHODOLOGY as $key => $method) {
            $value = (float) $indicators[$key];
            $normalized = $key === 'gini_ratio'
                ? (($value - $method['min']) / ($method['max'] - $method['min'])) * 100
                : ($value / $method['max']) * 100;
            $normalized = round(min(100, max(0, $normalized)), 2);
            $subScores[$key] = $normalized;
            $score += $normalized * ($method['weight'] / 100);
        }

        $score = round($score, 2);

        return [
            'ikk_score' => $score,
            'vulnerability_tier' => match (true) {
                $score < 30 => 'rendah',
                $score < 50 => 'sedang',
                $score < 70 => 'tinggi',
                default => 'kritis',
            },
            'sub_scores' => $subScores,
        ];
    }

    /**
     * @param  list<array<string, int|float|string|null>>  $records
     * @return int number of records saved
     */
    public function upsert(array $records): int
    {
        return DB::transaction(function () use ($records): int {
            foreach ($records as $record) {
                $calculation = $this->calculate($record);
                RegionWelfareIndex::query()->updateOrCreate(
                    [
                        'region_code' => $record['region_code'],
                        'period_year' => $record['period_year'],
                    ],
                    [
                        ...$record,
                        'ikk_score' => $calculation['ikk_score'],
                        'vulnerability_tier' => $calculation['vulnerability_tier'],
                    ],
                );
            }

            return count($records);
        });
    }
}
