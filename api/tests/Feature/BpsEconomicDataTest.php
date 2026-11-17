<?php

namespace Tests\Feature;

use App\Models\EconomicIndicator;
use App\Models\EconomicIndicatorObservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
}
