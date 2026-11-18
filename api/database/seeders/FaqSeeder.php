<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a01',
                'question' => 'Apakah AkuHadir.my.id sudah menjadi partai politik resmi?',
                'answer' => 'Informasi mengenai status dan legalitas resmi akan disampaikan melalui pengumuman serta dokumen resmi PARTAI. Silakan periksa kanal resmi untuk informasi terbaru.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a02',
                'question' => 'Bagaimana cara saya bergabung menjadi anggota?',
                'answer' => 'Pilih menu Gabung Anggota atau kunjungi halaman pendaftaran, lalu ikuti petunjuk untuk mengisi data yang diperlukan.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a03',
                'question' => 'Apakah data pribadi saya aman?',
                'answer' => 'Data yang Anda berikan digunakan untuk keperluan layanan terkait. Jangan kirimkan informasi sensitif yang tidak diminta; silakan hubungi pengurus untuk pertanyaan tentang pengelolaan data.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a04',
                'question' => 'Bagaimana transparansi keuangan partai dan batas sumbangan bekerja?',
                'answer' => 'Setiap transaksi dicatat dalam ledger yang terhubung secara hash (append-only) dan dapat diaudit. UU Partai Politik membatasi sumbangan individu hingga Rp1 miliar per tahun serta badan usaha hingga Rp7,5 miliar per tahun. Sumbangan anonim dilarang dan identitas penyumbang diperlakukan sebagai bukti kepatuhan. Nominal iuran atau donasi tidak menambah hak suara dan tidak menambah kelayakan kursi.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a05',
                'question' => 'Siapa menjawab apa dalam struktur organisasi?',
                'answer' => 'Pemisahan kekuasaan dibagi menjadi empat lapis: Administrasi Legal, Panel Audit & Etik, Editorial Kurikulum, dan Perwalian Teknis; ditambah koordinasi Cluster Kecamatan. Ketua dan bendahara memegang mandat administrasi terbatas, panel independen mengaudit keuangan serta etik, editorial menjaga kurikulum, dan perwalian teknis mengelola infrastruktur dengan SLA. Anggota berdaulat tetap memiliki hak koreksi dan recall.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a06',
                'question' => 'Bagaimana siapa pun yang memenuhi ambang dapat mengisi kursi terbuka?',
                'answer' => 'Kursi terbuka dapat dilihat di /tata-kelola. Kelayakan dihitung otomatis dari overall_mastery_level, skor domain yang diwajibkan, dan participation_score yang terverifikasi. Pemenuhan ambang membuka hak mengajukan diri ke kolam sortisi; uang dan popularitas tidak menjadi bobot keputusan.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a07',
                'question' => 'Bagaimana Pengawasan Birokrasi, IKK, dan reputasi terpisah saling terhubung?',
                'answer' => 'Pengawasan Birokrasi menerima laporan, IKK menunjukkan kerentanan kesejahteraan wilayah, dan Sistem Reputasi Terpisah mencatat Mastery serta Participation secara terverifikasi dalam ledger yang berbeda. Ketiganya saling melengkapi bukti publik, tetapi tidak boleh ditukar atau dimanipulasi menggunakan nominal keuangan.',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(
                ['id' => $faq['id']],
                [...$faq, 'is_published' => true],
            );
        }
    }
}
