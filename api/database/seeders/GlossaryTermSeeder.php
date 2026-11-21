<?php

namespace Database\Seeders;

use App\Models\GlossaryTerm;
use Illuminate\Database\Seeder;

class GlossaryTermSeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            [
                'term' => 'IKK',
                'definition' => 'Indeks Kerentanan Kesejahteraan, yaitu skor gabungan untuk membantu membandingkan tingkat kerentanan wilayah. IKK bukan pengganti indikator resmi dari masing-masing sumber.',
                'category' => 'Data publik',
            ],
            [
                'term' => 'TPT',
                'definition' => 'Tingkat Pengangguran Terbuka: persentase angkatan kerja yang tidak bekerja dan sedang mencari pekerjaan atau mempersiapkan usaha.',
                'category' => 'Data publik',
            ],
            [
                'term' => 'Inflasi',
                'definition' => 'Kenaikan harga barang dan jasa secara umum dalam suatu periode. Angka inflasi merangkum perubahan harga, bukan kenaikan harga satu barang saja.',
                'category' => 'Ekonomi',
            ],
            [
                'term' => 'Stunting',
                'definition' => 'Gangguan pertumbuhan pada anak akibat kekurangan gizi kronis, infeksi berulang, dan faktor lingkungan; biasanya diukur dari tinggi badan menurut umur.',
                'category' => 'Kesejahteraan',
            ],
            [
                'term' => 'Gini Ratio',
                'definition' => 'Ukuran ketimpangan distribusi pengeluaran atau pendapatan. Nilai yang lebih tinggi menunjukkan ketimpangan yang lebih besar.',
                'category' => 'Ekonomi',
            ],
            [
                'term' => 'Mastery Score',
                'definition' => 'Skor kapasitas intelektual yang menggambarkan penguasaan anggota terhadap materi kewargaan. Perkembangan Mastery Score dipelajari menggunakan algoritma SM-2 (SuperMemo 2) dan terpisah dari kontribusi serta hak suara.',
                'category' => 'Partisipasi',
            ],
            [
                'term' => 'Participation Score',
                'definition' => 'Skor kontribusi bagi aksi lapangan dan kontribusi non-fisik yang telah diverifikasi, seperti riset, bantuan hukum, logistik, atau kerja editorial. Skor ini terpisah dari penguasaan materi dan tidak menambah bobot suara.',
                'category' => 'Partisipasi',
            ],
            [
                'term' => 'SM-2 (SuperMemo 2)',
                'definition' => 'Algoritma pengulangan berjeda (spaced repetition) yang menentukan jadwal ulasan berdasarkan kualitas ingatan. Materi yang sulit diingat dijadwalkan lebih cepat, sementara materi yang dikuasai dapat dijadwalkan lebih lama agar masuk ke memori jangka panjang.',
                'category' => 'Pembelajaran',
            ],
            [
                'term' => 'Zero Leverage',
                'definition' => 'Prinsip anti-oligarki bahwa besaran donasi atau kekayaan tidak menambah bobot suara, akses keputusan, atau kelayakan jabatan. Setiap anggota yang memenuhi syarat memiliki kedudukan suara yang setara.',
                'category' => 'Tata kelola',
            ],
            [
                'term' => 'Reputation Ledger',
                'definition' => 'Pencatatan riwayat reputasi yang terhubung dengan rantai hash (hash-chained), sehingga perubahan atau penghapusan catatan dapat terdeteksi. Ledger ini mendukung audit riwayat Mastery dan Participation secara terbuka.',
                'category' => 'Tata kelola',
            ],
            [
                'term' => 'Sortisi (Sortition)',
                'definition' => 'Pemilihan panel audit atau juri secara acak dari anggota yang memenuhi syarat, sebagaimana sistem juri. Pemilihan acak mengurangi dominasi popularitas dan menjaga representasi yang lebih adil.',
                'category' => 'Tata kelola',
            ],
            [
                'term' => 'Idempotensi',
                'definition' => 'Sifat sistem yang mencegah input ganda menghasilkan efek berulang. Contohnya, satu klik voter dengan satu permintaan yang sama hanya menghasilkan satu suara atau satu tindakan.',
                'category' => 'Teknologi',
            ],
            [
                'term' => 'Kontrak Faksi',
                'definition' => 'Komitmen terbuka dan terukur yang disepakati wakil atau kelompok faksi untuk menjalankan janji serta melaporkan perkembangannya kepada publik.',
                'category' => 'Tata kelola',
            ],
        ];

        foreach ($terms as $term) {
            GlossaryTerm::query()->updateOrCreate(
                ['term' => $term['term']],
                $term,
            );
        }
    }
}
