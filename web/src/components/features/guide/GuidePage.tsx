import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Panduan Penggunaan | PARTAI",
  description:
    "Panduan membaca data publik, memahami skor reputasi, dan mendaftar menjadi anggota PARTAI.",
};

const steps = [
  {
    number: "01",
    menu: "Belajar",
    title: "Bangun kapasitas dan bagikan gagasan",
    description:
      "Ikuti materi dan Kuis Kewargaan untuk memperdalam pengetahuan kewargaan. Untuk topik yang memerlukan penalaran lebih lanjut, tulis esai sebagai ruang menyampaikan argumen.",
    href: "/belajar",
  },
  {
    number: "02",
    menu: "Juri Warga",
    title: "Tinjau esai bersama",
    description:
      "Baca esai anggota lain dan berikan peer-review yang beralasan. Penilaian antarsesama membantu membangun diskusi dan menjaga kualitas gagasan.",
    href: "/review",
  },
  {
    number: "03",
    menu: "Aksi Lapangan",
    title: "Catat kehadiran dalam aksi",
    description:
      "Pilih kegiatan yang tersedia, lalu klaim kehadiran menggunakan QR di lokasi. Klaim menggunakan pemeriksaan lokasi GPS/geofencing untuk membantu memastikan kehadiran berlangsung di lapangan.",
    href: "/events",
  },
  {
    number: "04",
    menu: "Data Publik",
    title: "Ikuti indikator dan sumber data",
    description:
      "Jelajahi dasbor indikator kesejahteraan dan kebencanaan yang merujuk pada BPS dan BNPB. Periksa keterangan sumber serta tahun data; data di dasbor saat ini merupakan simulasi, bukan data resmi real-time.",
    href: "/data",
  },
  {
    number: "05",
    menu: "Voting",
    title: "Ikut menentukan keputusan",
    description:
      "Buka voting yang sedang aktif, pelajari pertanyaannya, pilih satu opsi, lalu kirim suara. Prinsip tata kelolanya adalah satu anggota, satu suara.",
    href: "/voting",
  },
];

export default function GuideContent() {
  return (
    <main className="flex-1">
      <div className="mx-auto w-full max-w-5xl px-6 py-16 sm:px-8 sm:py-24">
        <header className="max-w-3xl">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
            Panduan penggunaan
          </p>
          <h1 className="mt-5 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
            Panduan untuk semua warga
          </h1>
          <p className="mt-5 text-lg leading-8 text-text-muted">
            Panduan singkat untuk membaca data, memahami cara kerja skor, dan
            mulai bergabung atau menggunakan fitur publik.
          </p>
        </header>

        <div className="mt-12 grid gap-8 lg:grid-cols-[15rem_minmax(0,1fr)]">
          <nav
            aria-label="Daftar isi panduan"
            className="h-fit rounded-2xl border border-background bg-surface p-5 lg:sticky lg:top-24"
          >
            <h2 className="font-extrabold text-text-main">Daftar isi</h2>
            <ol className="mt-3 space-y-2 text-sm">
              <li>
                <a className="font-semibold text-primary-hover underline underline-offset-4" href="#grafik">
                  Membaca grafik
                </a>
              </li>
              <li>
                <a className="font-semibold text-primary-hover underline underline-offset-4" href="#skor">
                  Memahami kedua skor
                </a>
              </li>
              <li>
                <a className="font-semibold text-primary-hover underline underline-offset-4" href="#keanggotaan">
                  Mendaftar anggota
                </a>
              </li>
              <li>
                <a className="font-semibold text-primary-hover underline underline-offset-4" href="#privasi">
                  Privasi data
                </a>
              </li>
              <li>
                <a className="font-semibold text-primary-hover underline underline-offset-4" href="#langkah">
                  Fitur lainnya
                </a>
              </li>
            </ol>
          </nav>

          <div className="min-w-0 space-y-8">
            <section
              aria-labelledby="journey-guide-title"
              className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8"
              id="perjalanan"
            >
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">
                Kitab perjalanan anggota
              </p>
              <h2 className="mt-2 text-2xl font-extrabold text-text-main" id="journey-guide-title">
                Calon Anggota → Anggota Terverifikasi → Pemegang Mandat
              </h2>
              <ol className="mt-5 grid gap-4 md:grid-cols-3">
                {[
                  ["1", "Calon Anggota", "Daftar melalui SSO, lengkapi identitas, lalu ikuti edukasi kewargaan."],
                  ["2", "Anggota Terverifikasi", "Bangun Mastery dan Participation melalui pembelajaran serta kontribusi terverifikasi."],
                  ["3", "Pemegang Mandat", "Ajukan diri ke kursi terbuka bila ambang Mastery, domain, dan partisipasi terpenuhi."],
                ].map(([number, title, description]) => (
                  <li className="rounded-xl bg-primary-light p-5" key={number}>
                    <span className="text-sm font-black text-primary-hover">{number}</span>
                    <h3 className="mt-2 font-extrabold text-text-main">{title}</h3>
                    <p className="mt-2 text-sm leading-6 text-text-muted">{description}</p>
                  </li>
                ))}
              </ol>
            </section>

            <section className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8" id="anti-oligarki">
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">Anti-oligarki</p>
              <h2 className="mt-2 text-2xl font-extrabold text-text-main">Zero Leverage: Keuangan Tidak Membeli Hak</h2>
              <p className="mt-4 leading-7 text-text-muted">
                Mastery Score mengukur penguasaan, Participation Score mencatat kontribusi terverifikasi, dan keuangan partai hanya tunduk pada transparansi serta plafon UU Parpol. Sumbangan individu dibatasi Rp1 miliar/tahun dan badan usaha Rp7,5 miliar/tahun; donasi anonim dilarang. Ledger hash append-only menjaga jejak, tetapi nominal tidak menambah hak suara atau kelayakan kursi.
              </p>
              <Link className="mt-5 inline-flex font-bold text-primary-hover underline underline-offset-4" href="/tata-kelola">Cek kursi dan ambang kapasitas</Link>
            </section>

            <section className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8" id="pengawasan">
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">Pengawasan publik</p>
              <h2 className="mt-2 text-2xl font-extrabold text-text-main">Peta layanan kewargaan</h2>
              <div className="mt-5 grid gap-3 sm:grid-cols-2">
                {[
                  ["/belajar", "Edukasi kewargaan"],
                  ["/info-indonesia", "IKK dan indikator wilayah"],
                  ["/data", "Kawal anggaran dan data"],
                  ["/pengawasan-birokrasi", "Pengawasan birokrasi"],
                  ["/jemput-hak", "Jemput hak"],
                  ["/kontrak-faksi", "Kontrak faksi"],
                  ["/tata-kelola", "Siapa menjawab apa"],
                  ["/tanya-jawab", "Pertanyaan dan FAQ bersitasi"],
                ].map(([href, label]) => (
                  <Link className="rounded-xl border border-background px-4 py-3 font-bold text-primary-hover hover:bg-primary-light" href={href} key={href}>{label} →</Link>
                ))}
              </div>
            </section>
            <section
              aria-labelledby="chart-guide-title"
              className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8"
              id="grafik"
            >
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">
                Data publik
              </p>
              <h2
                className="mt-2 text-2xl font-extrabold text-text-main"
                id="chart-guide-title"
              >
                Cara membaca grafik di Info Indonesia
              </h2>
              <ol className="mt-4 list-inside list-decimal space-y-3 leading-7 text-text-muted">
                <li>
                  Pilih indikator dan wilayah yang ingin dibandingkan; grafik
                  menampilkan perubahan dari waktu ke waktu.
                </li>
                <li>
                  Arahkan kursor atau fokuskan grafik untuk membaca nilai dan
                  periodenya. Perhatikan satuan, misalnya persen.
                </li>
                <li>
                  Bandingkan garis wilayah dengan rata-rata nasional bila
                  keduanya tersedia. Perbedaan skala atau periode dapat
                  memengaruhi perbandingan.
                </li>
                <li>
                  Periksa tahun pembaruan dan sumber BPS pada halaman. Data yang
                  belum tersinkron dapat belum tersedia atau berupa simulasi.
                </li>
              </ol>
              <Link
                className="mt-5 inline-flex font-bold text-primary-hover underline underline-offset-4"
                href="/info-indonesia"
              >
                Buka Info Indonesia
              </Link>
            </section>

            <section
              aria-labelledby="score-guide-title"
              className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8"
              id="skor"
            >
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">
                Reputasi anggota
              </p>
              <h2
                className="mt-2 text-2xl font-extrabold text-text-main"
                id="score-guide-title"
              >
                Mastery Score dan Participation Score
              </h2>
              <div className="mt-5 grid gap-4 sm:grid-cols-2">
                <article className="rounded-xl bg-primary-light p-5">
                  <h3 className="font-extrabold text-text-main">Mastery Score</h3>
                  <p className="mt-2 leading-7 text-text-muted">
                    Mengukur penguasaan materi kewargaan pada lima domain.
                    Skor ini berhubungan dengan tingkat kapasitas dan ambang
                    kelayakan peran tertentu.
                  </p>
                </article>
                <article className="rounded-xl bg-slate-50 p-5">
                  <h3 className="font-extrabold text-text-main">
                    Participation Score
                  </h3>
                  <p className="mt-2 leading-7 text-text-muted">
                    Mencatat kontribusi anggota yang telah diverifikasi, baik
                    kerja lapangan maupun kontribusi non-fisik. Skor ini
                    terpisah dari penguasaan materi dan tidak mengubah hak suara.
                  </p>
                </article>
              </div>
              <div className="mt-6">
                <h3 className="font-extrabold text-text-main">
                  Tangga poin reputasi anggota
                </h3>
                <p className="mt-2 text-sm leading-6 text-text-muted">
                  Level mengikuti total poin reputasi anggota, terpisah dari
                  Mastery Score dan Participation Score.
                </p>
                <ol className="mt-3 grid gap-3 sm:grid-cols-2">
                  {[
                    ["0–99 poin", "Warga Baru"],
                    ["100–499 poin", "Warga Aktif"],
                    ["500–999 poin", "Penggerak"],
                    ["1.000+ poin", "Pengawal Demokrasi"],
                  ].map(([points, level]) => (
                    <li
                      className="flex items-center justify-between gap-4 rounded-xl border border-background bg-white p-4"
                      key={level}
                    >
                      <span className="text-sm font-semibold text-text-muted">
                        {points}
                      </span>
                      <span className="text-right font-extrabold text-text-main">
                        {level}
                      </span>
                    </li>
                  ))}
                </ol>
              </div>
              <p className="mt-4 text-sm leading-6 text-text-muted">
                Singkatnya: Mastery mengukur apa yang dipelajari; Participation
                mencatat kontribusi. Keduanya bukan ukuran nilai seseorang.
              </p>
            </section>

            <section
              aria-labelledby="registration-guide-title"
              className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8"
              id="keanggotaan"
            >
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">
                Bergabung
              </p>
              <h2
                className="mt-2 text-2xl font-extrabold text-text-main"
                id="registration-guide-title"
              >
                Cara mendaftar menjadi anggota
              </h2>
              <ol className="mt-4 list-inside list-decimal space-y-3 leading-7 text-text-muted">
                <li>
                  Buka formulir pendaftaran dan isi nama lengkap, email,
                  password minimal 8 karakter, serta wilayah.
                </li>
                <li>
                  NIK bersifat opsional. Tinjau kembali data sebelum mengirim
                  formulir.
                </li>
                <li>
                  Setelah pendaftaran berhasil, Anda akan diarahkan ke profil
                  anggota. Gunakan email dan password untuk masuk kembali.
                </li>
              </ol>
              <Link
                className="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-primary px-5 py-3 font-bold text-surface transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                href="/daftar"
              >
                Daftar sebagai Anggota
              </Link>
            </section>

            <section
              aria-labelledby="privacy-guide-title"
              className="scroll-mt-24 rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8"
              id="privasi"
            >
              <p className="text-sm font-black uppercase tracking-[0.16em] text-primary-hover">
                Privasi dan keamanan
              </p>
              <h2
                className="mt-2 text-2xl font-extrabold text-text-main"
                id="privacy-guide-title"
              >
                Data pribadi dijaga dengan enkripsi
              </h2>
              <p className="mt-4 leading-7 text-text-muted">
                PARTAI berkomitmen melindungi privasi warga. Data sensitif yang
                diberikan, termasuk NIK, dienkripsi saat disimpan untuk membantu
                menjaga kerahasiaannya. Pengelolaan data pribadi dilakukan
                dengan memperhatikan UU No. 27 Tahun 2022 tentang Pelindungan
                Data Pribadi (UU PDP).
              </p>
            </section>

            <section
              aria-labelledby="platform-guide-title"
              className="scroll-mt-24"
              id="langkah"
            >
              <h2
                className="text-2xl font-extrabold text-text-main"
                id="platform-guide-title"
              >
                Fitur lainnya
              </h2>
              <ol className="mt-5 grid gap-4 md:grid-cols-2">
                {steps.map((step) => (
                  <li
                    className="rounded-2xl border border-background bg-surface p-6 shadow-sm"
                    key={step.number}
                  >
                    <div className="flex items-center gap-3">
                      <span className="inline-flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-light text-sm font-black text-primary-hover">
                        {step.number}
                      </span>
                      <p className="text-sm font-bold uppercase tracking-[0.12em] text-primary-hover">
                        Menu: {step.menu}
                      </p>
                    </div>
                    <h3 className="mt-5 text-xl font-extrabold text-text-main">
                      {step.title}
                    </h3>
                    <p className="mt-3 leading-7 text-text-muted">{step.description}</p>
                    <Link
                      className="mt-5 inline-flex font-bold text-primary-hover underline decoration-primary underline-offset-4 transition hover:text-primary focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
                      href={step.href}
                    >
                      Buka {step.menu}
                      <span aria-hidden="true" className="ml-2">
                        →
                      </span>
                    </Link>
                  </li>
                ))}
              </ol>
            </section>
          </div>
        </div>

        <section className="mt-10 rounded-2xl border border-primary/10 bg-primary-light p-6 sm:p-8">
          <p className="text-sm font-black uppercase tracking-[0.18em] text-primary-hover">
            Akses pengurus
          </p>
          <h2 className="mt-3 text-2xl font-extrabold text-text-main">
            Untuk Pengurus Partai
          </h2>
          <p className="mt-3 max-w-3xl leading-7 text-text-muted">
            Pengurus dapat membuka Panel Admin untuk mengelola kebutuhan
            administrasi partai melalui portal API.
          </p>
          <a
            className="mt-5 inline-flex min-h-12 items-center justify-center rounded-xl bg-primary px-5 font-bold text-surface transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
            href="https://api.akuhadir.my.id/admin"
            rel="noopener noreferrer"
            target="_blank"
          >
            Buka Panel Admin
            <span aria-hidden="true" className="ml-2">
              ↗
            </span>
          </a>
        </section>
      </div>
    </main>
  );
}
