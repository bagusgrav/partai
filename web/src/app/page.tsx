import Link from "next/link";

const steps = [
  {
    number: "01",
    title: "Belajar Harian",
    description: "Selesaikan kuis untuk membangun kapasitas dan pemahaman kewargaan.",
    href: "/belajar",
    linkLabel: "Mulai belajar",
  },
  {
    number: "02",
    title: "Aksi Lapangan",
    description: "Klaim kehadiran Anda di lokasi aksi damai dan bergerak bersama.",
    href: "/events",
    linkLabel: "Lihat aksi",
  },
  {
    number: "03",
    title: "Validasi Silang",
    description: "Awasi dan validasi klaim anggota lain dari wilayah berbeda.",
    href: "/review",
    linkLabel: "Buka Juri Warga",
  },
];

const features = [
  {
    number: "01",
    title: "Belajar",
    description: "Asah wawasan kewargaan melalui kuis dan latihan esai.",
    href: "/belajar",
    linkLabel: "Jelajahi materi",
  },
  {
    number: "02",
    title: "Aksi",
    description: "Temukan kegiatan dan catat partisipasi di lapangan.",
    href: "/events",
    linkLabel: "Lihat kegiatan",
  },
  {
    number: "03",
    title: "Juri Warga",
    description: "Bantu menjaga kepercayaan melalui peninjauan bersama.",
    href: "/review",
    linkLabel: "Ikut meninjau",
  },
  {
    number: "04",
    title: "Pengawasan Data",
    description: "Ruang untuk transparansi dan pengawasan data publik.",
  },
];

export default function Home() {
  return (
    <main className="flex-1">
      <section className="relative isolate overflow-hidden bg-slate-950 text-white">
        <div
          aria-hidden="true"
          className="absolute -right-32 -top-40 -z-10 size-[30rem] rounded-full bg-emerald-500/20 blur-3xl"
        />
        <div
          aria-hidden="true"
          className="absolute -bottom-48 -left-32 -z-10 size-[28rem] rounded-full bg-teal-400/10 blur-3xl"
        />
        <div className="mx-auto grid max-w-7xl gap-12 px-6 py-20 sm:px-8 sm:py-28 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:py-32">
          <div className="max-w-3xl">
            <p className="inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-emerald-300/10 px-4 py-2 text-sm font-semibold text-emerald-200">
              <span aria-hidden="true" className="size-2 rounded-full bg-emerald-400" />
              Ruang belajar dan pengawasan warga
            </p>
            <h1 className="mt-7 text-5xl font-black tracking-tight sm:text-6xl lg:text-7xl">
              Aku <span className="text-emerald-400">Hadir.</span>
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-300 sm:text-xl">
              Bersama membangun kapasitas warga dan mengawasi kepentingan publik
              melalui edukasi kewargaan yang terbuka, aksi nyata, dan validasi
              bersama.
            </p>
            <div className="mt-9 flex flex-col gap-3 sm:flex-row">
              <Link
                className="inline-flex min-h-14 items-center justify-center rounded-xl bg-emerald-500 px-7 text-base font-extrabold text-slate-950 shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                href="/belajar"
              >
                Mulai Belajar Sekarang
                <span aria-hidden="true" className="ml-2">→</span>
              </Link>
              <Link
                className="inline-flex min-h-14 items-center justify-center rounded-xl border border-white/20 px-7 text-base font-bold text-white transition hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                href="/events"
              >
                Jelajahi Aksi Lapangan
              </Link>
            </div>
          </div>

          <aside className="rounded-3xl border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/20 backdrop-blur sm:p-8">
            <p className="text-sm font-bold uppercase tracking-[0.2em] text-emerald-300">
              Bertumbuh bersama
            </p>
            <h2 className="mt-4 text-2xl font-extrabold leading-snug sm:text-3xl">
              Warga berdaya. Ruang publik terjaga.
            </h2>
            <p className="mt-4 leading-7 text-slate-300">
              Belajar, bergerak, dan saling mengawasi—dalam satu perjalanan
              partisipasi yang transparan.
            </p>
            <div className="mt-8 grid grid-cols-3 gap-3 border-t border-white/10 pt-6">
              {[
                { label: "Belajar", icon: "01" },
                { label: "Bergerak", icon: "02" },
                { label: "Mengawasi", icon: "03" },
              ].map(({ label, icon }) => (
                <div className="text-center" key={label}>
                  <span className="mx-auto flex size-10 items-center justify-center rounded-xl bg-emerald-400/15 text-sm font-black text-emerald-300">
                    {icon}
                  </span>
                  <p className="mt-2 text-xs font-semibold text-slate-300 sm:text-sm">
                    {label}
                  </p>
                </div>
              ))}
            </div>
          </aside>
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-6 py-20 sm:px-8 sm:py-24">
        <div className="max-w-2xl">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-emerald-700">
            Mulai dari sini
          </p>
          <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Tiga langkah untuk ikut berperan
          </h2>
          <p className="mt-4 text-lg leading-7 text-slate-600">
            Perjalanan sederhana untuk membangun kapasitas dan menjaga ruang
            publik bersama.
          </p>
        </div>
        <div className="mt-10 grid gap-5 md:grid-cols-3">
          {steps.map((step) => (
            <article
              className="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-950/5 sm:p-7"
              key={step.number}
            >
              <span className="inline-flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-black text-emerald-800">
                {step.number}
              </span>
              <h3 className="mt-5 text-xl font-extrabold text-slate-950">{step.title}</h3>
              <p className="mt-3 min-h-14 leading-6 text-slate-600">{step.description}</p>
              <Link
                className="mt-5 inline-flex items-center font-bold text-emerald-800 transition group-hover:text-emerald-950 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-600"
                href={step.href}
              >
                {step.linkLabel}
                <span aria-hidden="true" className="ml-2">→</span>
              </Link>
            </article>
          ))}
        </div>
      </section>

      <section className="border-y border-slate-200 bg-white">
        <div className="mx-auto max-w-7xl px-6 py-20 sm:px-8 sm:py-24">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p className="text-sm font-black uppercase tracking-[0.2em] text-emerald-700">
                Satu ruang, banyak cara berkontribusi
              </p>
              <h2 className="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
                Kenali fitur PARTAI
              </h2>
            </div>
            <p className="max-w-lg leading-6 text-slate-600">
              Pilih langkah yang sesuai untuk mulai belajar dan terlibat dalam
              kehidupan publik.
            </p>
          </div>
          <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            {features.map((feature) => {
              const content = (
                <>
                  <div className="flex items-start justify-between gap-4">
                    <span className="inline-flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-sm font-black text-emerald-800">
                      {feature.number}
                    </span>
                    {!feature.href && (
                      <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                        Segera hadir
                      </span>
                    )}
                  </div>
                  <h3 className="mt-5 text-lg font-extrabold text-slate-950">
                    {feature.title}
                  </h3>
                  <p className="mt-2 min-h-12 text-sm leading-6 text-slate-600">
                    {feature.description}
                  </p>
                  {feature.href && feature.linkLabel && (
                    <span className="mt-5 inline-flex items-center text-sm font-bold text-emerald-800">
                      {feature.linkLabel}
                      <span aria-hidden="true" className="ml-2">→</span>
                    </span>
                  )}
                </>
              );

              return feature.href ? (
                <Link
                  className="rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-950/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                  href={feature.href}
                  key={feature.number}
                >
                  {content}
                </Link>
              ) : (
                <article
                  className="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                  key={feature.number}
                >
                  {content}
                </article>
              );
            })}
          </div>
        </div>
      </section>

      <footer className="mx-auto flex w-full max-w-7xl flex-col gap-3 px-6 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <p className="font-bold tracking-[0.12em] text-emerald-800">PARTAI</p>
        <p>Edukasi kewargaan dan pengawasan publik, bersama warga.</p>
      </footer>
    </main>
  );
}
