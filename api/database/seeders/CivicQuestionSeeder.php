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
