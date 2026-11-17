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
                'definition' => 'Nilai penguasaan materi kewargaan anggota pada skala 0–100. Skor ini menggambarkan kapasitas belajar dan terpisah dari poin kontribusi.',
                'category' => 'Partisipasi',
            ],
            [
                'term' => 'Participation Score',
                'definition' => 'Poin dari kontribusi anggota yang telah diverifikasi, seperti kerja lapangan, riset, bantuan hukum, logistik, atau kerja editorial. Skor ini terpisah dari penguasaan materi dan tidak memengaruhi hak suara.',
                'category' => 'Partisipasi',
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
