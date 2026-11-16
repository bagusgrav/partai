<?php

namespace Database\Seeders;

use App\Models\WelfareData;
use Illuminate\Database\Seeder;

class WelfareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = (int) now()->year;
        $records = [
            ['32', 'Jawa Barat', 'kemiskinan', 7.62, 'persen', 2023, 'BPS'],
            ['35', 'Jawa Timur', 'kemiskinan', 10.50, 'persen', 2023, 'BPS'],
            ['33', 'Jawa Tengah', 'kemiskinan', 10.77, 'persen', 2023, 'BPS'],
            ['31', 'DKI Jakarta', 'kemiskinan', 4.44, 'persen', 2023, 'BPS'],
            ['12', 'Sumatera Utara', 'kemiskinan', 8.15, 'persen', 2023, 'BPS'],
            ['32', 'Jawa Barat', 'stunting', 21.70, 'persen', 2023, 'BPS'],
            ['35', 'Jawa Timur', 'stunting', 17.70, 'persen', 2023, 'BPS'],
            ['33', 'Jawa Tengah', 'stunting', 20.70, 'persen', 2023, 'BPS'],
            ['31', 'DKI Jakarta', 'stunting', 17.60, 'persen', 2023, 'BPS'],
            ['12', 'Sumatera Utara', 'stunting', 18.90, 'persen', 2023, 'BPS'],
            ['32', 'Jawa Barat', 'bencana', 150, 'kejadian', $currentYear, 'BNPB'],
            ['35', 'Jawa Timur', 'bencana', 92, 'kejadian', $currentYear, 'BNPB'],
            ['33', 'Jawa Tengah', 'bencana', 85, 'kejadian', $currentYear, 'BNPB'],
            ['31', 'DKI Jakarta', 'bencana', 41, 'kejadian', $currentYear, 'BNPB'],
            ['12', 'Sumatera Utara', 'bencana', 110, 'kejadian', $currentYear, 'BNPB'],
        ];

        foreach ($records as [$regionCode, $regionName, $indicator, $value, $unit, $year, $source]) {
            WelfareData::query()->updateOrCreate(
                [
                    'region_code' => $regionCode,
                    'indicator' => $indicator,
                    'year' => $year,
                    'source' => $source,
                ],
                [
                    'region_name' => $regionName,
                    'value' => $value,
                    'unit' => $unit,
                ],
            );
        }
    }
}
