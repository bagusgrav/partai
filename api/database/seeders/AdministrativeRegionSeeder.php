<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdministrativeRegionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/indonesia_regions.json');
        $regions = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $provinceCount = count(array_filter(
            $regions,
            static fn (array $region): bool => $region['level'] === 1,
        ));
        $regencyCount = count(array_filter(
            $regions,
            static fn (array $region): bool => $region['level'] === 2,
        ));

        if ($provinceCount !== 38 || $regencyCount !== 514) {
            throw new RuntimeException('Dataset wilayah harus berisi 38 provinsi dan 514 kabupaten/kota.');
        }

        $now = now();
        $records = array_map(
            static fn (array $region): array => [
                ...$region,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $regions,
        );

        foreach (array_chunk($records, 500) as $batch) {
            DB::table('regions')->upsert(
                $batch,
                ['id'],
                ['parent_id', 'name', 'level', 'updated_at'],
            );
        }
    }
}
