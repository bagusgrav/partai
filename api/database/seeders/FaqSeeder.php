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
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(
                ['id' => $faq['id']],
                [...$faq, 'is_published' => true],
            );
        }
    }
}
