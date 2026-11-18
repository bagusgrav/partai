<?php

namespace Database\Seeders;

use App\Models\OrganizationSeat;
use Illuminate\Database\Seeder;

class OrganizationSeatSeeder extends Seeder
{
    public function run(): void
    {
        $national = OrganizationSeat::query()->updateOrCreate(
            ['seat_code' => 'MEMBER-SOVEREIGN'],
            [
                'parent_id' => null,
                'layer' => 'anggota_berdaulat',
                'title' => 'Majelis Anggota Berdaulat',
                'territory_level' => 'nasional',
                'answering_scope' => 'Menjawab keputusan tertinggi melalui satu anggota satu suara; dapat mengawasi mandat, mengajukan recall, dan koreksi. Tidak ada kewenangan jual-beli suara berdasarkan iuran.',
                'selection_method' => 'pemilihan_anggota',
                'required_mastery_level' => 1,
                'required_min_participation' => 0,
                'status' => 'filled',
                'sort_order' => 10,
            ],
        );

        $seats = [
            ['LEGAL-CHAIR', null, 'anggota_berdaulat', 'Ketua Umum (Mandat Legal)', 'nasional', 'kepengurusan dan mandat legal', 'mandat_legal_terbatas', 3, null, 0, 'filled'],
            ['LEGAL-TREASURER', 'Ketua Umum (Mandat Legal)', 'pengurus_legal', 'Bendahara Umum', 'nasional', 'keuangan, transparansi, dan laporan keuangan', 'mandat_legal_terbatas', 3, 'keuangan_negara', 10, 'filled'],
            ['LEGAL-SECRETARY', 'Ketua Umum (Mandat Legal)', 'pengurus_legal', 'Sekretaris Jenderal', 'nasional', 'administrasi legal, tata tertib, dan tata usaha', 'mandat_legal_terbatas', 3, 'hukum', 10, 'filled'],
            ['PANEL-AUDIT-01', null, 'peran_fungsional_sortisi', 'Anggota Panel Audit Keuangan & Etik', 'nasional', 'audit ledger, kepatuhan UU Parpol, dan temuan konflik kepentingan', 'sortisi_acak', 4, 'keuangan_negara', 20, 'open'],
            ['PANEL-ETHICS-01', null, 'peran_fungsional_sortisi', 'Anggota Panel Etik', 'nasional', 'penilaian etik, recusal, dan rekomendasi koreksi mandat', 'sortisi_acak', 4, 'hukum', 20, 'sortition_pool'],
            ['PANEL-EDITORIAL-01', null, 'peran_fungsional_sortisi', 'Editor Kurikulum & Publikasi', 'nasional', 'editorial kurikulum, sumber, koreksi, dan keterbacaan publik', 'sortisi_acak', 3, 'politik_bernegara', 10, 'open'],
            ['TRUSTEE-TECH-01', null, 'perwalian_teknis', 'Perwalian Teknis Infrastruktur', 'nasional', 'operasi kunci multisig, keamanan sistem, dan pemulihan insiden', 'perwalian_multisig', 4, null, 20, 'open'],
            ['TRUSTEE-TECH-02', 'Perwalian Teknis Infrastruktur', 'perwalian_teknis', 'Perwalian Cadangan Teknik', 'nasional', 'cadangan rotasi multisig dan verifikasi perubahan infrastruktur', 'perwalian_multisig', 4, null, 20, 'open'],
            ['REG-PROV-01', null, 'cluster_teritorial', 'Koordinator Provinsi', 'provinsi', 'koordinasi laporan publik, tidak mengubah mandat nasional', 'pemilihan_anggota', 2, null, 5, 'filled'],
            ['REG-KAB-01', 'Koordinator Provinsi', 'cluster_teritorial', 'Koordinator Kabupaten/Kota', 'kab_kota', 'koordinasi wilayah, mengumpulkan bukti, dan menjawab SLA warga', 'pemilihan_anggota', 2, null, 5, 'filled'],
            ['CLUSTER-COORD-3171', 'Koordinator Kabupaten/Kota', 'cluster_teritorial', 'Koordinator Cluster Kecamatan', 'kecamatan_cluster', 'jemput laporan warga, verifikasi lapangan, dan eskalasi pelanggaran', 'pemilihan_anggota', 2, null, 5, 'open'],
            ['CLUSTER-LEGAL-3171', 'Koordinator Cluster Kecamatan', 'cluster_teritorial', 'Pendamping Hak Warga Cluster', 'kecamatan_cluster', 'membantu jemput hak, bukan memberi keputusan hukum final', 'pemilihan_anggota', 2, 'hukum', 5, 'open'],
            ['CLUSTER-BUREAU-3171', 'Koordinator Cluster Kecamatan', 'cluster_teritorial', 'Pengawas Birokrasi Cluster', 'kecamatan_cluster', 'memantau IKK dan laporan birokrasi dengan bukti terverifikasi', 'pemilihan_anggota', 2, null, 5, 'sortition_pool'],
            ['CLUSTER-EDITORIAL-3171', 'Koordinator Cluster Kecamatan', 'cluster_teritorial', 'Editor Lokal & Kurator Data', 'kecamatan_cluster', 'mengoreksi ringkasan data lokal dan menjaga sumber tetap tercantum', 'sortisi_acak', 2, 'politik_bernegara', 5, 'open'],
        ];

        foreach ($seats as $index => $row) {
            [$code, $parentTitle, $layer, $title, $territory, $scope, $method, $level, $domain, $participation, $status] = $row;
            $parentId = $parentTitle
                ? OrganizationSeat::query()->where('title', $parentTitle)->value('id')
                : ($layer === 'pengurus_legal' ? OrganizationSeat::query()->where('seat_code', 'LEGAL-CHAIR')->value('id') : null);

            OrganizationSeat::query()->updateOrCreate(
                ['seat_code' => $code],
                [
                    'parent_id' => $parentId,
                    'layer' => $layer,
                    'title' => $title,
                    'territory_level' => $territory,
                    'region_code' => str_starts_with($code, 'CLUSTER-') ? '3171' : null,
                    'province_name' => $territory === 'provinsi' ? 'Contoh Provinsi' : null,
                    'answering_scope' => "Menjawab {$scope}. Batas kewenangan: mandat terbatas, tidak boleh memperdagangkan suara atau melampaui hasil sortisi/koreksi anggota. SLA respons 3x24 jam.",
                    'selection_method' => $method,
                    'required_mastery_level' => $level,
                    'required_domain' => $domain,
                    'required_min_participation' => $participation,
                    'term_duration_months' => 24,
                    'max_consecutive_terms' => 2,
                    'holder_display_name' => $status === 'filled' ? 'Mandat Aktif Terverifikasi' : null,
                    'term_starts_at' => $status === 'filled' ? '2026-01-01' : null,
                    'term_ends_at' => $status === 'filled' ? '2027-12-31' : null,
                    'status' => $status,
                    'sort_order' => 20 + $index,
                ],
            );
        }
    }
}
