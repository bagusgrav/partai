<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicatorObservation;
use App\Models\WelfareData;
use App\Services\WelfareIndexService;
use Illuminate\Console\Command;

class SyncWelfareFromEconomicIndicators extends Command
{
    protected $signature = 'welfare:sync-economic-indicators {--year=2024 : Observation year to aggregate}';

    protected $description = 'Recalculate IKK from BPS economic observations for provinces and Indonesia';

    public function handle(WelfareIndexService $service): int
    {
        $year = filter_var($this->option('year'), FILTER_VALIDATE_INT);

        if (! is_int($year) || $year < 2021 || $year > 2024) {
            $this->error('The aggregation year must be between 2021 and 2024.');

            return self::FAILURE;
        }

        $observations = EconomicIndicatorObservation::query()
            ->whereIn('indicator_name', ['kemiskinan', 'tpt', 'gini', 'stunting'])
            ->where('period_year', $year)
            ->whereNull('period_month')
            ->where(function ($query): void {
                $query->where('region_code', '9999')
                    ->orWhere('region_code', 'like', '__00');
            })
            ->orderBy('region_code')
            ->get(['indicator_name', 'region_code', 'region_name', 'value'])
            ->groupBy('region_code');

        $disastersByProvince = WelfareData::query()
            ->where('indicator', 'bencana')
            ->where('source', 'BNPB')
            ->where('year', $year)
            ->selectRaw('region_code, SUM(value) AS event_count')
            ->groupBy('region_code')
            ->pluck('event_count', 'region_code');
        $disasterCounts = [];

        foreach ($disastersByProvince as $regionCode => $eventCount) {
            $provinceCode = substr((string) $regionCode, 0, 2);
            $disasterCounts[$provinceCode] = ($disasterCounts[$provinceCode] ?? 0) + (int) $eventCount;
        }

        $maxEvents = max([1, ...array_values($disasterCounts)]);
        $records = [];
        $missing = [];

        foreach ($observations as $regionCode => $rows) {
            $regionCode = (string) $regionCode;
            $byIndicator = $rows->keyBy('indicator_name');
            $indicatorNames = ['kemiskinan', 'tpt', 'gini', 'stunting'];

            if ($byIndicator->count() !== count($indicatorNames)) {
                $missing[] = $regionCode;

                continue;
            }

            $isNational = $regionCode === '9999';
            $provinceName = (string) $rows->first()->region_name;
            $regionDisasterCount = $isNational
                ? array_sum($disasterCounts)
                : (int) ($disasterCounts[substr($regionCode, 0, 2)] ?? 0);

            $records[] = [
                'region_code' => $regionCode,
                'region_name' => $isNational ? 'Nasional (Indonesia)' : $provinceName,
                'province_name' => $isNational ? 'Nasional (Indonesia)' : $provinceName,
                'period_year' => $year,
                'poverty_rate' => (float) $byIndicator['kemiskinan']->value,
                'open_unemployment_rate' => (float) $byIndicator['tpt']->value,
                'gini_ratio' => (float) $byIndicator['gini']->value,
                'stunting_rate' => (float) $byIndicator['stunting']->value,
                'disaster_events_count' => $regionDisasterCount,
                'affected_population' => 0,
                'disaster_risk_score' => min(100, round(($regionDisasterCount / $maxEvents) * 100, 2)),
                'bps_source_citation' => "BPS WebAPI; observasi kemiskinan, TPT, Gini Ratio, dan stunting tahun {$year}.",
                'bnpb_source_citation' => $disastersByProvince->isEmpty()
                    ? "BNPB DIBI {$year} tidak tersedia; skor risiko bencana 0."
                    : "BNPB DIBI {$year}; skor risiko relatif terhadap jumlah kejadian tertinggi di wilayah dataset.",
                'last_synced_at' => now()->toDateTimeString(),
            ];
        }

        $provinceCount = count(array_filter(
            $records,
            static fn (array $record): bool => $record['region_code'] !== '9999',
        ));
        $hasNational = collect($records)->contains(
            static fn (array $record): bool => $record['region_code'] === '9999',
        );

        if ($provinceCount < 34 || ! $hasNational) {
            $this->error(sprintf(
                'Expected at least 34 complete province rows and one national row for %d; found %d provinces%s.',
                $year,
                $provinceCount,
                $hasNational ? '' : ' and no national row',
            ));

            if ($missing !== []) {
                $this->line('Incomplete indicator rows: '.implode(', ', $missing));
            }

            return self::FAILURE;
        }

        $count = $service->upsert($records);
        $this->info("Synchronized {$count} IKK rows for {$year}.");

        return self::SUCCESS;
    }
}
