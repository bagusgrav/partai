<?php

namespace Database\Seeders;

use App\Models\FactionContract;
use Illuminate\Database\Seeder;

class FactionContractSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = [
            [
                'id' => 'f28d2f9d-44f1-4c64-94da-10e8255d6501',
                'politician_name' => 'Ayu Pratiwi',
                'title' => 'Membuka laporan anggaran secara berkala',
                'description' => 'Menerbitkan ringkasan penggunaan anggaran publik setiap tiga bulan.',
                'status' => 'SEDANG_BERJALAN',
                'evidence_url' => null,
                'deadline' => '2027-06-30',
            ],
            [
                'id' => 'f28d2f9d-44f1-4c64-94da-10e8255d6502',
                'politician_name' => 'Bima Santoso',
                'title' => 'Memperjuangkan layanan kesehatan keliling',
                'description' => 'Mendorong layanan kesehatan keliling untuk wilayah yang jauh dari puskesmas.',
                'status' => 'TERPENUHI',
                'evidence_url' => 'https://example.org/bukti/layanan-kesehatan-keliling',
                'deadline' => '2026-10-01',
            ],
            [
                'id' => 'f28d2f9d-44f1-4c64-94da-10e8255d6503',
                'politician_name' => 'Citra Wulandari',
                'title' => 'Menyediakan forum dengar warga bulanan',
                'description' => 'Menyelenggarakan forum terbuka bulanan untuk menerima aspirasi warga.',
                'status' => 'BELUM_DIMULAI',
                'evidence_url' => null,
                'deadline' => '2027-03-31',
            ],
            [
                'id' => 'f28d2f9d-44f1-4c64-94da-10e8255d6504',
                'politician_name' => 'Danu Wijaya',
                'title' => 'Memperbaiki akses air bersih di desa',
                'description' => 'Mengawal perbaikan akses air bersih bagi desa yang belum terlayani.',
                'status' => 'GAGAL',
                'evidence_url' => 'https://example.org/bukti/evaluasi-air-bersih',
                'deadline' => '2026-06-30',
            ],
        ];

        foreach ($contracts as $contract) {
            $record = FactionContract::query()->find($contract['id']) ?? new FactionContract;
            $record->forceFill($contract)->save();
        }
    }
}
