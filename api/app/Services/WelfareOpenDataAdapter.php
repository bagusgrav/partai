<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WelfareOpenDataAdapter
{
    /**
     * Return a complete joined dataset, or null when either configured source
     * is unavailable or does not provide the expected normalized records.
     *
     * @return list<array<string, int|float|string>>|null
     */
    public function records(): ?array
    {
        $bpsUrl = config('bps.ikk_data_url');
        $bnpbUrl = config('bnpb.ikk_data_url');

        if (! is_string($bpsUrl) || trim($bpsUrl) === '' || ! is_string($bnpbUrl) || trim($bnpbUrl) === '') {
            return null;
        }

        $bpsApiKey = config('bps.api_key');
        if (is_string($bpsApiKey) && $bpsApiKey !== '') {
            $bpsUrl = str_replace('{api_key}', rawurlencode($bpsApiKey), $bpsUrl);
        }
        $bpsRows = $this->requestRows(
            $bpsUrl,
            array_filter(['key' => $bpsApiKey], static fn ($value): bool => is_string($value) && $value !== ''),
        );
        $resourceId = config('bnpb.ikk_resource_id');
        if (is_string($resourceId) && $resourceId !== '') {
            $bnpbUrl = str_replace('{resource_id}', rawurlencode($resourceId), $bnpbUrl);
        }
        $bnpbApiKey = config('bnpb.api_key');
        $bnpbRows = $this->requestRows(
            $bnpbUrl,
            array_filter(['resource_id' => $resourceId], static fn ($value): bool => is_string($value) && $value !== ''),
            is_string($bnpbApiKey) && $bnpbApiKey !== '' ? ['Authorization' => $bnpbApiKey] : [],
        );

        if ($bpsRows === null || $bnpbRows === null) {
            return null;
        }

        $disasters = [];

        foreach ($bnpbRows as $row) {
            if (! is_array($row) || ! isset($row['region_code'], $row['period_year'])) {
                continue;
            }

            $key = (string) $row['region_code'].':'.(int) $row['period_year'];

            if (! is_numeric($row['disaster_events_count'] ?? null)
                || ! is_numeric($row['affected_population'] ?? null)
                || ! is_numeric($row['disaster_risk_score'] ?? null)) {
                continue;
            }

            $disasters[$key] = [
                'disaster_events_count' => (int) $row['disaster_events_count'],
                'affected_population' => (int) $row['affected_population'],
                'disaster_risk_score' => (float) $row['disaster_risk_score'],
                'bnpb_source_citation' => (string) ($row['bnpb_source_citation'] ?? 'BNPB, Data Informasi Bencana Indonesia (DIBI).'),
            ];
        }

        $records = [];

        foreach ($bpsRows as $row) {
            if (! is_array($row) || ! isset($row['region_code'], $row['period_year'])) {
                continue;
            }

            $key = (string) $row['region_code'].':'.(int) $row['period_year'];

            if (! isset($disasters[$key])
                || ! is_string($row['region_name'] ?? null)
                || ! is_string($row['province_name'] ?? null)
                || ! is_numeric($row['poverty_rate'] ?? null)
                || ! is_numeric($row['gini_ratio'] ?? null)
                || ! is_numeric($row['open_unemployment_rate'] ?? null)
                || ! is_numeric($row['stunting_rate'] ?? null)) {
                continue;
            }

            $records[] = [
                'region_code' => (string) $row['region_code'],
                'region_name' => $row['region_name'],
                'province_name' => $row['province_name'],
                'period_year' => (int) $row['period_year'],
                'poverty_rate' => (float) $row['poverty_rate'],
                'gini_ratio' => (float) $row['gini_ratio'],
                'open_unemployment_rate' => (float) $row['open_unemployment_rate'],
                'stunting_rate' => (float) $row['stunting_rate'],
                ...$disasters[$key],
                'bps_source_citation' => (string) ($row['bps_source_citation'] ?? 'BPS WebAPI, data indikator wilayah.'),
                'last_synced_at' => now()->toDateTimeString(),
            ];
        }

        return $records === [] ? null : $records;
    }

    /**
     * @param  array<string, string>  $query
     * @param  array<string, string>  $headers
     * @return list<mixed>|null
     */
    private function requestRows(string $url, array $query, array $headers = []): ?array
    {
        try {
            $response = Http::withHeaders($headers)
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->get($url, $query);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            return null;
        }

        $rows = $payload['records'] ?? $payload['data'] ?? $payload['result']['records'] ?? null;

        return is_array($rows) && array_is_list($rows) ? $rows : null;
    }
}
