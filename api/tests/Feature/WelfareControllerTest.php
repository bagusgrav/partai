<?php

namespace Tests\Feature;

use App\Models\WelfareData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelfareControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_averages_the_latest_indicator_year_and_current_year_disasters(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 16)->startOfDay());

        WelfareData::factory()->create([
            'region_code' => '32',
            'region_name' => 'Jawa Barat',
            'indicator' => 'kemiskinan',
            'value' => 7.62,
            'year' => 2023,
        ]);
        WelfareData::factory()->create([
            'region_code' => '35',
            'region_name' => 'Jawa Timur',
            'indicator' => 'kemiskinan',
            'value' => 10.50,
            'year' => 2023,
        ]);
        WelfareData::factory()->create([
            'region_code' => '32',
            'indicator' => 'kemiskinan',
            'value' => 99,
            'year' => 2022,
        ]);
        WelfareData::factory()->create([
            'region_code' => '32',
            'indicator' => 'stunting',
            'value' => 21.70,
            'year' => 2023,
        ]);
        WelfareData::factory()->create([
            'region_code' => '35',
            'region_name' => 'Jawa Timur',
            'indicator' => 'stunting',
            'value' => 17.70,
            'year' => 2023,
        ]);
        WelfareData::factory()->create([
            'region_code' => '32',
            'indicator' => 'bencana',
            'value' => 150,
            'unit' => 'kejadian',
            'year' => 2026,
            'source' => 'BNPB',
        ]);
        WelfareData::factory()->create([
            'region_code' => '35',
            'region_name' => 'Jawa Timur',
            'indicator' => 'bencana',
            'value' => 92,
            'unit' => 'kejadian',
            'year' => 2026,
            'source' => 'BNPB',
        ]);
        WelfareData::factory()->create([
            'region_code' => '32',
            'indicator' => 'bencana',
            'value' => 999,
            'unit' => 'kejadian',
            'year' => 2025,
            'source' => 'BNPB',
        ]);

        $this->getJson('/api/welfare/summary')
            ->assertOk()
            ->assertJsonPath('summary.average_poverty', 9.06)
            ->assertJsonPath('summary.poverty_year', 2023)
            ->assertJsonPath('summary.average_stunting', 19.7)
            ->assertJsonPath('summary.stunting_year', 2023)
            ->assertJsonPath('summary.disaster_events', 242)
            ->assertJsonPath('summary.disaster_year', 2026);
    }

    public function test_region_list_returns_one_row_per_region_and_details_preserve_string_codes(): void
    {
        WelfareData::factory()->create([
            'region_code' => '032',
            'region_name' => 'Wilayah Contoh',
        ]);
        WelfareData::factory()->create([
            'region_code' => '032',
            'region_name' => 'Wilayah Contoh',
            'indicator' => 'stunting',
            'value' => 18.5,
        ]);

        $this->getJson('/api/welfare/regions')
            ->assertOk()
            ->assertJsonCount(1, 'regions')
            ->assertJsonPath('regions.0.region_code', '032')
            ->assertJsonPath('regions.0.poverty', 7.62)
            ->assertJsonPath('regions.0.stunting', 18.5);

        $this->getJson('/api/welfare/region/032')
            ->assertOk()
            ->assertJsonPath('region_code', '032')
            ->assertJsonPath('region_name', 'Wilayah Contoh')
            ->assertJsonCount(2, 'data');
    }

    public function test_region_detail_returns_not_found_for_unknown_region(): void
    {
        $this->getJson('/api/welfare/region/999')->assertNotFound();
    }
}
