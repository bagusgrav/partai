<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicatorObservation;
use App\Services\BpsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FetchBpsEconomicData extends Command
{
    protected $signature = 'bps:fetch-economic-data';

    protected $description = 'Fetch historical monthly inflation data from the BPS WebAPI';

    public function handle(BpsApiService $bpsApi): int
    {
        try {
            $observations = $bpsApi->fetchInflationHistory();
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        DB::transaction(function () use ($observations): void {
            foreach ($observations as $observation) {
                EconomicIndicatorObservation::query()->updateOrCreate(
                    [
                        'indicator_name' => $observation['indicator_name'],
                        'period_year' => $observation['period_year'],
                        'period_month' => $observation['period_month'],
                    ],
                    [
                        'value' => $observation['value'],
                        'unit' => $observation['unit'],
                        'source' => $observation['source'],
                    ],
                );
            }
        });

        $this->info(sprintf('Saved %d historical BPS economic observations.', count($observations)));

        return self::SUCCESS;
    }
}
