<?php

namespace Database\Seeders;

use App\Models\CivicQuestion;
use Illuminate\Database\Seeder;

class CivicQuestionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->questions() as $question) {
            $question['mastery_level'] = min($question['mastery_level'], 2);

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
                'explanation' => 'Pasal 1 ayat (3) menyatakan Indonesia adalah negara hukum yang mengikat warga maupun pemerintah. Karena itu, tindakan pemerintah harus memiliki dasar hukum; pilihan B, C, dan D bertentangan dengan prinsip tersebut.',
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
                'explanation' => 'Pasal 24C ayat (1) UUD NRI 1945 memberi MK kewenangan menguji undang-undang terhadap UUD, dan putusannya bersifat final. Putusan No. 91/PUU-XVIII/2020 juga menunjukkan bahwa MK dapat menetapkan akibat hukum sesuai amar; pilihan B, C, dan D keliru menggambarkan kewenangan itu.',
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
            ...$this->additionalQuestions(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function additionalQuestions(): array
    {
        return [
            $this->question('hak_konstitusional', 1, 'Seorang anak tidak dapat bersekolah karena akses pendidikan di wilayahnya terbatas. Hak konstitusional yang paling langsung berkaitan adalah...', ['Hak setiap warga negara mendapat pendidikan.', 'Hak memperoleh jabatan publik tanpa persyaratan.', 'Hak menolak seluruh kewajiban hukum.', 'Hak mengubah putusan pengadilan melalui petisi.'], 'A', 'Pasal 31 ayat (1) UUD NRI 1945 menyatakan setiap warga negara berhak mendapat pendidikan.', 'Pasal 31 ayat (1) UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Warga menyampaikan kritik damai atas kebijakan pemerintah. Jaminan konstitusional yang paling relevan adalah...', ['Kebebasan berserikat, berkumpul, dan mengeluarkan pendapat.', 'Hak meminta pemerintah menghapus semua peraturan.', 'Hak melakukan tindakan apa pun tanpa tanggung jawab.', 'Hak memaksa pejabat menerima seluruh tuntutan.'], 'A', 'Pasal 28E ayat (3) UUD NRI 1945 menjamin kebebasan berserikat, berkumpul, dan mengeluarkan pendapat dengan tetap menghormati hak orang lain.', 'Pasal 28E ayat (3) dan Pasal 28J UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Penyebaran data pribadi warga tanpa dasar yang sah dapat mengancam hak atas...', ['Kesempatan menduduki jabatan tertentu.', 'Perlindungan diri pribadi, keluarga, kehormatan, martabat, dan harta benda.', 'Pembebasan dari kewajiban menaati hukum.', 'Kewenangan membatalkan keputusan lembaga negara.'], 'B', 'Pasal 28G ayat (1) UUD NRI 1945 melindungi diri pribadi, keluarga, kehormatan, martabat, dan harta benda. Pelindungan data pribadi diatur lebih lanjut dalam UU No. 27 Tahun 2022.', 'Pasal 28G ayat (1) UUD NRI 1945; UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi'),
            $this->question('hak_konstitusional', 3, 'Pembatasan kebebasan warga dalam suatu undang-undang hanya dapat dibenarkan apabila...', ['Ditetapkan oleh pejabat mana pun tanpa dasar tertulis.', 'Bertujuan mencegah kritik terhadap pemerintah.', 'Ditetapkan dengan undang-undang untuk tujuan yang sah dan menghormati hak orang lain.', 'Berlaku hanya bagi kelompok yang tidak sependapat.'], 'C', 'Pasal 28J UUD NRI 1945 mensyaratkan pembatasan ditetapkan dengan undang-undang untuk tujuan konstitusional serta menghormati hak orang lain.', 'Pasal 28J UUD NRI 1945'),
            $this->question('hak_konstitusional', 3, 'Jaminan konstitusi yang secara tegas melarang diskriminasi dan memberi hak atas perlindungan dari perlakuan diskriminatif terdapat pada...', ['Pasal 28I ayat (2) UUD NRI 1945.', 'Pasal 23 ayat (1) UUD NRI 1945.', 'Pasal 22E ayat (5) UUD NRI 1945.', 'Pasal 33 ayat (1) UUD NRI 1945.'], 'A', 'Pasal 28I ayat (2) menjamin kebebasan dari perlakuan diskriminatif atas dasar apa pun dan hak memperoleh perlindungan terhadap diskriminasi.', 'Pasal 28I ayat (2) UUD NRI 1945'),
            $this->question('hak_konstitusional', 3, 'Warga yang menghadapi proses hukum berhak atas perlakuan yang sama di hadapan hukum dan...', ['Kepastian hukum yang adil.', 'Putusan yang selalu menguntungkan dirinya.', 'Pengecualian dari proses peradilan.', 'Hak memilih hakim untuk setiap perkara.'], 'A', 'Pasal 28D ayat (1) UUD NRI 1945 menjamin kepastian hukum yang adil serta perlakuan yang sama di hadapan hukum.', 'Pasal 28D ayat (1) UUD NRI 1945'),
            $this->question('hak_konstitusional', 4, 'Hak untuk hidup dan hak untuk tidak disiksa disebut sebagai hak yang tidak dapat dikurangi dalam keadaan apa pun. Ketentuan ini terdapat pada...', ['Pasal 28J ayat (1) UUD NRI 1945.', 'Pasal 28I ayat (1) UUD NRI 1945.', 'Pasal 23A UUD NRI 1945.', 'Pasal 24C ayat (1) UUD NRI 1945.'], 'B', 'Pasal 28I ayat (1) menyebut hak-hak yang tidak dapat dikurangi dalam keadaan apa pun, termasuk hak untuk hidup dan hak untuk tidak disiksa.', 'Pasal 28I ayat (1) UUD NRI 1945'),

            $this->question('alasan_bernegara', 1, 'Menurut Pasal 1 ayat (1) UUD NRI 1945, bentuk negara Indonesia adalah...', ['Negara kesatuan yang berbentuk republik.', 'Negara federal berbentuk kerajaan.', 'Konfederasi negara-negara berdaulat.', 'Negara kota yang dipimpin parlemen.'], 'A', 'Pasal 1 ayat (1) UUD NRI 1945 menyatakan Negara Indonesia ialah Negara Kesatuan yang berbentuk Republik.', 'Pasal 1 ayat (1) UUD NRI 1945'),
            $this->question('alasan_bernegara', 1, 'Pancasila dalam kehidupan bernegara berfungsi sebagai...', ['Dasar negara dan sumber segala sumber hukum negara.', 'Pengganti seluruh undang-undang yang berlaku.', 'Program kerja tahunan pemerintah pusat.', 'Aturan teknis penyelenggaraan pemilu.'], 'A', 'Pasal 2 UU No. 12 Tahun 2011 menegaskan Pancasila merupakan sumber segala sumber hukum negara, bukan pengganti setiap aturan tertulis.', 'Pasal 2 UU No. 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan'),
            $this->question('alasan_bernegara', 2, 'Mengapa tujuan negara dalam Pembukaan UUD NRI 1945 mencakup mencerdaskan kehidupan bangsa?', ['Pendidikan dan pengetahuan warga merupakan kepentingan publik yang mendukung kehidupan bangsa.', 'Agar hanya pemerintah yang boleh mengembangkan ilmu pengetahuan.', 'Untuk membatasi pendidikan pada pejabat negara.', 'Supaya semua keputusan publik ditentukan satu kelompok.'], 'A', 'Mencerdaskan kehidupan bangsa adalah salah satu tujuan pembentukan pemerintah negara Indonesia dalam alinea keempat Pembukaan UUD NRI 1945.', 'Pembukaan UUD NRI 1945 alinea keempat'),
            $this->question('alasan_bernegara', 2, 'Semboyan Bhinneka Tunggal Ika pada lambang negara mengingatkan bahwa...', ['Keberagaman dapat hidup dalam persatuan Indonesia.', 'Semua warga harus memiliki kebudayaan yang sama.', 'Perbedaan pendapat tidak boleh disampaikan.', 'Daerah dapat mengabaikan konstitusi nasional.'], 'A', 'Pasal 36A UUD NRI 1945 menetapkan Garuda Pancasila dengan semboyan Bhinneka Tunggal Ika sebagai lambang negara, menegaskan persatuan dalam keberagaman.', 'Pasal 36A UUD NRI 1945'),
            $this->question('alasan_bernegara', 3, 'Nilai Pancasila yang paling membantu menilai keadilan kebijakan dengan dampak berbeda pada kelompok masyarakat adalah...', ['Keadilan sosial bagi seluruh rakyat Indonesia.', 'Keistimewaan permanen bagi kelompok terkuat.', 'Penghapusan partisipasi warga terdampak.', 'Penyeragaman seluruh kebutuhan daerah.'], 'A', 'Sila kelima Pancasila menempatkan keadilan sosial sebagai tujuan bersama; dampak dan akses yang adil perlu dipertimbangkan dalam kebijakan.', 'Pembukaan UUD NRI 1945; Pancasila sila kelima'),
            $this->question('alasan_bernegara', 3, 'Kedaulatan rakyat yang dilaksanakan menurut Undang-Undang Dasar berarti...', ['Kekuasaan publik bersumber pada rakyat dan dijalankan melalui aturan konstitusional.', 'Setiap pejabat dapat bertindak tanpa batas setelah dipilih.', 'Semua kebijakan harus diputus melalui referendum harian.', 'Lembaga negara tidak memerlukan pembagian kewenangan.'], 'A', 'Pasal 1 ayat (2) UUD NRI 1945 menempatkan kedaulatan di tangan rakyat dan pelaksanaannya menurut UUD, sehingga mandat publik dibatasi konstitusi.', 'Pasal 1 ayat (2) UUD NRI 1945'),
            $this->question('alasan_bernegara', 4, 'Ketentuan bahwa bentuk Negara Kesatuan Republik Indonesia tidak dapat diubah terdapat dalam...', ['Pasal 37 ayat (5) UUD NRI 1945.', 'Pasal 23 ayat (1) UUD NRI 1945.', 'Pasal 22E ayat (1) UUD NRI 1945.', 'Pasal 31 ayat (1) UUD NRI 1945.'], 'A', 'Pasal 37 ayat (5) UUD NRI 1945 menyatakan khusus mengenai bentuk Negara Kesatuan Republik Indonesia tidak dapat dilakukan perubahan.', 'Pasal 37 ayat (5) UUD NRI 1945'),

            $this->question('keuangan_negara', 1, 'Rancangan APBN diajukan Presiden untuk dibahas bersama...', ['DPR dengan memperhatikan pertimbangan DPD.', 'Mahkamah Agung sebagai pemegang kekuasaan kehakiman.', 'Komisi Pemilihan Umum sebagai penyelenggara pemilu.', 'Bank Indonesia tanpa pembahasan dengan DPR.'], 'A', 'Pasal 23 ayat (2) UUD NRI 1945 mengatur rancangan APBN diajukan Presiden untuk dibahas bersama DPR dengan memperhatikan pertimbangan DPD.', 'Pasal 23 ayat (2) UUD NRI 1945'),
            $this->question('keuangan_negara', 1, 'Pajak dan pungutan lain yang bersifat memaksa untuk keperluan negara harus diatur dengan...', ['Undang-undang.', 'Keputusan lisan pejabat setempat.', 'Kesepakatan informal antarwarga.', 'Surat edaran tanpa dasar hukum.'], 'A', 'Pasal 23A UUD NRI 1945 menyatakan pajak dan pungutan lain yang bersifat memaksa untuk keperluan negara diatur dengan undang-undang.', 'Pasal 23A UUD NRI 1945'),
            $this->question('keuangan_negara', 2, 'Apa peran utama Badan Pemeriksa Keuangan terhadap keuangan negara?', ['Memeriksa pengelolaan dan tanggung jawab keuangan negara secara bebas dan mandiri.', 'Menetapkan seluruh pajak tanpa undang-undang.', 'Menyusun dan menjalankan belanja kementerian.', 'Menggantikan DPR dalam membahas rancangan APBN.'], 'A', 'Pasal 23E ayat (1) UUD NRI 1945 menetapkan BPK yang bebas dan mandiri untuk memeriksa pengelolaan dan tanggung jawab keuangan negara.', 'Pasal 23E ayat (1) UUD NRI 1945'),
            $this->question('keuangan_negara', 2, 'Untuk menilai hasil program yang menggunakan APBN, warga sebaiknya memeriksa...', ['Tujuan, indikator hasil, dan pelaporan penggunaan dana yang dapat dipertanggungjawabkan.', 'Besarnya anggaran dibanding seluruh program lain saja.', 'Dokumen yang hanya dapat dibaca pelaksana.', 'Dukungan politik dari setiap warga.'], 'A', 'Keuangan negara dikelola secara efisien, efektif, transparan, dan bertanggung jawab; tujuan serta indikator membantu menilai hasil program.', 'Pasal 3 ayat (1) UU No. 17 Tahun 2003 tentang Keuangan Negara'),
            $this->question('keuangan_negara', 3, 'Jika DPR tidak menyetujui rancangan APBN yang diajukan Presiden, pemerintah menjalankan...', ['APBN tahun yang lalu.', 'Belanja tanpa batas anggaran.', 'Anggaran yang ditetapkan BPK.', 'Penghentian otomatis seluruh layanan publik.'], 'A', 'Pasal 23 ayat (3) UUD NRI 1945 menyatakan apabila DPR tidak menyetujui rancangan APBN, pemerintah menjalankan APBN tahun yang lalu.', 'Pasal 23 ayat (3) UUD NRI 1945'),
            $this->question('keuangan_negara', 3, 'Badan publik menolak seluruh permintaan informasi anggaran dengan alasan semua dokumen keuangan rahasia. Penilaian yang tepat adalah...', ['Penolakan menyeluruh tidak sesuai asas keterbukaan; pengecualian harus memiliki dasar hukum dan diterapkan terbatas.', 'Penolakan selalu benar karena anggaran tidak terkait kepentingan publik.', 'Informasi anggaran hanya boleh diberikan kepada pejabat.', 'Pemohon wajib menyatakan pilihan politiknya.'], 'A', 'UU Keterbukaan Informasi Publik mengatur informasi publik pada dasarnya terbuka, sedangkan pengecualian harus ketat dan terbatas.', 'Pasal 2 UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik'),
            $this->question('keuangan_negara', 4, 'Pejabat meminta penyedia pengadaan yang dibiayai APBN mengirim pembayaran ke rekening pribadinya. Respons warga paling bertanggung jawab adalah...', ['Menyimpan bukti yang diperoleh secara sah dan melapor melalui kanal resmi yang berwenang.', 'Menyebarkan data pribadi tanpa verifikasi.', 'Membayar karena dianggap kebiasaan administrasi.', 'Mengubah dokumen pengadaan untuk menyesuaikan permintaan.'], 'A', 'Permintaan keuntungan pribadi dalam pengadaan berindikasi korupsi. Pelaporan berbasis bukti melalui kanal resmi mendukung pemeriksaan dan akuntabilitas.', 'UU No. 31 Tahun 1999 jo. UU No. 20 Tahun 2001 tentang Pemberantasan Tindak Pidana Korupsi'),

            $this->question('hukum', 1, 'Kedudukan warga negara yang sama di dalam hukum dan pemerintahan ditegaskan dalam...', ['Pasal 27 ayat (1) UUD NRI 1945.', 'Pasal 23E ayat (1) UUD NRI 1945.', 'Pasal 33 ayat (4) UUD NRI 1945.', 'Pasal 36A UUD NRI 1945.'], 'A', 'Pasal 27 ayat (1) UUD NRI 1945 menyatakan warga negara bersamaan kedudukannya di dalam hukum dan pemerintahan serta wajib menjunjung hukum tanpa kecuali.', 'Pasal 27 ayat (1) UUD NRI 1945'),
            $this->question('hukum', 1, 'Dalam hierarki peraturan perundang-undangan, peraturan pemerintah tidak boleh bertentangan dengan...', ['Undang-undang atau peraturan yang lebih tinggi.', 'Pendapat pribadi setiap pejabat.', 'Keputusan organisasi nonpemerintah.', 'Kebiasaan tanpa dasar hukum.'], 'A', 'Sistem hierarki mengharuskan peraturan yang lebih rendah tunduk pada aturan yang lebih tinggi.', 'Pasal 7 UU No. 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan'),
            $this->question('hukum', 2, 'Lembaga yang berwenang menguji undang-undang terhadap UUD NRI 1945 adalah...', ['Mahkamah Konstitusi.', 'Mahkamah Agung.', 'Badan Pemeriksa Keuangan.', 'Komisi Pemilihan Umum.'], 'A', 'Pasal 24C ayat (1) UUD NRI 1945 memberi Mahkamah Konstitusi kewenangan menguji undang-undang terhadap UUD.', 'Pasal 24C ayat (1) UUD NRI 1945'),
            $this->question('hukum', 2, 'Lembaga yang berwenang menguji peraturan di bawah undang-undang terhadap undang-undang adalah...', ['Mahkamah Agung.', 'Mahkamah Konstitusi.', 'Dewan Perwakilan Rakyat.', 'Badan Pemeriksa Keuangan.'], 'A', 'Pasal 24A ayat (1) UUD NRI 1945 memberi Mahkamah Agung kewenangan menguji peraturan di bawah undang-undang terhadap undang-undang.', 'Pasal 24A ayat (1) UUD NRI 1945'),
            $this->question('hukum', 3, 'Orang atau kelompok orang miskin yang menghadapi masalah hukum dapat memperoleh bantuan hukum sesuai...', ['UU No. 16 Tahun 2011 tentang Bantuan Hukum dan prosedurnya.', 'Aturan bahwa hanya pejabat dapat didampingi penasihat hukum.', 'Kebijakan lisan yang berbeda bagi setiap terdakwa.', 'Ketentuan bahwa bantuan hukum hanya tersedia setelah perkara diputus.'], 'A', 'UU No. 16 Tahun 2011 tentang Bantuan Hukum menyediakan akses bantuan hukum bagi orang atau kelompok orang miskin sesuai ketentuan dan prosedur.', 'UU No. 16 Tahun 2011 tentang Bantuan Hukum'),
            $this->question('hukum', 3, 'Seseorang yang telah ditetapkan sebagai tersangka tetap harus diperlakukan...', ['Tidak bersalah sebelum ada putusan pengadilan yang berkekuatan hukum tetap.', 'Sebagai orang yang sudah diputus bersalah.', 'Tanpa hak memperoleh penasihat hukum.', 'Sebagai pihak yang tidak boleh mengajukan pembelaan.'], 'A', 'Asas praduga tak bersalah menuntut seseorang diperlakukan tidak bersalah sebelum kesalahannya dibuktikan melalui proses peradilan yang sah.', 'Pasal 8 ayat (1) UU No. 48 Tahun 2009 tentang Kekuasaan Kehakiman'),
            $this->question('hukum', 4, 'Hakim menerima instruksi dari pihak yang berkepentingan dalam suatu perkara. Prinsip yang paling langsung dilanggar adalah...', ['Kemerdekaan kekuasaan kehakiman.', 'Kewenangan BPK memeriksa APBN.', 'Hak DPR mengajukan rancangan undang-undang.', 'Asas pemilu langsung dan rahasia.'], 'A', 'Pasal 24 ayat (1) UUD NRI 1945 menetapkan kekuasaan kehakiman yang merdeka untuk menegakkan hukum dan keadilan.', 'Pasal 24 ayat (1) UUD NRI 1945'),

            $this->question('politik_bernegara', 1, 'Tiga fungsi DPR menurut UUD NRI 1945 adalah...', ['Legislasi, anggaran, dan pengawasan.', 'Peradilan, audit, dan penyidikan.', 'Moneter, diplomasi, dan pemungutan suara.', 'Pengangkatan kepala daerah, audit, dan legislasi.'], 'A', 'Pasal 20A ayat (1) UUD NRI 1945 menyebut DPR memiliki fungsi legislasi, anggaran, dan pengawasan.', 'Pasal 20A ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 1, 'Presiden dan Wakil Presiden dipilih...', ['Dalam satu pasangan secara langsung oleh rakyat.', 'Oleh Mahkamah Agung.', 'Oleh BPK bersama DPR.', 'Melalui penunjukan kepala daerah.'], 'A', 'Pasal 6A ayat (1) UUD NRI 1945 menyatakan Presiden dan Wakil Presiden dipilih dalam satu pasangan secara langsung oleh rakyat.', 'Pasal 6A ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 2, 'Apa arti asas pemilu “rahasia” bagi pemilih?', ['Pilihan pemilih dilindungi dari paksaan dan tidak diungkap melalui tata cara pemungutan suara.', 'Penyelenggara tidak perlu mengumumkan hasil pemilu.', 'Peserta pemilu boleh menyembunyikan laporan dana kampanye.', 'Pemilih tidak boleh mengetahui program peserta pemilu.'], 'A', 'Asas rahasia melindungi pilihan pemilih dari tekanan; asas ini tidak meniadakan kewajiban mengumumkan hasil secara transparan.', 'Pasal 22E ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 2, 'Praktik yang paling mendukung partisipasi bermakna warga dalam rancangan kebijakan publik adalah...', ['Menyampaikan masukan melalui kanal resmi dan meminta penjelasan tentang tindak lanjutnya.', 'Menganggap konsultasi menggantikan seluruh proses legislasi.', 'Menyebarkan informasi yang belum diverifikasi.', 'Membatasi masukan hanya kepada pendukung kebijakan.'], 'A', 'Partisipasi memberi ruang bagi warga terdampak untuk menyampaikan pandangan dan memperoleh informasi proses, tanpa menggantikan kewenangan lembaga konstitusional.', 'UU No. 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan, sebagaimana diubah'),
            $this->question('politik_bernegara', 3, 'Anggota DPR dipilih melalui pemilu. Hal tersebut menunjukkan bahwa...', ['Rakyat memberikan mandat perwakilan melalui pemilihan umum.', 'Anggota DPR dapat mengabaikan konstitusi setelah terpilih.', 'Pemilu menghapus kewajiban DPR untuk diawasi.', 'Perwakilan rakyat tidak perlu bertanggung jawab kepada publik.'], 'A', 'Pasal 19 ayat (1) UUD NRI 1945 menetapkan anggota DPR dipilih melalui pemilihan umum. Mandat representasi tetap dijalankan dalam batas konstitusi dan hukum.', 'Pasal 19 ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 3, 'Pemilih menemukan indikasi pembagian uang untuk memengaruhi pilihannya. Respons yang paling bertanggung jawab adalah...', ['Menolak, menyimpan bukti yang diperoleh secara sah, dan melapor kepada pengawas pemilu.', 'Menerima uang dan menjanjikan suara kepada pemberi.', 'Menyebarkan data pribadi terduga tanpa verifikasi.', 'Merusak surat suara orang lain.'], 'A', 'Politik uang merusak pemilu yang jujur dan adil. Bukti yang sah dapat dilaporkan melalui mekanisme pengawasan dan penegakan hukum pemilu.', 'Pasal 22E ayat (1) UUD NRI 1945; UU No. 7 Tahun 2017 tentang Pemilihan Umum'),
            $this->question('politik_bernegara', 4, 'Rancangan undang-undang dibahas oleh DPR dan Presiden untuk mendapat persetujuan bersama. Prinsip ini mencerminkan...', ['Pembentukan undang-undang melibatkan lembaga perwakilan dan Presiden menurut prosedur konstitusi.', 'Presiden dapat membuat semua undang-undang tanpa DPR.', 'DPR dapat menggantikan pengadilan memutus perkara.', 'Setiap undang-undang wajib disetujui lewat referendum.'], 'A', 'Pasal 20 ayat (2) UUD NRI 1945 menetapkan setiap rancangan undang-undang dibahas DPR dan Presiden untuk mendapat persetujuan bersama.', 'Pasal 20 ayat (2) UUD NRI 1945'),

            $this->question('hak_konstitusional', 1, 'Setiap warga negara wajib menjunjung hukum dan pemerintahan tanpa kecuali. Ketentuan ini tercantum dalam...', ['Pasal 27 ayat (1) UUD NRI 1945.', 'Pasal 23E ayat (1) UUD NRI 1945.', 'Pasal 31 ayat (1) UUD NRI 1945.', 'Pasal 36A UUD NRI 1945.'], 'A', 'Pasal 27 ayat (1) UUD NRI 1945 menyatakan warga negara memiliki kedudukan yang sama dalam hukum dan pemerintahan serta wajib menjunjungnya tanpa kecuali.', 'Pasal 27 ayat (1) UUD NRI 1945'),
            $this->question('hak_konstitusional', 1, 'Warga negara yang memenuhi syarat berhak mendapat pekerjaan dan penghidupan yang layak bagi kemanusiaan. Jaminan ini terdapat pada...', ['Pasal 27 ayat (2) UUD NRI 1945.', 'Pasal 22E ayat (1) UUD NRI 1945.', 'Pasal 23A UUD NRI 1945.', 'Pasal 24C ayat (1) UUD NRI 1945.'], 'A', 'Pasal 27 ayat (2) UUD NRI 1945 menjamin tiap warga negara berhak atas pekerjaan dan penghidupan yang layak bagi kemanusiaan.', 'Pasal 27 ayat (2) UUD NRI 1945'),
            $this->question('hak_konstitusional', 1, 'Hak untuk memeluk agama dan beribadat menurut agama atau kepercayaannya dijamin oleh...', ['Pasal 28E ayat (1) UUD NRI 1945.', 'Pasal 23 ayat (1) UUD NRI 1945.', 'Pasal 20A ayat (1) UUD NRI 1945.', 'Pasal 33 ayat (4) UUD NRI 1945.'], 'A', 'Pasal 28E ayat (1) UUD NRI 1945 menjamin kebebasan memeluk agama dan beribadat menurut agama, memilih pendidikan, pekerjaan, kewarganegaraan, tempat tinggal, serta meninggalkannya dan kembali.', 'Pasal 28E ayat (1) UUD NRI 1945'),
            $this->question('hak_konstitusional', 1, 'Warga yang membayar pajak sesuai ketentuan undang-undang sedang memenuhi...', ['Kewajiban perpajakan yang diatur berdasarkan undang-undang.', 'Hak untuk menentukan sendiri besaran pajak tanpa aturan.', 'Kewenangan untuk mengaudit pemerintah secara pribadi.', 'Kewajiban untuk mengikuti kegiatan politik tertentu.'], 'A', 'Pasal 23A UUD NRI 1945 menyatakan pajak dan pungutan lain yang bersifat memaksa untuk keperluan negara diatur dengan undang-undang.', 'Pasal 23A UUD NRI 1945'),
            $this->question('hak_konstitusional', 1, 'Kebebasan menyampaikan pendapat di muka umum harus dijalankan dengan cara yang...', ['Menghormati hak orang lain dan tunduk pada pembatasan yang ditetapkan undang-undang.', 'Menghapus hak warga lain untuk berpendapat.', 'Bebas dari segala tanggung jawab hukum.', 'Memaksa semua orang menyetujui pendapat tersebut.'], 'A', 'Pasal 28J UUD NRI 1945 mewajibkan setiap orang menghormati hak asasi orang lain dan tunduk pada pembatasan yang ditetapkan dengan undang-undang.', 'Pasal 28J UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Warga mengusulkan perbaikan akses bagi penyandang disabilitas pada fasilitas umum. Hak yang paling langsung mendukung usulan itu adalah...', ['Hak memperoleh kemudahan dan perlakuan khusus untuk mencapai persamaan dan keadilan.', 'Hak untuk tidak mengikuti aturan keselamatan umum.', 'Hak memperoleh jabatan publik tanpa proses seleksi.', 'Hak menghapus fasilitas yang digunakan warga lain.'], 'A', 'Pasal 28H ayat (2) UUD NRI 1945 memberikan hak atas kemudahan dan perlakuan khusus untuk memperoleh kesempatan dan manfaat yang sama guna mencapai persamaan dan keadilan.', 'Pasal 28H ayat (2) UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Warga bergotong royong menjaga kebersihan sungai di lingkungannya. Tindakan ini paling sesuai dengan tanggung jawab...', ['Menjaga lingkungan hidup yang baik dan sehat.', 'Menetapkan undang-undang nasional.', 'Menggantikan tugas semua lembaga pengawas.', 'Mengatur pengadilan di wilayahnya.'], 'A', 'Pasal 28H ayat (1) UUD NRI 1945 menjamin hak atas lingkungan hidup yang baik dan sehat; menjaga lingkungan juga merupakan tanggung jawab bersama.', 'Pasal 28H ayat (1) UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Negara mengalokasikan anggaran pendidikan sesuai ketentuan konstitusi. Ketentuan anggaran pendidikan sekurang-kurangnya dua puluh persen tercantum dalam...', ['Pasal 31 ayat (4) UUD NRI 1945.', 'Pasal 27 ayat (1) UUD NRI 1945.', 'Pasal 22E ayat (5) UUD NRI 1945.', 'Pasal 24A ayat (1) UUD NRI 1945.'], 'A', 'Pasal 31 ayat (4) UUD NRI 1945 mengamanatkan prioritas anggaran pendidikan sekurang-kurangnya dua puluh persen dari APBN dan APBD.', 'Pasal 31 ayat (4) UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Warga yang memenuhi syarat dapat ikut serta dalam upaya pembelaan negara sebagai...', ['Hak dan kewajiban warga negara.', 'Hak khusus pejabat pemerintah.', 'Kewajiban yang hanya berlaku bagi penduduk ibu kota.', 'Pengganti seluruh hak konstitusional lain.'], 'A', 'Pasal 27 ayat (3) UUD NRI 1945 menyebut setiap warga negara berhak dan wajib ikut serta dalam upaya pembelaan negara.', 'Pasal 27 ayat (3) UUD NRI 1945'),
            $this->question('hak_konstitusional', 2, 'Seorang warga meminta layanan kesehatan tanpa perlakuan diskriminatif. Hak yang paling relevan adalah hak atas...', ['Pelayanan kesehatan dan lingkungan hidup yang baik dan sehat.', 'Jaminan bahwa setiap permintaan layanan harus disetujui tanpa prosedur.', 'Kewenangan menggantikan tenaga kesehatan.', 'Perlakuan berbeda berdasarkan pilihan politik.'], 'A', 'Pasal 28H ayat (1) UUD NRI 1945 menjamin hak untuk memperoleh pelayanan kesehatan.', 'Pasal 28H ayat (1) UUD NRI 1945'),

            $this->question('alasan_bernegara', 1, 'Sila pertama Pancasila berbunyi...', ['Ketuhanan Yang Maha Esa.', 'Kemanusiaan yang adil dan beradab.', 'Persatuan Indonesia.', 'Keadilan sosial bagi seluruh rakyat Indonesia.'], 'A', 'Ketuhanan Yang Maha Esa merupakan sila pertama Pancasila dan menjadi bagian dari dasar negara Indonesia.', 'Pembukaan UUD NRI 1945'),
            $this->question('alasan_bernegara', 1, 'Sikap membantu warga lain tanpa membeda-bedakan latar belakang paling mencerminkan sila...', ['Kemanusiaan yang adil dan beradab.', 'Persatuan Indonesia.', 'Kerakyatan yang dipimpin oleh hikmat kebijaksanaan dalam permusyawaratan/perwakilan.', 'Keadilan sosial bagi seluruh rakyat Indonesia.'], 'A', 'Sila kedua mengajarkan pengakuan martabat manusia dan perlakuan yang adil serta beradab terhadap sesama.', 'Pancasila sila kedua; Pembukaan UUD NRI 1945'),
            $this->question('alasan_bernegara', 1, 'Musyawarah untuk mencapai keputusan bersama mencerminkan nilai Pancasila yang berbunyi...', ['Kerakyatan yang dipimpin oleh hikmat kebijaksanaan dalam permusyawaratan/perwakilan.', 'Ketuhanan Yang Maha Esa.', 'Persatuan Indonesia.', 'Kemanusiaan yang adil dan beradab.'], 'A', 'Sila keempat menempatkan musyawarah dan perwakilan sebagai nilai dalam kehidupan bersama.', 'Pancasila sila keempat; Pembukaan UUD NRI 1945'),
            $this->question('alasan_bernegara', 1, 'Menghargai kebudayaan daerah lain sebagai bagian dari identitas bangsa paling langsung mendukung nilai...', ['Persatuan Indonesia.', 'Keistimewaan satu kebudayaan di atas semua yang lain.', 'Penghapusan seluruh tradisi daerah.', 'Pemisahan warga berdasarkan asal daerah.'], 'A', 'Sila ketiga Pancasila menekankan persatuan Indonesia yang tetap dapat menaungi keberagaman masyarakat.', 'Pancasila sila ketiga; Pasal 32 UUD NRI 1945'),
            $this->question('alasan_bernegara', 1, 'Tujuan negara untuk ikut melaksanakan ketertiban dunia berdasarkan kemerdekaan, perdamaian abadi, dan keadilan sosial tercantum dalam...', ['Pembukaan UUD NRI 1945 alinea keempat.', 'Pasal 23A UUD NRI 1945.', 'Pasal 22E ayat (5) UUD NRI 1945.', 'Pasal 31 ayat (1) UUD NRI 1945.'], 'A', 'Alinea keempat Pembukaan UUD NRI 1945 memuat tujuan negara tersebut sebagai bagian dari amanat konstitusional.', 'Pembukaan UUD NRI 1945 alinea keempat'),
            $this->question('alasan_bernegara', 2, 'Pancasila disebut sumber segala sumber hukum negara. Apa makna praktisnya dalam penyusunan peraturan?', ['Materi peraturan harus selaras dengan nilai Pancasila dan konstitusi.', 'Pancasila menghapus kebutuhan akan undang-undang tertulis.', 'Setiap peraturan hanya boleh mengatur satu sila.', 'Peraturan dapat mengabaikan hak warga jika memakai nama Pancasila.'], 'A', 'Pasal 2 UU No. 12 Tahun 2011 menempatkan Pancasila sebagai sumber segala sumber hukum negara, sehingga pembentukan hukum harus selaras dengan nilai dasarnya.', 'Pasal 2 UU No. 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan'),
            $this->question('alasan_bernegara', 2, 'Negara melindungi seluruh bangsa dan wilayah Indonesia. Tujuan ini paling sesuai dengan gagasan...', ['Negara hadir untuk melindungi segenap bangsa dan seluruh tumpah darah Indonesia.', 'Negara hanya melayani satu kelompok masyarakat.', 'Perlindungan diberikan berdasarkan dukungan politik.', 'Pemerintah tidak memiliki tanggung jawab terhadap keselamatan warga.'], 'A', 'Melindungi segenap bangsa dan seluruh tumpah darah Indonesia adalah tujuan negara yang tercantum dalam alinea keempat Pembukaan UUD NRI 1945.', 'Pembukaan UUD NRI 1945 alinea keempat'),
            $this->question('alasan_bernegara', 2, 'Penyelesaian masalah lingkungan yang mempertimbangkan kebutuhan warga saat ini dan generasi mendatang paling sejalan dengan...', ['Prinsip keberlanjutan dan keadilan sosial.', 'Kepentingan jangka pendek tanpa mempertimbangkan dampak.', 'Hak satu kelompok untuk menghabiskan seluruh sumber daya.', 'Larangan bagi masyarakat untuk ikut memberi masukan.'], 'A', 'Pasal 33 ayat (4) UUD NRI 1945 memasukkan prinsip keberlanjutan dan berwawasan lingkungan dalam penyelenggaraan perekonomian nasional.', 'Pasal 33 ayat (4) UUD NRI 1945'),
            $this->question('alasan_bernegara', 2, 'Perbedaan agama dan budaya warga di Indonesia seharusnya disikapi dengan...', ['Menghormati keberagaman dan menjaga persatuan dalam kehidupan bersama.', 'Mewajibkan semua warga meninggalkan identitas budayanya.', 'Membatasi pelayanan publik hanya pada kelompok mayoritas.', 'Menganggap perbedaan sebagai alasan untuk meniadakan hukum.'], 'A', 'Bhinneka Tunggal Ika menggambarkan persatuan dalam keberagaman, sejalan dengan jaminan konstitusi atas kebebasan beragama dan kesetaraan warga.', 'Pasal 28E dan Pasal 36A UUD NRI 1945'),
            $this->question('alasan_bernegara', 2, 'Pancasila dan UUD NRI 1945 berhubungan dalam kehidupan bernegara karena...', ['Pancasila menjadi dasar negara, sedangkan UUD mengatur kerangka konstitusional penyelenggaraan negara.', 'UUD menggantikan seluruh nilai Pancasila.', 'Pancasila hanya berlaku untuk urusan pribadi.', 'Keduanya memberi kewenangan tanpa batas kepada pemerintah.'], 'A', 'Pancasila merupakan dasar negara dan sumber segala sumber hukum, sementara UUD NRI 1945 memuat aturan dasar penyelenggaraan negara serta hak warga.', 'Pembukaan UUD NRI 1945; Pasal 2 UU No. 12 Tahun 2011'),

            $this->question('keuangan_negara', 1, 'Anggaran tahunan pemerintah daerah yang dibahas bersama DPRD dan kepala daerah disebut...', ['Anggaran Pendapatan dan Belanja Daerah (APBD).', 'Anggaran Pendapatan dan Belanja Negara (APBN).', 'Laporan hasil pemeriksaan BPK.', 'Rencana anggaran rumah tangga warga.'], 'A', 'APBD merupakan rencana keuangan tahunan daerah yang ditetapkan dengan peraturan daerah setelah dibahas bersama DPRD dan kepala daerah.', 'UU No. 23 Tahun 2014 tentang Pemerintahan Daerah; UU No. 17 Tahun 2003 tentang Keuangan Negara'),
            $this->question('keuangan_negara', 1, 'APBN digunakan untuk merencanakan keuangan pemerintah pada tingkat...', ['Pusat atau nasional.', 'Desa saja.', 'Satu keluarga.', 'Satu badan usaha swasta.'], 'A', 'APBN adalah rencana keuangan tahunan pemerintahan pusat yang ditetapkan dengan undang-undang.', 'Pasal 23 ayat (1) UUD NRI 1945'),
            $this->question('keuangan_negara', 1, 'APBD terutama merencanakan penerimaan dan belanja pada tingkat...', ['Pemerintah daerah.', 'Pemerintah pusat saja.', 'Lembaga peradilan nasional saja.', 'Organisasi masyarakat tanpa anggaran publik.'], 'A', 'APBD merinci rencana keuangan tahunan pemerintah daerah dan menjadi dasar pelaksanaan anggaran di daerah.', 'UU No. 17 Tahun 2003 tentang Keuangan Negara; UU No. 23 Tahun 2014 tentang Pemerintahan Daerah'),
            $this->question('keuangan_negara', 1, 'Pemerintah membangun sekolah negeri menggunakan anggaran yang telah disetujui. Pengeluaran tersebut termasuk...', ['Belanja publik untuk mendukung pelayanan dan kepentingan masyarakat.', 'Penerimaan pajak bagi pemerintah.', 'Pinjaman pribadi warga.', 'Dana kampanye peserta pemilu.'], 'A', 'Belanja pemerintah membiayai program dan layanan publik sesuai tujuan serta alokasi yang ditetapkan dalam anggaran.', 'UU No. 17 Tahun 2003 tentang Keuangan Negara'),
            $this->question('keuangan_negara', 1, 'Dokumen APBN ditetapkan dalam bentuk...', ['Undang-undang.', 'Surat pribadi pejabat.', 'Keputusan tidak tertulis.', 'Perjanjian antarwarga.'], 'A', 'Pasal 23 ayat (1) UUD NRI 1945 menetapkan APBN sebagai wujud pengelolaan keuangan negara tahunan yang ditetapkan dengan undang-undang.', 'Pasal 23 ayat (1) UUD NRI 1945'),
            $this->question('keuangan_negara', 2, 'Pendapatan asli daerah (PAD) merupakan salah satu sumber pendapatan dalam...', ['APBD.', 'APBN saja.', 'Laporan pengadilan.', 'Daftar pemilih tetap.'], 'A', 'PAD adalah salah satu sumber pendapatan daerah yang dicatat dan dikelola melalui APBD sesuai ketentuan peraturan perundang-undangan.', 'UU No. 23 Tahun 2014 tentang Pemerintahan Daerah'),
            $this->question('keuangan_negara', 2, 'Warga ingin menilai apakah belanja daerah mencapai tujuan program. Informasi yang paling membantu adalah...', ['Target program, realisasi anggaran, hasil yang dicapai, dan laporan pertanggungjawaban.', 'Jumlah dokumen yang dicetak tanpa melihat isinya.', 'Pendapat satu orang tanpa data pendukung.', 'Daftar kegiatan yang tidak berkaitan dengan anggaran.'], 'A', 'Pengelolaan keuangan yang efektif dan bertanggung jawab dapat dinilai dengan membandingkan tujuan, realisasi, hasil, dan pelaporan program.', 'Pasal 3 ayat (1) UU No. 17 Tahun 2003 tentang Keuangan Negara'),
            $this->question('keuangan_negara', 2, 'Rencana belanja daerah harus dibahas oleh kepala daerah bersama...', ['DPRD.', 'Mahkamah Konstitusi.', 'Komisi Pemilihan Umum.', 'Badan Pemeriksa Keuangan sebagai penetap anggaran.'], 'A', 'APBD dibahas dan disetujui bersama oleh pemerintah daerah dan DPRD menurut tata cara yang ditetapkan dalam peraturan perundang-undangan.', 'UU No. 23 Tahun 2014 tentang Pemerintahan Daerah'),
            $this->question('keuangan_negara', 2, 'Mengapa laporan penggunaan APBN dan APBD perlu dapat dipertanggungjawabkan?', ['Agar publik dan lembaga berwenang dapat menilai kepatuhan serta hasil penggunaan uang negara.', 'Agar seluruh rincian anggaran selalu dirahasiakan.', 'Agar anggaran dapat digunakan tanpa tujuan yang jelas.', 'Agar pemeriksaan keuangan tidak diperlukan.'], 'A', 'Uang negara harus dikelola secara transparan dan bertanggung jawab untuk kepentingan masyarakat serta dapat diperiksa sesuai kewenangan.', 'Pasal 23 ayat (1) dan Pasal 23E ayat (1) UUD NRI 1945'),
            $this->question('keuangan_negara', 2, 'Jika warga menemukan ketidaksesuaian antara rencana dan realisasi proyek daerah, langkah awal yang konstruktif adalah...', ['Memeriksa informasi resmi dan menyampaikan pertanyaan atau laporan melalui kanal pengaduan yang tersedia.', 'Menyebarkan tuduhan tanpa bukti kepada semua orang.', 'Mengubah dokumen anggaran agar tampak sesuai.', 'Menghentikan layanan publik secara sepihak.'], 'A', 'Pemeriksaan informasi dan pelaporan melalui mekanisme resmi mendukung transparansi serta proses tindak lanjut yang dapat dipertanggungjawabkan.', 'UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik; UU No. 23 Tahun 2014 tentang Pemerintahan Daerah'),

            $this->question('hukum', 1, 'Seseorang yang mengetahui dugaan korupsi sebaiknya...', ['Melaporkan melalui kanal resmi dengan menyampaikan informasi yang diperoleh secara sah.', 'Menyebarkan identitas dan alamat terduga tanpa verifikasi.', 'Menghapus dokumen yang mungkin menjadi bukti.', 'Meminta imbalan agar tidak melapor.'], 'A', 'Pelaporan melalui mekanisme resmi membantu pihak berwenang menilai informasi dan menindaklanjutinya sesuai hukum.', 'UU No. 31 Tahun 1999 jo. UU No. 20 Tahun 2001 tentang Pemberantasan Tindak Pidana Korupsi'),
            $this->question('hukum', 1, 'Dalam proses peradilan, seseorang dianggap tidak bersalah sampai kesalahannya dibuktikan melalui putusan yang berkekuatan hukum tetap. Ini disebut...', ['Asas praduga tak bersalah.', 'Asas kerahasiaan anggaran.', 'Asas pemilu serentak.', 'Asas otonomi daerah.'], 'A', 'Asas praduga tak bersalah melindungi hak seseorang selama proses peradilan dan melarang pemidanaan sebelum kesalahan dibuktikan secara sah.', 'Pasal 8 ayat (1) UU No. 48 Tahun 2009 tentang Kekuasaan Kehakiman'),
            $this->question('hukum', 1, 'Memberi atau menerima hadiah kepada pejabat karena jabatannya dapat menimbulkan risiko...', ['Benturan kepentingan atau gratifikasi yang perlu dinilai menurut aturan yang berlaku.', 'Penghapusan otomatis seluruh kewajiban hukum.', 'Perubahan sah atas hasil pemilu.', 'Penggantian tugas lembaga audit.'], 'A', 'Hadiah terkait jabatan dapat memengaruhi integritas dan perlu dinilai berdasarkan ketentuan gratifikasi serta pemberantasan korupsi.', 'UU No. 31 Tahun 1999 jo. UU No. 20 Tahun 2001 tentang Pemberantasan Tindak Pidana Korupsi'),
            $this->question('hukum', 1, 'Semua warga dan pejabat memiliki kedudukan yang sama di hadapan hukum. Prinsip ini dikenal sebagai...', ['Persamaan di hadapan hukum.', 'Kekebalan hukum tanpa batas.', 'Kewenangan membuat hukum sendiri.', 'Kerahasiaan proses pemerintahan.'], 'A', 'Pasal 27 ayat (1) UUD NRI 1945 menegaskan persamaan kedudukan warga negara di dalam hukum dan pemerintahan.', 'Pasal 27 ayat (1) UUD NRI 1945'),
            $this->question('hukum', 1, 'Dalam pengadaan barang dan jasa publik, proses yang terbuka dan sesuai aturan terutama membantu mencegah...', ['Penyalahgunaan kewenangan dan benturan kepentingan.', 'Akses publik terhadap informasi yang sah.', 'Pemeriksaan hasil pekerjaan.', 'Persaingan yang wajar antarpenyedia.'], 'A', 'Proses pengadaan yang transparan dan berintegritas mengurangi ruang penyalahgunaan kewenangan serta mendukung persaingan yang adil.', 'Peraturan Presiden No. 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah, sebagaimana diubah'),
            $this->question('hukum', 2, 'Seorang pejabat ikut menentukan pemenang pengadaan yang melibatkan usaha milik keluarganya. Risiko utama yang perlu ditangani adalah...', ['Benturan kepentingan yang dapat mengganggu objektivitas keputusan.', 'Kewajiban mengikuti pemilu.', 'Pelanggaran asas rahasia pemilih.', 'Perubahan otomatis terhadap APBN.'], 'A', 'Hubungan pribadi dalam keputusan jabatan dapat menimbulkan benturan kepentingan; pejabat harus mengikuti aturan pengungkapan dan pengendalian yang berlaku.', 'UU No. 30 Tahun 2014 tentang Administrasi Pemerintahan'),
            $this->question('hukum', 2, 'Warga meminta informasi publik tentang kontrak belanja, tetapi sebagian dokumen memuat informasi yang dikecualikan secara sah. Badan publik sebaiknya...', ['Membuka bagian yang dapat diakses dan menjelaskan pengecualian secara terbatas beserta dasar hukumnya.', 'Menolak seluruh informasi tanpa penjelasan.', 'Membuka data pribadi yang tidak berkaitan.', 'Mensyaratkan pemohon menyatakan pilihan politik.'], 'A', 'Asas keterbukaan informasi disertai pengecualian yang ketat dan terbatas; bagian informasi yang terbuka tetap perlu diberikan.', 'Pasal 2 dan Pasal 17 UU No. 14 Tahun 2008 tentang Keterbukaan Informasi Publik'),
            $this->question('hukum', 2, 'Lembaga yang memeriksa pengelolaan dan tanggung jawab keuangan negara secara bebas dan mandiri adalah...', ['Badan Pemeriksa Keuangan.', 'Komisi Pemilihan Umum.', 'Mahkamah Konstitusi.', 'Dewan Perwakilan Rakyat Daerah.'], 'A', 'Pasal 23E ayat (1) UUD NRI 1945 membentuk BPK yang bebas dan mandiri untuk memeriksa pengelolaan dan tanggung jawab keuangan negara.', 'Pasal 23E ayat (1) UUD NRI 1945'),
            $this->question('hukum', 2, 'Warga yang tidak mampu menghadapi perkara hukum dapat mencari bantuan melalui...', ['Pemberi bantuan hukum sesuai ketentuan undang-undang.', 'Pihak yang menjanjikan putusan tertentu.', 'Pejabat yang berwenang mengubah putusan pengadilan.', 'Perantara yang meminta penghilangan bukti.'], 'A', 'UU Bantuan Hukum menyediakan akses bantuan hukum bagi orang atau kelompok orang miskin sesuai syarat dan prosedur yang berlaku.', 'UU No. 16 Tahun 2011 tentang Bantuan Hukum'),
            $this->question('hukum', 2, 'Jika menemukan indikasi penyalahgunaan anggaran, warga sebaiknya menyimpan bukti dengan cara...', ['Memperoleh dan menyampaikannya secara sah tanpa mengubah dokumen atau melanggar privasi.', 'Mengakses akun orang lain tanpa izin.', 'Mengedit dokumen agar terlihat lebih meyakinkan.', 'Menyebarkan data pribadi yang tidak relevan.'], 'A', 'Informasi yang diperoleh secara sah dan tidak diubah lebih berguna untuk pemeriksaan; pelaporan tetap harus menghormati hukum dan privasi.', 'UU No. 31 Tahun 1999 jo. UU No. 20 Tahun 2001 tentang Pemberantasan Tindak Pidana Korupsi'),

            $this->question('politik_bernegara', 1, 'Pemilu di Indonesia dilaksanakan berdasarkan asas...', ['Langsung, umum, bebas, rahasia, jujur, dan adil.', 'Terbatas, wajib, terbuka, dan bertingkat.', 'Bebas, tidak teratur, dan hanya untuk kelompok tertentu.', 'Rahasia bagi penyelenggara dan tertutup bagi pemilih.'], 'A', 'Pasal 22E ayat (1) UUD NRI 1945 menetapkan asas langsung, umum, bebas, rahasia, jujur, dan adil dalam pemilu.', 'Pasal 22E ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 1, 'Dalam pemilu yang demokratis, warga yang memenuhi syarat dapat...', ['Memilih tanpa paksaan dan menentukan pilihannya secara rahasia.', 'Menentukan pilihan orang lain dengan ancaman.', 'Meminta penyelenggara mengubah suara tanpa proses.', 'Membeli hak pilih warga lain.'], 'A', 'Asas bebas dan rahasia melindungi pemilih dari paksaan serta menjaga kerahasiaan pilihannya.', 'Pasal 22E ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 1, 'Kedaulatan rakyat menurut UUD NRI 1945 dilaksanakan...', ['Menurut Undang-Undang Dasar.', 'Tanpa batas hukum.', 'Hanya melalui satu lembaga.', 'Dengan mengabaikan hak warga minoritas.'], 'A', 'Pasal 1 ayat (2) UUD NRI 1945 menyatakan kedaulatan berada di tangan rakyat dan dilaksanakan menurut Undang-Undang Dasar.', 'Pasal 1 ayat (2) UUD NRI 1945'),
            $this->question('politik_bernegara', 1, 'Tiga fungsi Dewan Perwakilan Rakyat adalah legislasi, anggaran, dan...', ['Pengawasan.', 'Peradilan.', 'Penyelenggaraan pemilu.', 'Pemeriksaan keuangan negara.'], 'A', 'Pasal 20A ayat (1) UUD NRI 1945 menyebut fungsi DPR meliputi legislasi, anggaran, dan pengawasan.', 'Pasal 20A ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 1, 'Masyarakat dapat berpartisipasi dalam pembahasan kebijakan publik dengan cara...', ['Menyampaikan masukan melalui forum atau kanal resmi yang tersedia.', 'Menghalangi warga lain menyampaikan pendapat.', 'Mengganti proses hukum dengan tekanan pribadi.', 'Menyebarkan informasi yang belum diperiksa.'], 'A', 'Partisipasi yang tertib melalui kanal yang tersedia membantu pembuat kebijakan memahami masukan warga dan tetap menghormati prosedur.', 'UU No. 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan, sebagaimana diubah'),
            $this->question('politik_bernegara', 2, 'Mengapa pemilih sebaiknya memeriksa informasi pemilu dari sumber resmi dan tepercaya?', ['Agar keputusan dan partisipasi didasarkan pada informasi yang dapat diverifikasi.', 'Agar pemilih tidak perlu membandingkan program atau gagasan.', 'Agar semua kritik terhadap penyelenggara diabaikan.', 'Agar hasil pemilu dapat ditentukan oleh pesan berantai.'], 'A', 'Informasi yang terverifikasi membantu warga berpartisipasi secara sadar dan mengurangi penyebaran kabar keliru.', 'UU No. 7 Tahun 2017 tentang Pemilihan Umum'),
            $this->question('politik_bernegara', 2, 'Jika warga tidak setuju terhadap kebijakan publik, cara demokratis untuk menyampaikan keberatan adalah...', ['Menyampaikan pendapat secara damai dan menggunakan mekanisme partisipasi atau pengaduan yang berlaku.', 'Mengancam pihak yang berbeda pendapat.', 'Menyebarkan identitas pribadi pembuat kebijakan.', 'Merusak fasilitas umum.'], 'A', 'Kebebasan berpendapat dijamin konstitusi dan harus dijalankan dengan menghormati hak orang lain serta hukum.', 'Pasal 28E ayat (3) dan Pasal 28J UUD NRI 1945'),
            $this->question('politik_bernegara', 2, 'Penyelenggara pemilu harus bersifat nasional, tetap, dan mandiri agar...', ['Penyelenggaraan pemilu berlangsung konsisten dan tidak dikendalikan pihak berkepentingan.', 'Hasil suara dapat ditentukan sebelum pemungutan suara.', 'Peserta pemilu bebas dari semua aturan.', 'Pemilih tidak dapat memantau proses pemilu.'], 'A', 'Pasal 22E ayat (5) UUD NRI 1945 mengatur penyelenggara pemilu yang bersifat nasional, tetap, dan mandiri.', 'Pasal 22E ayat (5) UUD NRI 1945'),
            $this->question('politik_bernegara', 2, 'Dalam demokrasi perwakilan, anggota DPR memperoleh mandat melalui...', ['Pemilihan umum.', 'Penunjukan oleh badan pemeriksa keuangan.', 'Putusan pengadilan untuk setiap kursi.', 'Pengundian oleh pemerintah.'], 'A', 'Pasal 19 ayat (1) UUD NRI 1945 menyatakan anggota DPR dipilih melalui pemilihan umum.', 'Pasal 19 ayat (1) UUD NRI 1945'),
            $this->question('politik_bernegara', 2, 'Perbedaan pendapat dalam masyarakat demokratis sebaiknya diselesaikan melalui...', ['Dialog, musyawarah, dan proses hukum yang berlaku.', 'Pemaksaan kehendak kelompok terbesar.', 'Pelarangan semua kritik.', 'Pengabaian hak pihak yang berbeda.'], 'A', 'Musyawarah dan proses hukum memungkinkan perbedaan dikelola dengan menghormati hak warga serta aturan yang berlaku.', 'Pancasila sila keempat; Pasal 28E dan Pasal 28J UUD NRI 1945'),
        ];
    }

    /**
     * @param  array{0: string, 1: string, 2: string, 3: string}  $choices
     * @return array<string, mixed>
     */
    private function question(
        string $domain,
        int $level,
        string $prompt,
        array $choices,
        string $correct,
        string $explanation,
        string $source,
    ): array {
        $correctIndex = ord($correct) - ord('A');
        $targetIndex = crc32($prompt) % 4;
        [$choices[$correctIndex], $choices[$targetIndex]] = [$choices[$targetIndex], $choices[$correctIndex]];

        return [
            'domain' => $domain,
            'mastery_level' => $level,
            'prompt' => $prompt,
            'options' => $this->options(...$choices),
            'correct_option' => chr(ord('A') + $targetIndex),
            'explanation' => $explanation,
            'source_citation' => $source,
            'source_url' => null,
            'mastery_points' => 5,
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
