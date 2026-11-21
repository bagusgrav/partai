<?php

namespace Database\Seeders;

use App\Models\PartySetting;
use Illuminate\Database\Seeder;

class PartySettingSeeder extends Seeder
{
    public function run(): void
    {
        PartySetting::query()->firstOrCreate([], [
            'party_name' => 'PARTAI',
            'logo_path' => null,
            'primary_color' => '#0f172a',
            'secondary_color' => '#ffffff',
            'tagline' => 'Bersama Warga, Menghadirkan Perubahan',
        ]);
    }
}
