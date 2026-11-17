<?php

namespace Database\Seeders;

use App\Services\WelfareIndexService;
use Illuminate\Database\Seeder;

class RegionWelfareIndexSeeder extends Seeder
{
    /**
     * Representative offline estimates keep CI usable without API credentials.
     *
     * @return list<array<string, int|float|string|null>>
     */
    public static function records(): array
    {
        $regions = [
            ['1171', 'Kota Banda Aceh', 'Aceh', 9.5, 0.32, 6.0, 26.0, 34, 8400, 70],
            ['1271', 'Kota Medan', 'Sumatera Utara', 8.3, 0.34, 8.0, 18.0, 52, 12600, 45],
            ['1371', 'Kota Padang', 'Sumatera Barat', 6.7, 0.30, 6.0, 24.5, 29, 5900, 35],
            ['1671', 'Kota Palembang', 'Sumatera Selatan', 10.0, 0.34, 6.5, 20.0, 61, 14300, 55],
            ['3171', 'Kota Administrasi Jakarta Selatan', 'DKI Jakarta', 4.0, 0.42, 6.5, 12.0, 18, 3200, 30],
            ['3273', 'Kota Bandung', 'Jawa Barat', 7.2, 0.39, 8.0, 21.0, 47, 9700, 45],
            ['3374', 'Kota Semarang', 'Jawa Tengah', 4.5, 0.36, 5.5, 17.0, 31, 6100, 25],
            ['3578', 'Kota Surabaya', 'Jawa Timur', 4.7, 0.41, 6.0, 15.0, 42, 7900, 35],
            ['6471', 'Kota Balikpapan', 'Kalimantan Timur', 2.4, 0.31, 5.0, 17.0, 20, 3800, 20],
            ['6371', 'Kota Banjarmasin', 'Kalimantan Selatan', 4.5, 0.34, 5.2, 24.0, 58, 11200, 60],
            ['7371', 'Kota Makassar', 'Sulawesi Selatan', 4.2, 0.40, 10.0, 21.5, 63, 12800, 55],
            ['7271', 'Kota Palu', 'Sulawesi Tengah', 6.5, 0.34, 5.0, 24.0, 76, 15800, 65],
            ['5171', 'Kota Denpasar', 'Bali', 2.3, 0.36, 4.0, 8.5, 24, 4500, 30],
            ['5271', 'Kota Mataram', 'Nusa Tenggara Barat', 7.5, 0.35, 5.5, 24.0, 78, 16200, 70],
            ['5371', 'Kota Kupang', 'Nusa Tenggara Timur', 13.0, 0.36, 3.8, 27.0, 81, 18100, 75],
            ['8171', 'Kota Ambon', 'Maluku', 16.0, 0.32, 6.0, 28.0, 73, 15400, 75],
            ['9471', 'Kota Jayapura', 'Papua', 18.0, 0.40, 5.0, 29.0, 91, 20900, 85],
        ];

        return array_map(
            static fn (array $region): array => [
                'region_code' => $region[0],
                'region_name' => $region[1],
                'province_name' => $region[2],
                'period_year' => 2024,
                'poverty_rate' => $region[3],
                'gini_ratio' => $region[4],
                'open_unemployment_rate' => $region[5],
                'stunting_rate' => $region[6],
                'disaster_events_count' => $region[7],
                'affected_population' => $region[8],
                'disaster_risk_score' => $region[9],
                'bps_source_citation' => sprintf(
                    'Rujukan: BPS %s Dalam Angka 2025; Susenas Maret 2024; Kemenkes SKI 2023. Baseline kurasi; verifikasi tabel sumber.',
                    $region[1],
                ),
                'bnpb_source_citation' => 'Rujukan: BNPB, Data Informasi Bencana Indonesia (DIBI) 2024. Baseline kurasi; verifikasi rekap wilayah.',
                'last_synced_at' => null,
            ],
            $regions,
        );
    }

    public function run(WelfareIndexService $service): void
    {
        $service->upsert(self::records());
    }
}
