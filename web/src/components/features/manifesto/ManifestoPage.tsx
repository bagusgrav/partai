import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Manifesto | PARTAI",
  description: "Mengapa PARTAI hadir untuk membangun politik yang terbuka.",
};

const commitments = [
  {
    number: "01",
    title: "Hadir dalam Kapasitas",
    description:
      "Belajar melalui Modul Belajar Interaktif untuk membangun pengetahuan dan kemampuan berpikir kritis.",
  },
  {
    number: "02",
    title: "Hadir di Lapangan",
    description:
      "Ikut dalam aksi nyata dengan kehadiran yang dapat diklaim dan diukur.",
  },
  {
    number: "03",
    title: "Hadir dalam Pengawasan",
    description:
      "Menggunakan Open Data agar kondisi publik dapat dipahami dan diawasi bersama.",
  },
  {
    number: "04",
    title: "Hadir dalam Demokrasi",
    description:
      "Berpartisipasi dalam voting internal untuk ikut menentukan arah bersama.",
  },
];

export default function ManifestoContent() {
  return (
    <main className="flex-1">
      <article className="mx-auto w-full max-w-4xl px-6 py-16 sm:px-8 sm:py-24">
        <header className="border-b border-background pb-10 sm:pb-14">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
            Manifesto PARTAI
          </p>
          <h1 className="mt-6 text-3xl font-black leading-tight tracking-tight text-text-main sm:text-4xl lg:text-5xl">
            Politik Tidak Seharusnya Begitu Kotor. Saatnya Anda &apos;Aku Hadir.&apos;
          </h1>
          <p className="mt-8 text-xl leading-9 text-text-muted">
            Selama puluhan tahun, kita diajari bahwa politik hanyalah soal
            memilih wajah setiap 5 tahun sekali...
          </p>
        </header>

        <div className="space-y-12 pt-10 pb-4 sm:space-y-16 sm:pt-14 sm:pb-6">
          <section>
            <h2 className="text-2xl font-extrabold tracking-tight text-text-main sm:text-3xl">
              Mengapa Partai Politik Sering Mengecewakan?
            </h2>
            <p className="mt-5 text-lg leading-8 text-text-muted">
              Karena mereka dibangun di atas uang, popularitas, dan loyalitas
              buta. Bukan di atas kapasitas intelektual...
            </p>
          </section>

          <section>
            <h2 className="text-2xl font-extrabold tracking-tight text-text-main sm:text-3xl">
              Solusi: AkuHadir.my.id
            </h2>
            <p className="mt-5 text-lg leading-8 text-text-muted">
              Partai politik terbuka (Open-Source Party) pertama di Indonesia
              yang digerakkan oleh Data, Edukasi, dan Teknologi.
            </p>
          </section>
        </div>

        <section aria-label="Empat cara untuk hadir">
          <div className="grid gap-4 sm:grid-cols-2">
            {commitments.map((commitment) => (
              <article
                className="rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-7"
                key={commitment.number}
              >
                <span className="inline-flex size-11 items-center justify-center rounded-xl bg-primary-light text-sm font-black text-primary-hover">
                  {commitment.number}
                </span>
                <h3 className="mt-5 text-xl font-extrabold text-text-main">
                  {commitment.title}
                </h3>
                <p className="mt-3 leading-7 text-text-muted">
                  {commitment.description}
                </p>
              </article>
            ))}
          </div>
        </section>

        <footer className="mt-12 border-t border-background pt-10 sm:mt-16 sm:pt-14">
          <p className="text-xl font-semibold leading-9 text-text-main sm:text-2xl sm:leading-10">
            Politik bukan milik mereka yang punya uang. Politik adalah milik
            mereka yang mau berpikir dan peduli. Buka akuhadir.my.id. Belajar.
            Bergerak. Awasi.
          </p>
          <Link
            className="mt-8 inline-flex w-full items-center justify-center rounded-md bg-primary px-8 py-4 text-base font-bold text-white shadow-sm transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 sm:w-auto sm:text-lg"
            href="/daftar"
          >
            Gabung Anggota Sekarang
          </Link>
        </footer>
      </article>
    </main>
  );
}
