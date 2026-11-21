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
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a08',
                'question' => 'Bagaimana PARTAI mencegah kecurangan suara tamu/guest?',
                'answer' => 'Akun tamu memperoleh sesi melalui Laravel Sanctum, tetapi ditandai sebagai role guest dan tidak diverifikasi. Sistem voting hanya menerima akun role member yang sudah terverifikasi serta bukan akun berprefiks guest_, sehingga akun tamu tidak dapat mengirim suara. Pendaftaran anggota juga memeriksa keunikan NIK yang disimpan sebagai hash.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a09',
                'question' => 'Apakah saya bisa membeli pengaruh dengan donasi?',
                'answer' => 'Tidak. PARTAI menerapkan prinsip Zero Leverage: besaran donasi tidak menambah bobot suara, kelayakan kursi, atau pengaruh keputusan. Sumbangan tetap tercatat dan diaudit, tetapi hak politik ditentukan oleh identitas anggota yang terverifikasi dan aturan kapasitas.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a10',
                'question' => 'Bagaimana cara kerja algoritma SM-2 di halaman Belajar?',
                'answer' => 'Setelah menjawab materi, sistem menilai kualitas jawaban dan memperbarui jadwal ulasan. Jawaban yang lemah mengulang interval pendek; jawaban yang kuat memperpanjang interval berikutnya. Nilai pengulangan dan faktor kemudahan disimpan, sehingga Mastery Score dan jadwal ulasan berkembang secara dinamis.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a11',
                'question' => 'Mengapa satu klik tidak boleh menghasilkan dua suara atau tindakan?',
                'answer' => 'Sistem menggunakan idempotensi dan kunci permintaan untuk memastikan permintaan berulang diproses hanya sekali. Pada voting dan tindakan lain, kombinasi identitas, kunci permintaan, dan data permintaan diperiksa; pengulangan yang sama tidak menambah efek, sedangkan kunci yang dipakai dengan data berbeda ditolak atau ditandai konflik.',
            ],
            [
                'id' => '4e3e8b8b-60e1-4ad0-898f-6dd68c5e8a12',
                'question' => 'Apakah riwayat reputasi dapat diubah atau dihapus?',
                'answer' => 'Reputation Ledger mencatat riwayat yang terhubung oleh rantai hash. Perubahan baru harus membentuk catatan baru dan dapat diaudit, bukan menyembunyikan catatan lama. Mekanisme ini membantu menjaga integritas Mastery dan Participation serta mendeteksi manipulasi.',
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
