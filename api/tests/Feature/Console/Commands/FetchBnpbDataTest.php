<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchBnpbDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_an_api_key_it_saves_idempotent_mock_records(): void
    {
        config(['bnpb.api_key' => null]);

        $this->artisan('fetch:bnpb-data')->assertSuccessful();
        $this->artisan('fetch:bnpb-data')->assertSuccessful();

        $this->assertDatabaseCount('welfare_data', 10);
        $this->assertDatabaseHas('welfare_data', [
            'region_code' => '32',
            'indicator' => 'bencana',
            'year' => (int) now()->year,
            'source' => 'BNPB',
        ]);
    }

    public function test_with_an_api_key_it_aggregates_district_counts_by_province(): void
    {
        config([
            'bnpb.api_key' => 'test-key',
            'bnpb.data_year' => 2025,
        ]);
        Http::preventStrayRequests();
        Http::fake([
            'gis.bnpb.go.id/server/rest/services/Jumlah_Bencana_kabkot_2025/MapServer/13/query*' => Http::response([
                'features' => [
                    ['attributes' => [
                        'kode_prop_' => 51,
                        'nama_prop_' => 'BALI',
                        'BANJIR' => 7,
                        'CUACA_EKSTREM' => 24,
                        'TANAH_LONGSOR' => 39,
                    ]],
                    ['attributes' => [
                        'kode_prop_' => 51,
                        'nama_prop_' => 'BALI',
                        'BANJIR' => 1,
                        'CUACA_EKSTREM' => 2,
                        'TANAH_LONGSOR' => 3,
                    ]],
                ],
                'exceededTransferLimit' => false,
            ]),
        ]);

        $this->artisan('fetch:bnpb-data')->assertSuccessful();

        Http::assertSentCount(1);
        $this->assertDatabaseHas('welfare_data', [
            'region_code' => '51',
            'region_name' => 'Bali',
            'indicator' => 'bencana',
            'value' => 76,
            'year' => 2025,
            'source' => 'BNPB',
        ]);
    }
}
