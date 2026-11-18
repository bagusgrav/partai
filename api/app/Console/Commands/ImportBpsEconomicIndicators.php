<?php

namespace App\Console\Commands;

use App\Models\EconomicIndicatorObservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;

class ImportBpsEconomicIndicators extends Command
{
    protected $signature = 'bps:import-economic-indicators {--stdin : Read JSON records from standard input} {--input= : Read a JSON payload directly} {--strict : Fail when the payload contains warnings}';

    protected $description = 'Import regional Gini and stunting observations produced by the BPS fetch script';

    public function handle(): int
    {
        if ($this->option('stdin') === ($this->option('input') !== null)) {
            $this->error('Provide exactly one of --stdin or --input.');

            return self::FAILURE;
        }

        $input = $this->option('stdin')
            ? file_get_contents('php://stdin')
            : $this->option('input');

        if (! is_string($input) || trim($input) === '') {
            $this->error('No JSON payload was provided.');

            return self::FAILURE;
        }

        try {
            $payload = json_decode($input, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->error('The input is not valid JSON.');

            return self::FAILURE;
        }

        if (! is_array($payload)) {
            $this->error('The JSON payload must contain observations.');

            return self::FAILURE;
        }

        if (array_is_list($payload)) {
            $records = $payload;
            $warnings = [];
        } else {
            $records = $payload['records'] ?? null;
            $warnings = $payload['warnings'] ?? null;
        }

        if (! is_array($records)
            || ! array_is_list($records)
            || ! is_array($warnings)
            || ! array_is_list($warnings)
            || array_filter($warnings, static fn (mixed $warning): bool => ! is_string($warning) || trim($warning) === '') !== []) {
            $this->error('The JSON payload must contain a list of observations and warning messages.');

            return self::FAILURE;
        }

        if ($records === [] && $warnings === []) {
            $this->error('The JSON payload contains neither observations nor warnings.');

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

        foreach ($warnings as $warning) {
            $this->warn($warning);
            Log::warning($warning, ['source' => 'BPS WebAPI sync']);
        }

        $this->info(sprintf('Imported %d BPS Gini and stunting observations.', count($records)));

        if ($this->option('strict') && $warnings !== []) {
            $this->error('Strict mode enabled; the BPS sync completed with warnings.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
