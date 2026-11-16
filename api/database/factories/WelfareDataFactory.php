<?php

namespace Database\Factories;

use App\Models\WelfareData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WelfareData>
 */
class WelfareDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'region_code' => '32',
            'region_name' => 'Jawa Barat',
            'indicator' => 'kemiskinan',
            'value' => 7.62,
            'unit' => 'persen',
            'year' => 2023,
            'source' => 'BPS',
        ];
    }
}
