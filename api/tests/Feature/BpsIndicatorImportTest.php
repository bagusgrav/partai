<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BpsIndicatorImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_gini_period_is_logged_without_blocking_available_indicator_import(): void
    {
        $warning = 'BPS has no observations from 2021 to 2024 for variable 168';
        Log::spy();

        $this->artisan('bps:import-economic-indicators', [
            '--input' => json_encode([
                'records' => [[
                    'indicator_name' => 'stunting',
                    'region_code' => '1100',
                    'region_name' => 'Aceh',
                    'period_year' => 2024,
                    'value' => 20.5,
                ]],
                'warnings' => [$warning],
            ], JSON_THROW_ON_ERROR),
        ])
            ->expectsOutputToContain($warning)
            ->assertSuccessful();

        $this->assertDatabaseHas('economic_indicator_observations', [
            'indicator_name' => 'stunting',
            'region_code' => '1100',
            'period_year' => 2024,
            'value' => 20.5,
        ]);
        Log::shouldHaveReceived('warning')
            ->once()
            ->with($warning, ['source' => 'BPS WebAPI sync']);
    }

    public function test_bps_warnings_fail_only_when_strict_mode_is_enabled(): void
    {
        $warning = 'BPS has no observations from 2021 to 2024 for variable 168';
        $payload = [
            'records' => [[
                'indicator_name' => 'stunting',
                'region_code' => '1100',
                'region_name' => 'Aceh',
                'period_year' => 2024,
                'value' => 20.5,
            ]],
            'warnings' => [$warning],
        ];

        $this->artisan('bps:import-economic-indicators', [
            '--input' => json_encode($payload, JSON_THROW_ON_ERROR),
            '--strict' => true,
        ])->assertFailed();

        $this->assertDatabaseHas('economic_indicator_observations', [
            'indicator_name' => 'stunting',
            'region_code' => '1100',
            'period_year' => 2024,
            'value' => 20.5,
        ]);
    }
}
