<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BpsApiService
{
    private const MONTHS = [
        'januari' => 1,
        'january' => 1,
        'februari' => 2,
        'february' => 2,
        'maret' => 3,
        'march' => 3,
        'april' => 4,
        'mei' => 5,
        'may' => 5,
        'juni' => 6,
        'june' => 6,
        'juli' => 7,
        'july' => 7,
        'agustus' => 8,
        'august' => 8,
        'september' => 9,
        'oktober' => 10,
        'october' => 10,
        'november' => 11,
        'desember' => 12,
        'december' => 12,
    ];

    /**
     * @return list<array{indicator_name: string, period_year: int, period_month: int, value: float, unit: string, source: string}>
     */
    public function fetchInflationHistory(): array
    {
        $apiKey = config('bps.api_key');
        $baseUrl = rtrim((string) config('bps.api_url'), '/');
        $domain = rawurlencode((string) config('bps.domain', '0000'));
        $variableId = config('bps.inflation_variable_id');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('BPS_API_KEY belum dikonfigurasi.');
        }

        if ($baseUrl === '' || ! is_numeric($variableId)) {
            throw new RuntimeException('URL API atau var_id inflasi BPS tidak valid.');
        }

        // BPS WebAPI var_id 1 is the national monthly inflation series.
        // Its th_id values are variable-specific, so resolve them from the BPS period catalog.
        $periods = $this->fetchPeriods($baseUrl, $domain, (string) $variableId, $apiKey);
        $startYear = (int) config('bps.economic_history_start_year', 2015);
        $currentYear = (int) now()->format('Y');
        $observations = [];

        foreach ($periods as $period) {
            $year = (int) $period['year'];

            if ($year < $startYear || $year > $currentYear) {
                continue;
            }

            $payload = $this->requestJson(
                "{$baseUrl}/list/model/data/domain/{$domain}/var/".rawurlencode((string) $variableId)
                    .'/th/'.rawurlencode((string) $period['id']).'/key/'.rawurlencode($apiKey),
                "data inflasi BPS tahun {$year}",
            );
            $observations = [...$observations, ...$this->observationsForPeriod($payload, $year)];
        }

        if ($observations === []) {
            throw new RuntimeException('BPS tidak mengembalikan data inflasi bulanan yang dapat digunakan.');
        }

        return $observations;
    }

    /**
     * @return list<array{id: string, year: string}>
     */
    private function fetchPeriods(string $baseUrl, string $domain, string $variableId, string $apiKey): array
    {
        $periods = [];
        $page = 1;
        $totalPages = 1;

        do {
            $url = "{$baseUrl}/list/model/th/domain/{$domain}/var/".rawurlencode($variableId);

            if ($page > 1) {
                $url .= "/page/{$page}";
            }

            $payload = $this->requestJson($url.'/key/'.rawurlencode($apiKey), 'daftar periode inflasi BPS');
            $data = $payload['data'] ?? null;
            $rows = is_array($data) ? ($data[1] ?? null) : null;

            if (! is_array($rows)) {
                throw new RuntimeException('BPS mengembalikan daftar periode inflasi dengan format tidak valid.');
            }

            foreach ($rows as $row) {
                if (! is_array($row) || ! is_numeric($row['th_id'] ?? null) || ! is_numeric($row['th'] ?? null)) {
                    continue;
                }

                $periods[] = [
                    'id' => (string) $row['th_id'],
                    'year' => (string) $row['th'],
                ];
            }

            $metadata = is_array($data) ? ($data[0] ?? null) : null;
            $totalPages = is_array($metadata) && is_numeric($metadata['pages'] ?? null)
                ? max(1, (int) $metadata['pages'])
                : 1;
            $page++;
        } while ($page <= $totalPages);

        if ($periods === []) {
            throw new RuntimeException('BPS tidak menyediakan periode untuk var_id inflasi yang dikonfigurasi.');
        }

        usort($periods, static fn (array $left, array $right): int => (int) $left['year'] <=> (int) $right['year']);

        return $periods;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{indicator_name: string, period_year: int, period_month: int, value: float, unit: string, source: string}>
     */
    private function observationsForPeriod(array $payload, int $year): array
    {
        if (($payload['data-availability'] ?? null) !== 'available') {
            return [];
        }

        $dataContent = $payload['datacontent'] ?? null;

        if (! is_array($dataContent)) {
            throw new RuntimeException("BPS tidak mengembalikan tabel data inflasi untuk tahun {$year}.");
        }

        $dimensionNames = ['vervar', 'var', 'turvar', 'tahun', 'turtahun'];
        $dimensions = [];

        foreach ($dimensionNames as $name) {
            $values = $payload[$name] ?? [];
            $dimensions[$name] = is_array($values) && $values !== [] ? $values : [['val' => '']];
        }

        $observations = [];

        foreach ($dimensions['vervar'] as $vervar) {
            foreach ($dimensions['var'] as $variable) {
                foreach ($dimensions['turvar'] as $turvar) {
                    foreach ($dimensions['tahun'] as $yearDimension) {
                        foreach ($dimensions['turtahun'] as $derivedYear) {
                            if (! is_array($vervar) || ! is_array($variable) || ! is_array($turvar)
                                || ! is_array($yearDimension) || ! is_array($derivedYear)) {
                                continue;
                            }

                            $month = $this->monthFromDimensions($vervar, $turvar, $derivedYear);

                            if ($month === null) {
                                continue;
                            }

                            $key = implode('', [
                                (string) ($vervar['val'] ?? ''),
                                (string) ($variable['val'] ?? ''),
                                (string) ($turvar['val'] ?? ''),
                                (string) ($yearDimension['val'] ?? ''),
                                (string) ($derivedYear['val'] ?? ''),
                            ]);
                            $value = $dataContent[$key] ?? null;

                            if (! is_numeric($value) || ! is_finite((float) $value)) {
                                continue;
                            }

                            $observations[] = [
                                'indicator_name' => 'inflasi',
                                'period_year' => $year,
                                'period_month' => $month,
                                'value' => (float) $value,
                                'unit' => 'persen',
                                'source' => 'BPS WebAPI',
                            ];
                        }
                    }
                }
            }
        }

        return $observations;
    }

    /**
     * @param  array<string, mixed>  ...$dimensions
     */
    private function monthFromDimensions(array ...$dimensions): ?int
    {
        foreach ($dimensions as $dimension) {
            $label = mb_strtolower(trim((string) ($dimension['label'] ?? '')));

            foreach (self::MONTHS as $name => $month) {
                if (preg_match('/(?:^|[^a-z])'.preg_quote($name, '/').'(?=$|[^a-z])/', $label) === 1) {
                    return $month;
                }
            }
        }

        foreach ($dimensions as $dimension) {
            $value = $dimension['val'] ?? null;

            if (is_numeric($value) && (int) $value >= 1 && (int) $value <= 12) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestJson(string $url, string $description): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'PARTAI-CivicTech/1.0 (https://akuhadir.my.id; bagusgrav@gmail.com)',
                'Accept' => 'application/json',
            ])
                ->withOptions(['verify' => config('bps.ssl_verify', true)])
                ->connectTimeout(5)
                ->timeout(30)
                ->retry([200, 500], throw: false)
                ->get($url);
        } catch (ConnectionException $exception) {
            Log::error($this->redactApiKey($exception->getMessage()), ['description' => $description]);

            throw new RuntimeException("Tidak dapat terhubung ke API BPS saat meminta {$description}.", previous: $exception);
        }

        if (! $response->successful()) {
            Log::error("BPS API request failed with HTTP {$response->status()}.", [
                'description' => $description,
                'status' => $response->status(),
                'body' => $this->redactApiKey($response->body()),
            ]);

            throw new RuntimeException("Permintaan {$description} ke BPS gagal dengan HTTP {$response->status()}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['status'] ?? null) !== 'OK') {
            throw new RuntimeException("API BPS mengembalikan respons tidak valid untuk {$description}.");
        }

        return $payload;
    }

    private function redactApiKey(string $value): string
    {
        $apiKey = config('bps.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            return $value;
        }

        return str_replace(array_unique([$apiKey, rawurlencode($apiKey)]), '[redacted]', $value);
    }
}
