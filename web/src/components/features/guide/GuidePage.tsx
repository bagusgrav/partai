import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Panduan Penggunaan | PARTAI",
  description: "Panduan lima langkah menggunakan platform PARTAI.",
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
            Lima langkah untuk ikut hadir
          </h1>
          <p className="mt-5 text-lg leading-8 text-text-muted">
            Kenali fitur-fitur PARTAI dan mulai berpartisipasi dalam belajar,
            aksi, pengawasan, serta tata kelola.
          </p>
        </header>

        <ol className="mt-12 grid gap-5 md:grid-cols-2">
          {steps.map((step) => (
            <li
              className="rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-7"
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
              <h2 className="mt-5 text-xl font-extrabold text-text-main">
                {step.title}
              </h2>
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
