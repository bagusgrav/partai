<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicatorObservation;
use App\Services\BpsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FetchBpsRegionalEconomicData extends Command
{
    protected $signature = 'bps:fetch-regional-economic-data';

    protected $description = 'Fetch BPS economic indicators for regencies and cities';

    public function handle(BpsApiService $bpsApi): int
    {
        try {
            $observations = $bpsApi->fetchRegionalEconomicIndicators();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($observations === []) {
            $this->info('BPS belum menyediakan observasi indikator ekonomi kabupaten/kota.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($observations): void {
            foreach ($observations as $observation) {
                EconomicIndicatorObservation::query()->updateOrCreate(
                    [
                        'indicator_name' => $observation['indicator_name'],
                        'region_code' => $observation['region_code'],
                        'period_year' => $observation['period_year'],
                        'period_month' => $observation['period_month'],
                    ],
                    [
                        'region_name' => $observation['region_name'],
                        'value' => $observation['value'],
                        'unit' => $observation['unit'],
                        'source' => $observation['source'],
                    ],
                );
            }
        });

        $this->info(sprintf('Saved %d BPS regency/city economic observations.', count($observations)));

        return self::SUCCESS;
    }
}
