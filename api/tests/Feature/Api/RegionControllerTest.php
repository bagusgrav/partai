<?php

namespace Tests\Feature\Api;

use Database\Seeders\AdministrativeRegionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_api_returns_provinces_and_the_selected_provinces_regencies(): void
    {
        $this->seed(AdministrativeRegionSeeder::class);

        $this->getJson('/api/regions')
            ->assertOk()
            ->assertJsonCount(38, 'regions')
            ->assertJsonFragment([
                'id' => '33',
                'parent_id' => null,
                'name' => 'Jawa Tengah',
                'level' => 1,
            ]);

        $this->getJson('/api/regions?parent_id=33')
            ->assertOk()
            ->assertJsonCount(35, 'regions')
            ->assertJsonFragment(['id' => '3374', 'name' => 'Kota Semarang'])
            ->assertJsonFragment(['id' => '3319', 'name' => 'Kabupaten Kudus']);
    }

    public function test_parent_id_must_identify_a_province(): void
    {
        $this->seed(AdministrativeRegionSeeder::class);

        $this->getJson('/api/regions?parent_id=3319')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_region_seeder_is_repeatable_and_seeds_all_provinces_and_regencies(): void
    {
        $this->seed(AdministrativeRegionSeeder::class);
        $this->seed(AdministrativeRegionSeeder::class);

        $this->assertDatabaseCount('regions', 552);
        $this->assertDatabaseHas('regions', ['id' => '33', 'level' => 1]);
        $this->assertDatabaseHas('regions', [
            'id' => '3374',
            'parent_id' => '33',
            'name' => 'Kota Semarang',
            'level' => 2,
        ]);
    }
}
