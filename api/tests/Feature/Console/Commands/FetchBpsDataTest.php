<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchBpsDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_an_api_key_it_saves_idempotent_mock_records(): void
    {
        config(['bps.api_key' => null]);

        $this->artisan('fetch:bps-data')->assertSuccessful();
        $this->artisan('fetch:bps-data')->assertSuccessful();

        $this->assertDatabaseCount('welfare_data', 20);
        $this->assertDatabaseHas('welfare_data', [
            'region_code' => '32',
            'indicator' => 'kemiskinan',
            'year' => 2024,
            'source' => 'BPS',
        ]);
    }

    public function test_with_an_api_key_but_without_series_ids_it_fails_without_saving_mock_data(): void
    {
        config(['bps.api_key' => 'test-key']);
        Http::preventStrayRequests();

        $this->artisan('fetch:bps-data')->assertFailed();

        $this->assertDatabaseCount('welfare_data', 0);
    }

    public function test_with_an_api_key_it_saves_values_for_each_province(): void
    {
        config([
            'bps.api_key' => 'test-key',
            'bps.domain' => '0000',
            'bps.indicators.kemiskinan' => [
                'variable_id' => 145,
                'period_id' => 100,
                'year' => 2024,
            ],
            'bps.indicators.stunting' => [
                'variable_id' => 250,
                'period_id' => 101,
                'year' => 2023,
            ],
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'https://webapi.bps.go.id/v1/api/domain/type/prov/key/test-key' => Http::response([
                'status' => 'OK',
                'data-availability' => 'available',
                'data' => [
                    ['page' => 1, 'pages' => 1, 'total' => 2],
                    [
                        ['domain_id' => '1100', 'domain_name' => 'Aceh'],
                        ['domain_id' => '1200', 'domain_name' => 'Sumatera Utara'],
                    ],
                ],
            ]),
            'https://webapi.bps.go.id/v1/api/list/model/data/domain/0000/var/145/th/100/key/test-key' => Http::response([
                'status' => 'OK',
                'data-availability' => 'available',
                'vervar' => [
                    ['val' => 1100, 'label' => 'Aceh'],
                    ['val' => 1200, 'label' => 'Sumatera Utara'],
                ],
                'var' => [['val' => 145]],
                'turvar' => [['val' => 0]],
                'tahun' => [['val' => 100]],
                'turtahun' => [['val' => 0]],
                'datacontent' => [
                    '110014501000' => 7.5,
                    '120014501000' => 8.2,
                ],
            ]),
            'https://webapi.bps.go.id/v1/api/list/model/data/domain/0000/var/250/th/101/key/test-key' => Http::response([
                'status' => 'OK',
                'data-availability' => 'available',
                'vervar' => [
                    ['val' => 1100, 'label' => 'Aceh'],
                    ['val' => 1200, 'label' => 'Sumatera Utara'],
                ],
                'var' => [['val' => 250]],
                'turvar' => [['val' => 0]],
                'tahun' => [['val' => 101]],
                'turtahun' => [['val' => 0]],
                'datacontent' => [
                    '110025001010' => 26.4,
                    '120025001010' => 18.9,
                ],
            ]),
        ]);

        $this->artisan('fetch:bps-data')->assertSuccessful();

        Http::assertSentCount(3);
        $this->assertDatabaseCount('welfare_data', 4);
        $this->assertDatabaseHas('welfare_data', [
            'region_code' => '11',
            'region_name' => 'Aceh',
            'indicator' => 'kemiskinan',
            'value' => 7.5,
            'year' => 2024,
            'source' => 'BPS',
        ]);
    }
}
