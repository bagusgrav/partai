<?php

namespace Tests\Feature;

use App\Models\EconomicIndicator;
use App\Models\EconomicIndicatorObservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BpsEconomicDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_bps_inflation_history_is_fetched_and_saved_idempotently(): void
    {
        $this->travelTo(now()->setDate(2024, 11, 1));
        config([
            'bps.api_key' => 'test-key',
            'bps.api_url' => 'https://bps.test/v1/api',
            'bps.domain' => '0000',
            'bps.inflation_variable_id' => 1,
            'bps.economic_history_start_year' => 2024,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://bps.test/v1/api/list/model/th/domain/0000/var/1/key/test-key' => Http::response([
                'status' => 'OK',
                'data-availability' => 'available',
                'data' => [
                    ['page' => 1, 'pages' => 1, 'total' => 1],
                    [['th_id' => 100, 'th' => '2024']],
                ],
            ]),
            'https://bps.test/v1/api/list/model/data/domain/0000/var/1/th/100/key/test-key' => Http::response([
                'status' => 'OK',
                'data-availability' => 'available',
                'vervar' => [
                    ['val' => 1, 'label' => 'Januari'],
                    ['val' => 2, 'label' => 'Februari'],
                ],
                'var' => [['val' => 1]],
                'turvar' => [['val' => 0]],
                'tahun' => [['val' => 100, 'label' => '2024']],
                'turtahun' => [['val' => 0]],
                'datacontent' => [
                    '1101000' => 2.57,
                    '2101000' => 2.75,
                ],
            ]),
        ]);

        $this->artisan('bps:fetch-economic-data')->assertSuccessful();
        $this->artisan('bps:fetch-economic-data')->assertSuccessful();

        $this->assertDatabaseCount('economic_indicator_observations', 2);
        $this->assertDatabaseHas('economic_indicator_observations', [
            'indicator_name' => 'inflasi',
            'period_year' => 2024,
            'period_month' => 1,
            'value' => 2.57,
            'source' => 'BPS WebAPI',
        ]);

        Http::assertSentCount(4);
        Http::assertSent(fn ($request): bool => $request->hasHeader(
            'User-Agent',
            'PARTAI-CivicTech/1.0 (https://akuhadir.my.id; bagusgrav@gmail.com)',
        ) && $request->hasHeader('Accept', 'application/json'));
    }

    public function test_bps_http_failures_are_logged_with_status_and_response_body(): void
    {
        config([
            'bps.api_key' => 'test-key',
            'bps.api_url' => 'https://bps.test/v1/api',
            'bps.domain' => '0000',
            'bps.inflation_variable_id' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response('upstream unavailable /key/test-key', 503),
        ]);
        Log::spy();

        $this->artisan('bps:fetch-economic-data')->assertFailed();

        Log::shouldHaveReceived('error')
            ->withArgs(fn (string $message, array $context): bool => str_contains($message, '503')
                && ($context['status'] ?? null) === 503
                && ($context['body'] ?? null) === 'upstream unavailable /key/[redacted]');
    }

    public function test_bps_connection_errors_are_logged_before_the_user_facing_message_is_returned(): void
    {
        config([
            'bps.api_key' => 'test-key',
            'bps.api_url' => 'https://bps.test/v1/api',
            'bps.domain' => '0000',
            'bps.inflation_variable_id' => 1,
        ]);
        Http::preventStrayRequests();
        Http::fake(fn () => throw new ConnectionException(
            'cURL error 60: SSL certificate problem for https://bps.test/key/test-key',
        ));
        Log::spy();

        $this->artisan('bps:fetch-economic-data')
            ->expectsOutputToContain('Tidak dapat terhubung ke API BPS')
            ->assertFailed();

        Log::shouldHaveReceived('error')
            ->withArgs(fn (string $message, array $context): bool => str_contains($message, 'cURL error 60')
                && str_contains($message, '[redacted]')
                && ! str_contains($message, 'test-key')
                && ($context['description'] ?? null) === 'daftar periode inflasi BPS');
    }

    public function test_economy_indicators_are_grouped_and_ordered_for_time_series(): void
    {
        $this->travelTo(now()->setDate(2024, 3, 1));

        foreach ([2 => 2.75, 1 => 2.57] as $month => $value) {
            EconomicIndicatorObservation::query()->create([
                'indicator_name' => 'inflasi',
                'period_year' => 2024,
                'period_month' => $month,
                'value' => $value,
                'unit' => 'persen',
                'source' => 'BPS WebAPI',
            ]);
        }

        EconomicIndicator::query()->create([
            'indicator_type' => 'exchange_rate',
            'title' => 'Kurs USD/IDR',
            'value' => 16325.75,
            'unit' => 'rupiah per USD',
            'period' => '2024-02-01',
            'source' => 'https://open.er-api.com/v6/latest/USD',
            'trend' => 'naik',
        ]);

        $this->getJson('/api/economy/indicators')
            ->assertOk()
            ->assertJsonCount(2, 'indicators.inflasi')
            ->assertJsonPath('indicators.inflasi.0.period_month', 1)
            ->assertJsonPath('indicators.inflasi.1.value', 2.75)
            ->assertJsonPath('indicators.kurs_usd.0.period_year', 2024)
            ->assertJsonPath('indicators.kurs_usd.0.value', 16325.75);
    }

    public function test_economy_indicators_default_to_national_and_filter_by_region(): void
    {
        foreach ([
            ['region_code' => '9999', 'region_name' => 'Nasional (Indonesia)', 'period_year' => 2024, 'period_month' => 2, 'value' => 2.8],
            ['region_code' => '3100', 'region_name' => 'DKI Jakarta', 'period_year' => 2024, 'period_month' => 2, 'value' => 2.5],
            ['region_code' => '3100', 'region_name' => 'DKI Jakarta', 'period_year' => 2024, 'period_month' => 1, 'value' => 2.3],
            ['region_code' => '3200', 'region_name' => 'Jawa Barat', 'period_year' => 2024, 'period_month' => 1, 'value' => 2.1],
        ] as $observation) {
            EconomicIndicatorObservation::query()->create([
                'indicator_name' => 'inflasi',
                ...$observation,
                'unit' => 'persen',
                'source' => 'BPS WebAPI',
            ]);
        }

        $this->getJson('/api/economy/indicators')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.region_name', 'Nasional (Indonesia)')
            ->assertJsonPath('data.0.value', 2.8);

        $this->getJson('/api/economy/indicators?region_code=3100')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.period_month', 1)
            ->assertJsonPath('data.0.region_name', 'DKI Jakarta')
            ->assertJsonPath('data.1.period_month', 2)
            ->assertJsonPath('data.1.value', 2.5);
    }

    public function test_economy_indicators_filter_by_type_and_order_annual_series(): void
    {
        foreach ([
            ['indicator_name' => 'kemiskinan', 'region_code' => '9999', 'region_name' => 'Nasional (Indonesia)', 'period_year' => 2024, 'period_month' => null, 'value' => 9.1],
            ['indicator_name' => 'kemiskinan', 'region_code' => '9999', 'region_name' => 'Nasional (Indonesia)', 'period_year' => 2022, 'period_month' => null, 'value' => 9.6],
            ['indicator_name' => 'kemiskinan', 'region_code' => '3100', 'region_name' => 'DKI Jakarta', 'period_year' => 2024, 'period_month' => null, 'value' => 4.2],
            ['indicator_name' => 'tpt', 'region_code' => '9999', 'region_name' => 'Nasional (Indonesia)', 'period_year' => 2023, 'period_month' => null, 'value' => 5.3],
        ] as $observation) {
            EconomicIndicatorObservation::query()->create([
                ...$observation,
                'unit' => 'persen',
                'source' => 'BPS WebAPI',
            ]);
        }

        $this->getJson('/api/economy/indicators?type=kemiskinan&region_code=3100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.period_year', 2024)
            ->assertJsonPath('data.0.period_month', null)
            ->assertJsonPath('data.0.region_name', 'DKI Jakarta')
            ->assertJsonPath('data.0.value', 4.2)
            ->assertJsonCount(2, 'indicators.kemiskinan')
            ->assertJsonPath('indicators.kemiskinan.0.period_year', 2022)
            ->assertJsonPath('indicators.kemiskinan.1.period_year', 2024);
    }

    public function test_economy_regions_returns_only_unique_provinces_publicly(): void
    {
        foreach ([
            ['region_code' => '3100', 'region_name' => 'DKI Jakarta'],
            ['region_code' => '3200', 'region_name' => 'Jawa Barat', 'period_month' => 1],
            ['region_code' => '3200', 'region_name' => 'Jawa Barat', 'period_month' => 2],
            ['region_code' => '3171', 'region_name' => 'Jakarta Selatan'],
            ['region_code' => '9999', 'region_name' => 'Nasional (Indonesia)'],
        ] as $region) {
            EconomicIndicatorObservation::query()->create([
                'indicator_name' => 'inflasi',
                ...$region,
                'period_year' => 2024,
                'period_month' => $region['period_month'] ?? 1,
                'value' => 2.5,
                'unit' => 'persen',
                'source' => 'BPS WebAPI',
            ]);
        }

        $this->getJson('/api/economy/regions')
            ->assertOk()
            ->assertJsonCount(2, 'regions')
            ->assertJsonPath('regions.0.region_code', '3100')
            ->assertJsonPath('regions.0.region_name', 'DKI Jakarta')
            ->assertJsonPath('regions.1.region_code', '3200')
            ->assertJsonPath('regions.1.region_name', 'Jawa Barat');
    }
}
