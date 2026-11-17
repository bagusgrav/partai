<?php

namespace Database\Seeders;

use App\Models\CivicQuestion;
use Illuminate\Database\Seeder;

class CivicQuestionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->questions() as $question) {
            CivicQuestion::query()->updateOrCreate(
                ['prompt' => $question['prompt']],
                [...$question, 'review_status' => 'published'],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questions(): array
    {
        return [
            [
                'domain' => 'hak_konstitusional',
                'mastery_level' => 1,
                'prompt' => 'Sebuah warga meminta kepastian prosedur layanan publik yang diterapkan berbeda terhadap orang dengan kondisi setara. Prinsip konstitusional mana yang paling langsung relevan?',
                'options' => $this->options('Kesetaraan perlakuan di hadapan hukum dan kepastian hukum yang adil.', 'Hak untuk mengusulkan perubahan undang-undang secara langsung.', 'Hak memperoleh pekerjaan tertentu dari pemerintah.', 'Kewenangan lembaga untuk mengabaikan aturan tertulis.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 28D ayat (1) menjamin pengakuan, jaminan, perlindungan, dan kepastian hukum yang adil serta perlakuan yang sama di hadapan hukum. Pilihan B, C, dan D bukan isi jaminan tersebut dan tidak membenarkan layanan yang diskriminatif.',
                'source_citation' => 'Pasal 28D ayat (1) UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'hak_konstitusional',
                'mastery_level' => 2,
                'prompt' => 'Warga meminta salinan informasi penggunaan anggaran suatu badan publik. Sikap badan publik yang paling sesuai dengan hak atas informasi adalah...',
                'options' => $this->options('Menolak semua permintaan karena dokumen pemerintah selalu rahasia.', 'Menyediakan informasi yang terbuka, dengan pengecualian hanya untuk informasi yang sah dinyatakan dikecualikan.', 'Meminta pemohon membuktikan dukungan politiknya terlebih dahulu.', 'Menerbitkan informasi hanya kepada pemilih pada pemilu terakhir.'),
                'correct_option' => 'B',
                'explanation' => 'Pasal 28F UUD NRI 1945 melindungi hak berkomunikasi dan memperoleh informasi, sedangkan Pasal 2 UU KIP menetapkan asas keterbukaan dengan pengecualian yang ketat dan terbatas. Pilihan A membalik asas, sementara C dan D menambahkan syarat yang tidak berdasar.',
                'source_citation' => 'Pasal 28F UUD NRI 1945; Pasal 2 UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'hak_konstitusional',
                'mastery_level' => 4,
                'prompt' => 'Dalam pencatatan administrasi kependudukan, seorang penghayat kepercayaan mengalami hambatan karena identitasnya tidak dapat dicatat sebagaimana diakui putusan pengadilan. Apa kesimpulan konstitusional yang tepat?',
                'options' => $this->options('Pencatatan hanya boleh diberikan setelah kelompok tersebut menjadi agama mayoritas.', 'Negara harus memastikan perlindungan dan pelayanan administrasi yang setara bagi penghayat kepercayaan.', 'Putusan pengadilan hanya berlaku untuk pemilu, bukan administrasi kependudukan.', 'Kesetaraan administrasi dapat ditangguhkan tanpa batas untuk alasan kemudahan.'),
                'correct_option' => 'B',
                'explanation' => 'Putusan MK No. 97/PUU-XIV/2016 menegaskan hak penghayat kepercayaan untuk memperoleh pencatatan administrasi kependudukan yang setara. Pilihan A dan D menambahkan diskriminasi yang tidak dibenarkan, sedangkan C keliru membatasi ruang lingkup putusan.',
                'source_citation' => 'Putusan Mahkamah Konstitusi No. 97/PUU-XIV/2016',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'alasan_bernegara',
                'mastery_level' => 1,
                'prompt' => 'Manakah yang termasuk tujuan pembentukan Pemerintah Negara Indonesia sebagaimana tercantum dalam Pembukaan UUD NRI 1945 alinea keempat?',
                'options' => $this->options('Melindungi segenap bangsa dan seluruh tumpah darah Indonesia.', 'Menjamin satu kelompok memperoleh keistimewaan permanen.', 'Menghapus seluruh perbedaan pandangan warga.', 'Memusatkan seluruh keputusan publik pada satu lembaga.'),
                'correct_option' => 'A',
                'explanation' => 'Alinea keempat Pembukaan UUD NRI 1945 memuat tujuan melindungi segenap bangsa dan seluruh tumpah darah Indonesia, memajukan kesejahteraan umum, mencerdaskan kehidupan bangsa, serta ikut melaksanakan ketertiban dunia. B, C, dan D tidak tercantum sebagai tujuan negara dan bertentangan dengan kesetaraan serta tata negara.',
                'source_citation' => 'Pembukaan UUD NRI 1945 alinea keempat',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'alasan_bernegara',
                'mastery_level' => 2,
                'prompt' => 'Sebuah kebijakan publik diputuskan setelah konsultasi warga. Pernyataan konstitusional mana yang menjelaskan dasar kedaulatan dalam proses tersebut?',
                'options' => $this->options('Kedaulatan berada di tangan rakyat dan dilaksanakan menurut Undang-Undang Dasar.', 'Kedaulatan berada pada pejabat yang sedang menjabat tanpa batas.', 'Kedaulatan hanya dapat dijalankan melalui referendum untuk setiap keputusan.', 'Kedaulatan berada di luar sistem konstitusi.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 1 ayat (2) UUD NRI 1945 menempatkan kedaulatan di tangan rakyat dan pelaksanaannya tunduk pada UUD. Pilihan B dan D meniadakan batas konstitusional; C keliru karena konstitusi tidak mewajibkan referendum untuk semua keputusan.',
                'source_citation' => 'Pasal 1 ayat (2) UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'alasan_bernegara',
                'mastery_level' => 3,
                'prompt' => 'Saat menilai kebijakan ekonomi, warga membandingkan dampaknya pada keadilan, keberlanjutan, dan efisiensi bersama. Nilai konstitusional mana yang paling mendukung pendekatan itu?',
                'options' => $this->options('Demokrasi ekonomi dengan prinsip kebersamaan, efisiensi berkeadilan, keberlanjutan, dan kemandirian.', 'Kebebasan pelaku ekonomi tanpa aturan atau tanggung jawab sosial.', 'Pengelolaan ekonomi hanya untuk kepentingan pemegang modal terbesar.', 'Larangan negara mengatur perekonomian dalam keadaan apa pun.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 33 ayat (4) UUD NRI 1945 menyebut demokrasi ekonomi beserta prinsip kebersamaan, efisiensi berkeadilan, keberlanjutan, berwawasan lingkungan, kemandirian, serta keseimbangan. B, C, dan D meniadakan prinsip-prinsip tersebut.',
                'source_citation' => 'Pasal 33 ayat (4) UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'keuangan_negara',
                'mastery_level' => 1,
                'prompt' => 'Dokumen tahunan yang menjadi dasar rencana keuangan pemerintahan pusat dan disetujui bersama oleh DPR serta Presiden adalah...',
                'options' => $this->options('Anggaran Pendapatan dan Belanja Negara (APBN).', 'Laporan tahunan partai politik.', 'Rencana kerja satu kementerian saja.', 'Catatan rapat internal tanpa persetujuan DPR.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 23 ayat (1) UUD NRI 1945 menetapkan APBN sebagai wujud pengelolaan keuangan negara tahunan yang ditetapkan dengan undang-undang dan dilaksanakan secara terbuka serta bertanggung jawab untuk sebesar-besarnya kemakmuran rakyat. Pilihan B, C, dan D bukan pengganti APBN.',
                'source_citation' => 'Pasal 23 ayat (1) UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'keuangan_negara',
                'mastery_level' => 2,
                'prompt' => 'Sebuah program publik menggunakan anggaran besar tetapi hasilnya tidak terukur. Prinsip pengelolaan keuangan negara mana yang dapat dipakai untuk mengevaluasinya?',
                'options' => $this->options('Pengelolaan yang tertib, taat peraturan, efisien, ekonomis, efektif, transparan, dan bertanggung jawab.', 'Pengeluaran yang tinggi selalu dianggap berhasil.', 'Kerahasiaan seluruh anggaran tanpa pengecualian.', 'Penilaian hanya berdasarkan popularitas program.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 3 ayat (1) UU No. 17 Tahun 2003 memuat prinsip tertib, taat peraturan, efisien, ekonomis, efektif, transparan, dan bertanggung jawab dengan memperhatikan rasa keadilan dan kepatutan. B dan D bukan ukuran akuntabilitas; C meniadakan asas transparansi.',
                'source_citation' => 'Pasal 3 ayat (1) UU No. 17 Tahun 2003 tentang Keuangan Negara',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'keuangan_negara',
                'mastery_level' => 4,
                'prompt' => 'Dalam rapat pengawasan, warga meminta keterbukaan rencana anggaran sekaligus meminta audit independen atas pelaksanaannya. Pembagian peran yang tepat adalah...',
                'options' => $this->options('Pemerintah mengelola APBN menurut undang-undang, sedangkan DPR menjalankan fungsi anggaran dan pengawasan serta BPK memeriksa pengelolaan dan tanggung jawab keuangan negara.', 'BPK menetapkan APBN dan pemerintah hanya menyebarkannya.', 'DPR menjalankan seluruh transaksi belanja dan mengaudit dirinya sendiri.', 'Tidak ada lembaga negara yang berwenang memeriksa penggunaan APBN.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 23 ayat (1) UUD NRI 1945 mengatur APBN melalui undang-undang; Pasal 20A mengatur fungsi DPR termasuk anggaran dan pengawasan; Pasal 23E memberi BPK tugas pemeriksaan yang bebas dan mandiri. B, C, dan D mencampur atau meniadakan fungsi konstitusional tersebut.',
                'source_citation' => 'Pasal 20A, Pasal 23 ayat (1), dan Pasal 23E ayat (1) UUD NRI 1945; UU No. 17 Tahun 2003 tentang Keuangan Negara',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'hukum',
                'mastery_level' => 1,
                'prompt' => 'Apa makna Pasal 1 ayat (3) UUD NRI 1945 dalam penyelenggaraan pemerintahan sehari-hari?',
                'options' => $this->options('Indonesia adalah negara hukum, sehingga tindakan pemerintah harus memiliki dasar hukum dan tunduk pada hukum.', 'Pemerintah dapat bertindak di luar hukum saat kebijakan dianggap mendesak.', 'Hukum hanya mengikat warga dan tidak mengikat pejabat.', 'Setiap sengketa harus diputus melalui pemungutan suara umum.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 1 ayat (3) menyatakan Indonesia adalah negara hukum. Prinsip ini mengikat warga maupun pemerintah. B dan C bertentangan dengan supremasi hukum, sedangkan D bukan prosedur umum penyelesaian sengketa.',
                'source_citation' => 'Pasal 1 ayat (3) UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'hukum',
                'mastery_level' => 2,
                'prompt' => 'Ketika suatu keputusan pemerintahan dipersoalkan karena diduga melanggar aturan, lembaga peradilan harus memutus sengketa tanpa tekanan pihak yang berkepentingan. Prinsip yang menjadi dasar adalah...',
                'options' => $this->options('Kekuasaan kehakiman yang merdeka untuk menyelenggarakan peradilan guna menegakkan hukum dan keadilan.', 'Kewajiban hakim mengikuti instruksi pihak yang membuat keputusan.', 'Hak pemerintah membatalkan putusan pengadilan secara sepihak.', 'Kewenangan pengadilan hanya berlaku untuk perkara pidana.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 24 ayat (1) UUD NRI 1945 menegaskan kekuasaan kehakiman yang merdeka untuk menegakkan hukum dan keadilan. B dan C merusak independensi peradilan; D keliru karena yurisdiksi peradilan tidak terbatas pada perkara pidana.',
                'source_citation' => 'Pasal 24 ayat (1) UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'hukum',
                'mastery_level' => 4,
                'prompt' => 'Dalam menilai suatu undang-undang, warga menemukan bahwa proses pembentukannya tidak memenuhi syarat yang ditetapkan Mahkamah Konstitusi. Apa peran konstitusional putusan MK dalam situasi itu?',
                'options' => $this->options('Putusan MK menguji undang-undang terhadap UUD dan dapat menetapkan akibat hukum atas cacat konstitusional sesuai amar putusan.', 'Putusan MK menggantikan seluruh proses legislasi untuk semua rancangan undang-undang.', 'Putusan MK hanya berupa saran yang boleh diabaikan oleh semua lembaga.', 'Putusan MK otomatis mengubah semua peraturan yang tidak berkaitan.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 24C ayat (1) UUD NRI 1945 memberi MK kewenangan menguji undang-undang terhadap UUD, dan putusannya bersifat final. Putusan No. 91/PUU-XVIII/2020 menyatakan UU Cipta Kerja inkonstitusional bersyarat dan memerintahkan perbaikan dalam tenggat tertentu. B, C, dan D melebih-lebihkan atau menafikan kewenangan tersebut.',
                'source_citation' => 'Pasal 24C ayat (1) UUD NRI 1945; Putusan Mahkamah Konstitusi No. 91/PUU-XVIII/2020',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'politik_bernegara',
                'mastery_level' => 1,
                'prompt' => 'Asas pemilihan umum di Indonesia yang secara tegas disebut dalam Pasal 22E ayat (1) UUD NRI 1945 adalah...',
                'options' => $this->options('Langsung, umum, bebas, rahasia, jujur, dan adil setiap lima tahun.', 'Terbuka, wajib, seragam, dan hanya dilakukan jika pemerintah menghendaki.', 'Bebas, bertingkat, terbatas, dan dilaksanakan setiap tahun.', 'Rahasia, terbatas, dan hanya diikuti kelompok tertentu.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 22E ayat (1) UUD NRI 1945 menetapkan pemilu dilaksanakan secara langsung, umum, bebas, rahasia, jujur, dan adil setiap lima tahun sekali. B, C, dan D mengubah asas atau membatasi hak pilih tanpa dasar.',
                'source_citation' => 'Pasal 22E ayat (1) UUD NRI 1945; Pasal 167 ayat (1) UU No. 7 Tahun 2017 tentang Pemilihan Umum',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'politik_bernegara',
                'mastery_level' => 2,
                'prompt' => 'Mengapa penyelenggara pemilu perlu bersifat nasional, tetap, dan mandiri?',
                'options' => $this->options('Agar pemilu diselenggarakan oleh lembaga yang bebas dari campur tangan pihak berkepentingan dan bekerja konsisten di seluruh wilayah.', 'Agar penyelenggara dapat menentukan hasil tanpa menghitung suara.', 'Agar pemerintah dapat mengganti hasil pemilu tanpa proses hukum.', 'Agar peserta pemilu tidak perlu menaati aturan yang sama.'),
                'correct_option' => 'A',
                'explanation' => 'Pasal 22E ayat (5) UUD NRI 1945 menetapkan pemilu diselenggarakan oleh komisi pemilihan umum yang bersifat nasional, tetap, dan mandiri. B, C, dan D berlawanan dengan mandat penyelenggaraan yang independen dan adil.',
                'source_citation' => 'Pasal 22E ayat (5) UUD NRI 1945; UU No. 7 Tahun 2017 tentang Pemilihan Umum',
                'source_url' => null,
                'mastery_points' => 5,
            ],
            [
                'domain' => 'politik_bernegara',
                'mastery_level' => 4,
                'prompt' => 'Seorang pemilih memahami bahwa pemilu legislatif dan pemilu presiden dapat diselenggarakan serentak. Putusan MK yang menjadi dasar pergeseran dari pemilu terpisah ke pemilu serentak adalah...',
                'options' => $this->options('Putusan MK No. 14/PUU-XI/2013.', 'Putusan MK No. 97/PUU-XIV/2016.', 'Putusan MK No. 91/PUU-XVIII/2020.', 'Putusan MK yang tidak menguji aturan pemilu serentak.'),
                'correct_option' => 'A',
                'explanation' => 'Putusan MK No. 14/PUU-XI/2013 menguji aturan pemilu dan menjadi dasar penyelenggaraan pemilu legislatif serta presiden secara serentak mulai 2019. Putusan 97/PUU-XIV/2016 membahas administrasi penghayat kepercayaan; 91/PUU-XVIII/2020 mengenai UU Cipta Kerja; pilihan D bukan putusan yang menetapkan pemilu serentak.',
                'source_citation' => 'Putusan Mahkamah Konstitusi No. 14/PUU-XI/2013; Pasal 22E UUD NRI 1945',
                'source_url' => null,
                'mastery_points' => 5,
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, text: string}>
     */
    private function options(string $a, string $b, string $c, string $d): array
    {
        return [
            ['key' => 'A', 'text' => $a],
            ['key' => 'B', 'text' => $b],
            ['key' => 'C', 'text' => $c],
            ['key' => 'D', 'text' => $d],
        ];
    }
}
