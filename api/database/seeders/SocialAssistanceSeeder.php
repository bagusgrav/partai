<?php

namespace Database\Seeders;

use App\Models\SocialAssistance;
use Illuminate\Database\Seeder;

class SocialAssistanceSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['0000000000000001', 'Data Simulasi 01', 'PKH', 'DITERIMA', '2026', '3201', null],
            ['0000000000000002', 'Data Simulasi 02', 'BPNT', 'PROSES', '2026', '3202', null],
            ['0000000000000003', 'Data Simulasi 03', 'PIP', 'TIDAK_DITERIMA', '2026', '3301', 'Data simulasi untuk pengujian.'],
            ['0000000000000004', 'Data Simulasi 04', 'PKH', 'DITERIMA', '2026', '3501', null],
            ['0000000000000005', 'Data Simulasi 05', 'BPNT', 'DITERIMA', '2026', '3101', null],
            ['0000000000000006', 'Data Simulasi 06', 'PIP', 'PROSES', '2026', '1201', null],
            ['0000000000000007', 'Data Simulasi 07', 'PKH', 'TIDAK_DITERIMA', '2026', '3203', 'Data simulasi untuk pengujian.'],
        ];

        foreach ($records as [$nik, $name, $program, $status, $period, $regionCode, $notes]) {
            SocialAssistance::query()->updateOrCreate(
                ['nik' => $nik],
                [
                    'name' => $name,
                    'program' => $program,
                    'status' => $status,
                    'period' => $period,
                    'region_code' => $regionCode,
                    'notes' => $notes,
                ],
            );
        }
    }
}
