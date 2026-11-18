<?php

namespace Database\Seeders;

use App\Models\BureaucracyReport;
use App\Models\PublicAgencyScorecard;
use App\Services\BureaucracyWatchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BureaucracyWatchSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['KEMENPANRB-01', 'Kementerian Pendayagunaan Aparatur Negara dan Reformasi Birokrasi', 'kementerian_lembaga', null, null, 88.40, 'AA', 365.00, 92.10, 'Publikasi RB, KemenPANRB, 2025'],
            ['KEMENDIKDAS-01', 'Kementerian Pendidikan Dasar dan Menengah', 'kementerian_lembaga', null, null, 82.75, 'AA', 348.50, 89.30, 'Laporan Indeks RB dan Ombudsman RI, 2025'],
            ['KEMENKES-01', 'Kementerian Kesehatan', 'kementerian_lembaga', null, null, 80.20, 'A', 342.00, 87.60, 'Rapor RB KemenPANRB dan Kepatuhan Pelayanan Ombudsman RI, 2025'],
            ['BKN-01', 'Badan Kepegawaian Negara', 'kementerian_lembaga', null, null, 86.10, 'AA', 376.00, 91.20, 'Indeks Sistem Merit ASN BKN dan Hasil Evaluasi RB, 2025'],
            ['KOMINFO-01', 'Kementerian Komunikasi dan Digital', 'kementerian_lembaga', null, null, 78.30, 'A', 330.20, 84.40, 'Publikasi RB KemenPANRB dan Ombudsman RI, 2025'],
            ['PEMPROV-31', 'Pemerintah Provinsi DKI Jakarta', 'pemerintah_provinsi', 'DKI Jakarta', '31', 74.90, 'A', 318.40, 81.10, 'Rapor Kinerja Pemprov DKI dan Ombudsman DKI, 2025'],
            ['PEMPROV-32', 'Pemerintah Provinsi Jawa Barat', 'pemerintah_provinsi', 'Jawa Barat', '32', 81.60, 'AA', 355.30, 88.20, 'Rapor Kinerja Pemprov Jawa Barat, KemenPANRB dan Ombudsman, 2025'],
            ['PEMPROV-35', 'Pemerintah Provinsi Jawa Timur', 'pemerintah_provinsi', 'Jawa Timur', '35', 79.80, 'A', 346.70, 86.90, 'Rapor Kinerja Pemprov Jawa Timur, KemenPANRB dan Ombudsman, 2025'],
            ['PEMKAB-3507', 'Pemerintah Kabupaten Malang', 'pemerintah_kab_kota', 'Jawa Timur', '3507', 68.40, 'B', 292.50, 78.20, 'Rapor Pelayanan Publik Kabupaten Malang dan Ombudsman Jatim, 2025'],
            ['PEMKOTA-3273', 'Pemerintah Kota Bandung', 'pemerintah_kab_kota', 'Jawa Barat', '3273', 72.35, 'A', 308.10, 80.60, 'Rapor Pelayanan Publik Kota Bandung dan Ombudsman Jabar, 2025'],
            ['BPJSKES-01', 'BPJS Kesehatan', 'pelayanan_publik', null, null, 76.40, 'A', 310.80, 82.90, 'Indeks RB dan Kepatuhan Pelayanan Publik Ombudsman, 2025'],
            ['SAMSAT-3507', 'Unit Pelayanan Samsat Kabupaten Malang', 'pelayanan_publik', 'Jawa Timur', '3507', 62.10, 'BB', 270.20, 69.40, 'Rapor Pelayanan Publik dan Ombudsman Jatim, 2025'],
        ];

        $service = app(BureaucracyWatchService::class);
        foreach ($rows as [$code, $name, $category, $province, $region, $rb, $grade, $merit, $ombudsman, $citation]) {
            $calculation = $service->calculateScore($rb, $merit, $ombudsman);
            PublicAgencyScorecard::query()->updateOrCreate(
                ['agency_code' => $code, 'period_year' => 2026],
                [
                    'agency_name' => $name,
                    'agency_category' => $category,
                    'province_name' => $province,
                    'region_code' => $region,
                    'rb_index_score' => $rb,
                    'sakip_grade' => $grade,
                    'merit_system_score' => $merit,
                    'ombudsman_compliance_score' => $ombudsman,
                    'source_citation' => $citation,
                    ...$calculation,
                ],
            );
        }

        $malang = PublicAgencyScorecard::query()->where('agency_code', 'PEMKAB-3507')->first();
        $bandung = PublicAgencyScorecard::query()->where('agency_code', 'PEMKOTA-3273')->first();
        $samsat = PublicAgencyScorecard::query()->where('agency_code', 'SAMSAT-3507')->first();

        $examples = [
            ['maladministrasi', 'Keterlambatan penerbitan izin layanan', 'Permohonan telah lengkap, tetapi proses verifikasi belum ditindaklanjuti dalam waktu yang telah dijanjikan pada kalender layanan.', '2026-08-12', 'Jawa Timur', '3507', $malang, 'resolved', 'Permohonan telah ditindaklanjuti dan instansi menerapkan peninjauan berkala antrean.'],
            ['penyalahgunaan_fasilitas', 'Penggunaan kendaraan dinas di luar jadwal layanan', 'Kendaraan dinas terlihat digunakan untuk kepentingan pribadi di luar lokasi tugas tanpa penjelasan perjalanan dinas resmi.', '2026-09-03', 'Jawa Barat', '3273', $bandung, 'forwarded_to_ombudsman', 'Laporan diteruskan kepada Ombudsman untuk verifikasi administrasi aset dan perjalanan dinas.'],
            ['pungli_layanan', 'Permintaan biaya diluar standar layanan', 'Petugas meminta pembayaran tambahan di luar biaya resmi yang tercantum pada papan informasi dan kanal daring layanan.', '2026-09-18', 'Jawa Timur', '3507', $samsat, 'verified', 'Bukti telah diverifikasi dan instansi diminta menonaktifkan praktik pemungutan diluar ketentuan.'],
            ['penundaan_berlarut', 'Permintaan peninjauan berulang tanpa kepastian', 'Permohonan warga berulang kali diminta memperbaiki dokumen yang sama tanpa penjelasan tahap penyelesaian yang jelas.', '2026-10-01', 'Jawa Barat', '3273', $bandung, 'resolved', 'Keluhan diselesaikan melalui penerbitan keputusan tertulis dan perbaikan alur layanan.'],
            ['diskriminasi_layanan', 'Perlakuan berbeda pada loket layanan', 'Kelompok warga tidak mendapatkan urutan layanan yang setara meskipun berada dalam antrean dengan nomor prioritas yang sah.', '2026-10-20', 'Jawa Timur', '3507', $malang, 'forwarded_to_ombudsman', 'Laporan diteruskan untuk penilaian kepatuhan standar pelayanan publik.'],
            ['penyalahgunaan_fasilitas', 'Nomor kendaraan dinas digunakan tanpa surat tugas', 'Plat merah kendaraan dinas digunakan tanpa surat tugas pada waktu yang tidak terkait layanan operasional.', '2026-11-02', 'Jawa Timur', '3507', $malang, 'verified', 'Nomor aset dan kronologi telah dicatat untuk audit penggunaan fasilitas negara.'],
        ];

        foreach ($examples as $index => [$category, $title, $chronology, $incidentDate, $province, $region, $agency, $status, $note]) {
            $secret = 'seed-secret-'.($index + 1);
            BureaucracyReport::query()->updateOrCreate(
                ['ticket_code' => 'BW-2026-SEED'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'reporter_token_hash' => hash('sha256', $secret),
                    'request_id' => (string) Str::uuid(),
                    'payload_hash' => hash('sha256', 'seed-'.$index),
                    'agency_id' => $agency?->id,
                    'agency_name' => $agency?->agency_name ?? 'Instansi Contoh',
                    'region_code' => $region,
                    'province_name' => $province,
                    'category' => $category,
                    'title' => $title,
                    'chronology' => $chronology,
                    'incident_date' => $incidentDate,
                    'asset_identifier' => $category === 'penyalahgunaan_fasilitas' ? 'PLAT MERAH-'.($index + 1) : null,
                    'verification_status' => $status,
                    'public_resolution_note' => $note,
                    'verified_at' => now(),
                ],
            );
        }

        $malang?->update(['open_complaints_count' => 1, 'resolved_complaints_count' => 2]);
        $bandung?->update(['open_complaints_count' => 2, 'resolved_complaints_count' => 1]);
        $samsat?->update(['open_complaints_count' => 1, 'resolved_complaints_count' => 0]);
    }
}
