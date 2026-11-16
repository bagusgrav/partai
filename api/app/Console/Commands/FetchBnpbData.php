<?php

namespace App\Console\Commands;

use App\Models\WelfareData;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FetchBnpbData extends Command
{
    protected $signature = 'fetch:bnpb-data';

    protected $description = 'Fetch province-level disaster counts from BNPB';

    public function handle(): int
    {
        $apiKey = config('bnpb.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            $records = $this->mockRecords();
            $this->warn('BNPB_API_KEY is not configured; saving realistic mock data.');
        } else {
            $features = $this->fetchFeatures($apiKey);

            if ($features === null) {
                return self::FAILURE;
            }

            $records = $this->aggregateFeatures($features);

            if ($records === null) {
                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($records): void {
            foreach ($records as $record) {
                WelfareData::query()->updateOrCreate(
                    [
                        'region_code' => $record['region_code'],
                        'indicator' => 'bencana',
                        'year' => $record['year'],
                    ],
                    [
                        'region_name' => $record['region_name'],
                        'value' => $record['value'],
                        'unit' => 'kejadian',
                        'source' => 'BNPB',
                    ],
                );
            }
        });

        $this->info(sprintf('Saved %d BNPB province records.', count($records)));

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function fetchFeatures(string $apiKey): ?array
    {
        $endpoint = (string) config('bnpb.api_url');
        $features = [];
        $offset = 0;
        $exceededTransferLimit = true;

        while ($exceededTransferLimit) {
            try {
                $response = Http::acceptJson()
                    ->withHeaders(['X-API-Key' => $apiKey])
                    ->connectTimeout(5)
                    ->timeout(30)
                    ->retry([200, 500], throw: false)
                    ->get($endpoint, [
                        'where' => '1=1',
                        'outFields' => 'kode_prop_,nama_prop_,BANJIR,CUACA_EKSTREM,TANAH_LONGSOR',
                        'returnGeometry' => 'false',
                        'resultOffset' => $offset,
                        'resultRecordCount' => 2000,
                        'f' => 'json',
                    ]);
            } catch (ConnectionException) {
                $this->error('Unable to connect to the BNPB API.');

                return null;
            }

            if (! $response->successful()) {
                $this->error("BNPB API request failed with HTTP status {$response->status()}.");

                return null;
            }

            $payload = $response->json();

            if (! is_array($payload) || isset($payload['error']) || ! is_array($payload['features'] ?? null)) {
                $this->error('BNPB returned an invalid ArcGIS feature response.');

                return null;
            }

            $page = $payload['features'];

            if ($page === [] && ($payload['exceededTransferLimit'] ?? false)) {
                $this->error('BNPB reported more features but returned an empty page.');

                return null;
            }

            $features = [...$features, ...$page];
            $offset += count($page);
            $exceededTransferLimit = (bool) ($payload['exceededTransferLimit'] ?? false);
        }

        return $features;
    }

    /**
     * @param  list<array<string, mixed>>  $features
     * @return list<array{region_code: string, region_name: string, value: int, year: int}>|null
     */
    private function aggregateFeatures(array $features): ?array
    {
        $provinces = [];

        foreach ($features as $feature) {
            $attributes = $feature['attributes'] ?? null;

            if (! is_array($attributes)) {
                $this->error('BNPB returned a feature without attributes.');

                return null;
            }

            $rawCode = $attributes['kode_prop_'] ?? null;
            $regionName = trim((string) ($attributes['nama_prop_'] ?? ''));

            if (! is_numeric($rawCode) || $regionName === '') {
                $this->error('BNPB returned a feature without a valid province code or name.');

                return null;
            }

            $regionCode = str_pad((string) (int) $rawCode, 2, '0', STR_PAD_LEFT);
            $formattedName = mb_convert_case(mb_strtolower($regionName), MB_CASE_TITLE, 'UTF-8');
            $formattedName = match ($formattedName) {
                'Dki Jakarta' => 'DKI Jakarta',
                'Di Yogyakarta' => 'DI Yogyakarta',
                default => $formattedName,
            };
            $count = 0;

            foreach (['BANJIR', 'CUACA_EKSTREM', 'TANAH_LONGSOR'] as $field) {
                $value = $attributes[$field] ?? null;

                if ($value !== null && (! is_numeric($value) || (float) $value < 0)) {
                    $this->error("BNPB returned an invalid {$field} count for {$regionName}.");

                    return null;
                }

                $count += (int) ($value ?? 0);
            }

            $provinces[$regionCode] ??= [
                'region_code' => $regionCode,
                'region_name' => $formattedName,
                'value' => 0,
                'year' => (int) config('bnpb.data_year', 2025),
            ];
            $provinces[$regionCode]['value'] += $count;
        }

        if ($provinces === []) {
            $this->error('BNPB returned no province disaster records.');

            return null;
        }

        return array_values($provinces);
    }

    /**
     * @return list<array{region_code: string, region_name: string, value: int, year: int}>
     */
    private function mockRecords(): array
    {
        $provinces = [
            ['11', 'Aceh', 68],
            ['12', 'Sumatera Utara', 110],
            ['31', 'DKI Jakarta', 41],
            ['32', 'Jawa Barat', 150],
            ['33', 'Jawa Tengah', 85],
            ['34', 'DI Yogyakarta', 28],
            ['35', 'Jawa Timur', 92],
            ['51', 'Bali', 43],
            ['73', 'Sulawesi Selatan', 76],
            ['94', 'Papua', 55],
        ];

        return array_map(
            fn (array $province): array => [
                'region_code' => $province[0],
                'region_name' => $province[1],
                'value' => $province[2],
                'year' => (int) now()->year,
            ],
            $provinces,
        );
    }
}
