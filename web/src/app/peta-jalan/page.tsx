import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Peta Jalan Menuju Legalitas | PARTAI",
  description:
    "Tahapan terbuka PARTAI dari fondasi digital menuju partai politik resmi dan peserta Pemilu.",
};

const phases = [
  {
    number: "01",
    icon: "✅",
    status: "Selesai",
    period: "2026",
    title: "Fondasi Digital",
    description:
      "Membangun ruang partisipasi dan infrastruktur demokrasi digital yang bisa digunakan warga.",
    milestones: [
      "Membangun infrastruktur demokrasi digital melalui aplikasi ini.",
      "Memulai kaderisasi, verifikasi anggota, dan pembentukan database warga.",
      "Mengumpulkan minimal 10.000 pendaftar awal sebagai tolok ukur dukungan publik.",
    ],
  },
  {
    number: "02",
    icon: "🚧",
    status: "Target",
    period: "2027",
    title: "Konsolidasi & Legalitas Hukum",
    description:
      "Menyiapkan organisasi dan dokumen yang diperlukan untuk pendirian badan hukum partai politik.",
    milestones: [
      "Mendirikan badan hukum partai politik melalui akta notaris.",
      "Mendaftarkan partai ke Kementerian Hukum dan HAM (Kemenkumham).",
      "Menyusun AD/ART dan membentuk kepengurusan nasional (DPP).",
    ],
  },
  {
    number: "03",
    icon: "⏳",
    status: "Target",
    period: "2027–2028",
    title: "Verifikasi Faktual KPU",
    description:
      "Memperluas organisasi hingga tingkat daerah dan memenuhi persyaratan verifikasi partai politik.",
    milestones: [
      "Membentuk kepengurusan dan membuka kantor di seluruh provinsi di Indonesia.",
      "Membuka kantor cabang di minimal 75% kabupaten/kota di Indonesia.",
      "Memenuhi persyaratan keanggotaan dan Kartu Tanda Anggota (KTA) sesuai ketentuan KPU.",
    ],
  },
  {
    number: "04",
    icon: "🎯",
    status: "Target",
    period: "2029",
    title: "Kontestasi Pemilu",
    description:
      "Membawa kader hasil proses terbuka ke pemilihan umum dan mengawal kebijakan publik.",
    milestones: [
      "Mendaftar sebagai peserta Pemilu ke KPU RI.",
      "Mendeklarasikan calon legislatif dari kader terbaik yang tumbuh melalui sistem reputasi, bukan kader instan atau dinasti.",
      "Bertarung dalam Pemilu dan meraih kursi legislatif untuk mengawal kebijakan publik.",
    ],
  },
];

export default function RoadmapPage() {
  return (
    <main className="flex-1">
      <section className="border-b border-primary/10 bg-primary-light">
        <div className="mx-auto max-w-7xl px-6 py-16 sm:px-8 sm:py-20">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
            Komitmen dan tahapan
          </p>
          <h1 className="mt-4 max-w-3xl text-3xl font-black tracking-tight text-text-main sm:text-5xl">
            Peta Jalan Menuju Legalitas
          </h1>
          <p className="mt-6 max-w-3xl text-lg leading-8 text-text-muted">
            Dari fondasi digital menuju partai politik yang diakui secara
            resmi. Setiap tahap disusun terbuka agar warga dapat melihat arah,
            target, dan proses yang sedang kami bangun.
          </p>
        </div>
      </section>

      <section
        aria-label="Empat fase peta jalan"
        className="mx-auto w-full max-w-5xl px-6 py-16 sm:px-8 sm:py-20"
      >
        <ol className="ms-4 space-y-10 border-s-2 border-primary/15 sm:ms-6 sm:space-y-12">
          {phases.map((phase) => (
            <li className="relative ps-7 sm:ps-10" key={phase.number}>
              <span
                aria-hidden="true"
                className="absolute -start-[1.05rem] top-0 flex size-8 items-center justify-center rounded-full border-4 border-background bg-surface text-lg shadow-sm sm:size-9 sm:-start-[1.18rem]"
              >
                {phase.icon}
              </span>
              <article className="rounded-2xl border border-background bg-surface p-6 shadow-sm sm:p-8">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="rounded-full bg-primary-light px-3 py-1 text-xs font-black uppercase tracking-wide text-primary-hover">
                    Fase {phase.number}
                  </span>
                  <span className="rounded-full border border-primary/15 px-3 py-1 text-xs font-bold text-text-muted">
                    {phase.status}
                  </span>
                  <span className="ms-auto text-sm font-bold text-primary-hover">
                    {phase.period}
                  </span>
                </div>
                <h2 className="mt-4 text-2xl font-extrabold tracking-tight text-text-main sm:text-3xl">
                  {phase.title}
                </h2>
                <p className="mt-3 leading-7 text-text-muted">
                  {phase.description}
                </p>
                <ul className="mt-6 space-y-3">
                  {phase.milestones.map((milestone) => (
                    <li
                      className="flex gap-3 leading-6 text-text-main"
                      key={milestone}
                    >
                      <span
                        aria-hidden="true"
                        className="mt-2 size-1.5 shrink-0 rounded-full bg-primary"
                      />
                      <span>{milestone}</span>
                    </li>
                  ))}
                </ul>
              </article>
            </li>
          ))}
        </ol>
      </section>
    </main>
  );
}
