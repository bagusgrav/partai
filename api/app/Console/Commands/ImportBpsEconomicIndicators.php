<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicatorObservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

class ImportBpsEconomicIndicators extends Command
{
    protected $signature = 'bps:import-economic-indicators {--stdin : Read JSON records from standard input}';

    protected $description = 'Import regional Gini and stunting observations produced by the BPS fetch script';

    public function handle(): int
    {
        if (! $this->option('stdin')) {
            $this->error('Use --stdin to provide the JSON records.');

            return self::FAILURE;
        }

        $input = file_get_contents('php://stdin');

        if (! is_string($input) || trim($input) === '') {
            $this->error('No JSON records were provided.');

            return self::FAILURE;
        }

        try {
            $records = json_decode($input, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('The input is not valid JSON.');

            return self::FAILURE;
        }

        if (! is_array($records) || ! array_is_list($records) || $records === []) {
            $this->error('The JSON input must be a non-empty list of observations.');

            return self::FAILURE;
        }

        $seen = [];

        foreach ($records as $record) {
            if (! is_array($record)
                || ! in_array($record['indicator_name'] ?? null, ['gini', 'stunting'], true)
                || ! is_string($record['region_code'] ?? null)
                || preg_match('/^\d{4}$/', $record['region_code']) !== 1
                || ! is_string($record['region_name'] ?? null)
                || trim($record['region_name']) === ''
                || ! is_int($record['period_year'] ?? null)
                || $record['period_year'] < 2021
                || $record['period_year'] > 2024
                || ! is_numeric($record['value'] ?? null)
                || ! is_finite((float) $record['value'])) {
                $this->error('The JSON input contains an invalid BPS observation.');

                return self::FAILURE;
            }

            $key = implode(':', [
                $record['indicator_name'],
                $record['region_code'],
                $record['period_year'],
            ]);

            if (isset($seen[$key])) {
                $this->error("The JSON input contains duplicate observation {$key}.");

                return self::FAILURE;
            }

            $seen[$key] = true;
        }

        DB::transaction(function () use ($records): void {
            foreach ($records as $record) {
                EconomicIndicatorObservation::query()->updateOrCreate(
                    [
                        'indicator_name' => $record['indicator_name'],
                        'region_code' => $record['region_code'],
                        'period_year' => $record['period_year'],
                        'period_month' => null,
                    ],
                    [
                        'region_name' => $record['region_name'],
                        'value' => $record['value'],
                        'unit' => $record['indicator_name'] === 'gini' ? 'rasio' : 'persen',
                        'source' => 'BPS WebAPI',
                    ],
                );
            }
        });

        $this->info(sprintf('Imported %d BPS Gini and stunting observations.', count($records)));

        return self::SUCCESS;
    }
}
