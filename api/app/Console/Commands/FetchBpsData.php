<?php

namespace App\Console\Commands;

use App\Models\WelfareData;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchBpsData extends Command
{
    protected $signature = 'fetch:bps-data';

    protected $description = 'Fetch poverty and stunting indicators from BPS';

    public function handle(): int
    {
        $apiKey = config('bps.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            $records = $this->mockRecords();
            $this->warn('BPS_API_KEY is not configured; saving realistic mock data.');
        } else {
            $records = $this->fetchRecords($apiKey);

            if ($records === null) {
                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($records): void {
            foreach ($records as $record) {
                WelfareData::query()->updateOrCreate(
                    [
                        'region_code' => $record['region_code'],
                        'indicator' => $record['indicator'],
                        'year' => $record['year'],
                    ],
                    [
                        'region_name' => $record['region_name'],
                        'value' => $record['value'],
                        'unit' => $record['unit'],
                        'source' => 'BPS',
                    ],
                );
            }
        });

        $this->info(sprintf('Saved %d BPS welfare records.', count($records)));

        return self::SUCCESS;
    }

    /**
     * @return list<array{region_code: string, region_name: string, indicator: string, value: float, unit: string, year: int}>|null
     */
    private function fetchRecords(string $apiKey): ?array
    {
        $baseUrl = rtrim((string) config('bps.base_url'), '/');
        $indicators = config('bps.indicators');

        foreach (['kemiskinan', 'stunting'] as $indicator) {
            $settings = $indicators[$indicator] ?? [];

            if (
                ! is_array($settings)
                || ! is_numeric($settings['variable_id'] ?? null)
                || ! is_numeric($settings['period_id'] ?? null)
                || ! is_numeric($settings['year'] ?? null)
            ) {
                $this->error("Configure the BPS {$indicator} variable, period, and year IDs.");

                return null;
            }
        }

        $keyPath = rawurlencode($apiKey);
        $centralDomain = rawurlencode((string) config('bps.domain', '0000'));
        $domains = $this->requestJson(
            "{$baseUrl}/domain/type/prov/key/{$keyPath}",
            'BPS province list',
        );

        if ($domains === null) {
            return null;
        }

        $provinceDomains = $domains['data'][1] ?? null;

        if (! is_array($provinceDomains)) {
            $this->error('BPS returned an invalid province list.');

            return null;
        }

        $provinceCodes = [];
        $provinceNames = [];

        foreach ($provinceDomains as $provinceDomain) {
            if (! is_array($provinceDomain)) {
                continue;
            }

            $domainId = (string) ($provinceDomain['domain_id'] ?? '');
            $regionName = trim((string) ($provinceDomain['domain_name'] ?? ''));

            if (! preg_match('/^\d{4}$/', $domainId) || $domainId === '0000' || $regionName === '') {
                continue;
            }

            $regionCode = substr($domainId, 0, 2);
            $provinceCodes[$domainId] = $regionCode;
            $provinceNames[$regionCode] = $regionName;
        }

        $records = [];

        foreach (['kemiskinan', 'stunting'] as $indicator) {
            $settings = $indicators[$indicator];
            $variableId = rawurlencode((string) $settings['variable_id']);
            $periodId = rawurlencode((string) $settings['period_id']);
            $url = "{$baseUrl}/list/model/data/domain/{$centralDomain}/var/{$variableId}/th/{$periodId}/key/{$keyPath}";
            $response = $this->requestJson($url, "BPS {$indicator} data");

            if ($response === null) {
                return null;
            }

            if (($response['data-availability'] ?? null) !== 'available') {
                $this->error("BPS has no available data for {$indicator}.");

                return null;
            }

            $dataContent = $response['datacontent'] ?? null;
            $verticalVariables = $response['vervar'] ?? null;
            $variables = $response['var'] ?? null;
            $periods = $response['tahun'] ?? null;

            if (! is_array($dataContent) || ! is_array($verticalVariables) || ! is_array($variables) || ! is_array($periods)) {
                $this->error("BPS returned an incomplete data table for {$indicator}.");

                return null;
            }

            $derivedVariables = $response['turvar'] ?? [['val' => '']];
            $derivedPeriods = $response['turtahun'] ?? [['val' => '']];

            if (! is_array($derivedVariables) || ! is_array($derivedPeriods)) {
                $this->error("BPS returned invalid data dimensions for {$indicator}.");

                return null;
            }

            foreach ($verticalVariables as $verticalVariable) {
                if (! is_array($verticalVariable)) {
                    continue;
                }

                $regionCode = $this->regionCodeForVerticalVariable($verticalVariable, $provinceCodes, $provinceNames);

                if ($regionCode === null || ! isset($provinceNames[$regionCode])) {
                    continue;
                }

                $values = $this->valuesForProvince(
                    $verticalVariable,
                    $variables,
                    $derivedVariables,
                    $periods,
                    $derivedPeriods,
                    $dataContent,
                    (string) $settings['variable_id'],
                    (string) $settings['period_id'],
                );

                if ($values === []) {
                    continue;
                }

                if (count($values) !== 1 || ! is_numeric($values[0])) {
                    $this->error("BPS returned ambiguous or invalid values for {$indicator} in {$provinceNames[$regionCode]}.");

                    return null;
                }

                $records[] = [
                    'region_code' => $regionCode,
                    'region_name' => $provinceNames[$regionCode],
                    'indicator' => $indicator,
                    'value' => (float) $values[0],
                    'unit' => 'persen',
                    'year' => (int) $settings['year'],
                ];
            }
        }

        if ($records === []) {
            $this->error('BPS returned no usable province data.');

            return null;
        }

        return $records;
    }

    /**
     * @param  array<string, mixed>  $verticalVariable
     * @param  array<string, string>  $provinceCodes
     * @param  array<string, string>  $provinceNames
     */
    private function regionCodeForVerticalVariable(
        array $verticalVariable,
        array $provinceCodes,
        array $provinceNames,
    ): ?string {
        $verticalId = (string) ($verticalVariable['val'] ?? '');

        if (isset($provinceCodes[$verticalId])) {
            return $provinceCodes[$verticalId];
        }

        $label = $this->normalizeRegionName((string) ($verticalVariable['label'] ?? ''));

        foreach ($provinceNames as $regionCode => $regionName) {
            if ($label === $this->normalizeRegionName($regionName)) {
                return $regionCode;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $verticalVariable
     * @param  array<int, array<string, mixed>>  $variables
     * @param  array<int, array<string, mixed>>  $derivedVariables
     * @param  array<int, array<string, mixed>>  $periods
     * @param  array<int, array<string, mixed>>  $derivedPeriods
     * @param  array<string, int|float|string|null>  $dataContent
     * @return list<int|float|string|null>
     */
    private function valuesForProvince(
        array $verticalVariable,
        array $variables,
        array $derivedVariables,
        array $periods,
        array $derivedPeriods,
        array $dataContent,
        string $expectedVariableId,
        string $expectedPeriodId,
    ): array {
        $values = [];

        foreach ($variables as $variable) {
            if (! is_array($variable) || (string) ($variable['val'] ?? '') !== $expectedVariableId) {
                continue;
            }

            foreach ($periods as $period) {
                if (! is_array($period) || (string) ($period['val'] ?? '') !== $expectedPeriodId) {
                    continue;
                }

                foreach ($derivedVariables as $derivedVariable) {
                    if (! is_array($derivedVariable)) {
                        continue;
                    }

                    foreach ($derivedPeriods as $derivedPeriod) {
                        if (! is_array($derivedPeriod)) {
                            continue;
                        }

                        $key = implode('', [
                            (string) ($verticalVariable['val'] ?? ''),
                            (string) $variable['val'],
                            (string) ($derivedVariable['val'] ?? ''),
                            (string) $period['val'],
                            (string) ($derivedPeriod['val'] ?? ''),
                        ]);

                        if (array_key_exists($key, $dataContent)) {
                            $values[] = $dataContent[$key];
                        }
                    }
                }
            }
        }

        return $values;
    }

    private function normalizeRegionName(string $name): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]/i', '', $name) ?? '');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function requestJson(string $url, string $description): ?array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(20)
                ->retry([200, 500], throw: false)
                ->get($url);
        } catch (ConnectionException) {
            $this->error("Unable to connect while requesting {$description}.");

            return null;
        }

        if (! $response->successful()) {
            $this->error("{$description} failed with HTTP status {$response->status()}.");

            return null;
        }

        $payload = $response->json();

        if (
            ! is_array($payload)
            || ($payload['status'] ?? null) !== 'OK'
        ) {
            $this->error("{$description} returned an invalid API response.");

            return null;
        }

        return $payload;
    }

    /**
     * @return list<array{region_code: string, region_name: string, indicator: string, value: float, unit: string, year: int}>
     */
    private function mockRecords(): array
    {
        $provinces = [
            ['11', 'Aceh', 12.64, 26.4],
            ['12', 'Sumatera Utara', 7.99, 18.9],
            ['31', 'DKI Jakarta', 4.14, 17.6],
            ['32', 'Jawa Barat', 7.08, 21.7],
            ['33', 'Jawa Tengah', 10.47, 20.7],
            ['34', 'DI Yogyakarta', 10.83, 16.4],
            ['35', 'Jawa Timur', 9.56, 17.7],
            ['51', 'Bali', 3.8, 8.5],
            ['73', 'Sulawesi Selatan', 8.06, 23.8],
            ['94', 'Papua', 19.39, 28.6],
        ];
        $year = (int) config('bps.mock_year', 2024);
        $records = [];

        foreach ($provinces as [$regionCode, $regionName, $poverty, $stunting]) {
            $records[] = [
                'region_code' => $regionCode,
                'region_name' => $regionName,
                'indicator' => 'kemiskinan',
                'value' => $poverty,
                'unit' => 'persen',
                'year' => $year,
            ];
            $records[] = [
                'region_code' => $regionCode,
                'region_name' => $regionName,
                'indicator' => 'stunting',
                'value' => $stunting,
                'unit' => 'persen',
                'year' => $year,
            ];
        }

        return $records;
    }
}
