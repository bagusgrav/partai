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

export default function LandingPage() {
  return (
    <main className="flex-1">
      <section className="relative isolate overflow-hidden bg-primary-light text-text-main">
        <div
          aria-hidden="true"
          className="absolute -right-32 -top-40 -z-10 size-[30rem] rounded-full bg-primary/20 blur-3xl"
        />
        <div
          aria-hidden="true"
          className="absolute -bottom-48 -left-32 -z-10 size-[28rem] rounded-full bg-primary/10 blur-3xl"
        />
        <div className="mx-auto grid max-w-7xl gap-12 px-6 py-20 sm:px-8 sm:py-28 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:py-32">
          <div className="max-w-3xl">
            <p className="inline-flex items-center gap-2 rounded-full border border-primary/25 bg-surface px-4 py-2 text-sm font-semibold text-primary-hover">
              <span aria-hidden="true" className="size-2 rounded-full bg-primary" />
              Ruang belajar dan pengawasan warga
            </p>
            <h1 className="mt-7 text-3xl font-black tracking-tight sm:text-5xl">
              Aku <span className="text-primary">Hadir.</span>
            </h1>
            <p className="mt-6 max-w-2xl text-lg leading-8 text-text-muted sm:text-xl">
              Bersama membangun kapasitas warga dan mengawasi kepentingan publik
              melalui edukasi kewargaan yang terbuka, aksi nyata, dan validasi
              bersama.
            </p>
            <div className="mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
              <Link
                className="inline-flex min-h-14 w-full items-center justify-center rounded-md bg-primary px-4 py-4 text-base font-extrabold text-surface shadow-lg shadow-primary/20 transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50 sm:w-auto"
                href="/belajar"
              >
                Mulai Belajar Sekarang
                <span aria-hidden="true" className="ml-2">→</span>
              </Link>
              <Link
                className="inline-flex min-h-14 w-full items-center justify-center rounded-md border border-primary/20 px-4 py-4 text-base font-bold text-primary-hover transition hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50 sm:w-auto"
                href="/events"
              >
                Jelajahi Aksi Lapangan
              </Link>
              <Link
                className="inline-flex min-h-14 w-full items-center justify-center rounded-md border border-primary/20 px-4 py-4 text-base font-bold text-primary-hover transition hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50 sm:w-auto"
                href="/manifesto"
              >
                Baca Manifesto
              </Link>
            </div>
          </div>

          <aside className="rounded-3xl border border-primary/10 bg-surface p-6 shadow-2xl shadow-primary/10 backdrop-blur sm:p-8">
            <p className="text-sm font-bold uppercase tracking-[0.2em] text-primary">
              Bertumbuh bersama
            </p>
            <h2 className="mt-4 text-2xl font-extrabold leading-snug sm:text-3xl">
              Warga berdaya. Ruang publik terjaga.
            </h2>
            <p className="mt-4 leading-7 text-text-muted">
              Belajar, bergerak, dan saling mengawasi—dalam satu perjalanan
              partisipasi yang transparan.
            </p>
            <div className="mt-8 grid grid-cols-3 gap-3 border-t border-background pt-6">
              {[
                { label: "Belajar", icon: "01" },
                { label: "Bergerak", icon: "02" },
                { label: "Mengawasi", icon: "03" },
              ].map(({ label, icon }) => (
                <div className="text-center" key={label}>
                  <span className="mx-auto flex size-10 items-center justify-center rounded-xl bg-primary-light text-sm font-black text-primary">
                    {icon}
                  </span>
                  <p className="mt-2 text-xs font-semibold text-text-muted sm:text-sm">
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
          <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
            Mulai dari sini
          </p>
          <h2 className="mt-3 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
            Tiga langkah untuk ikut berperan
          </h2>
          <p className="mt-4 text-lg leading-7 text-text-muted">
            Perjalanan sederhana untuk membangun kapasitas dan menjaga ruang
            publik bersama.
          </p>
        </div>
        <div className="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
          {steps.map((step) => (
            <article
              className="group rounded-2xl border border-background bg-surface p-6 shadow-sm transition hover:-translate-y-1 hover:border-primary-light hover:shadow-lg hover:shadow-primary/5 sm:p-7"
              key={step.number}
            >
              <span className="inline-flex size-12 items-center justify-center rounded-2xl bg-primary-light text-sm font-black text-primary-hover">
                {step.number}
              </span>
              <h3 className="mt-5 text-xl font-extrabold text-text-main">{step.title}</h3>
              <p className="mt-3 min-h-14 leading-6 text-text-muted">{step.description}</p>
              <Link
                className="mt-5 inline-flex items-center font-bold text-primary-hover transition group-hover:text-primary focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary"
                href={step.href}
              >
                {step.linkLabel}
                <span aria-hidden="true" className="ml-2">→</span>
              </Link>
            </article>
          ))}
        </div>
      </section>

      <section className="border-y border-background bg-surface">
        <div className="mx-auto max-w-7xl px-6 py-20 sm:px-8 sm:py-24">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
                Satu ruang, banyak cara berkontribusi
              </p>
              <h2 className="mt-3 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
                Kenali fitur PARTAI
              </h2>
            </div>
            <p className="max-w-lg leading-6 text-text-muted">
              Pilih langkah yang sesuai untuk mulai belajar dan terlibat dalam
              kehidupan publik.
            </p>
          </div>
          <div className="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
            {features.map((feature) => {
              const content = (
                <>
                  <div className="flex items-start justify-between gap-4">
                    <span className="inline-flex size-11 items-center justify-center rounded-xl bg-primary-light text-sm font-black text-primary-hover">
                      {feature.number}
                    </span>
                    {!feature.href && (
                      <span className="rounded-full bg-background px-3 py-1 text-xs font-bold text-text-muted">
                        Segera hadir
                      </span>
                    )}
                  </div>
                  <h3 className="mt-5 text-lg font-extrabold text-text-main">
                    {feature.title}
                  </h3>
                  <p className="mt-2 min-h-12 text-sm leading-6 text-text-muted">
                    {feature.description}
                  </p>
                  {feature.href && feature.linkLabel && (
                    <span className="mt-5 inline-flex items-center text-sm font-bold text-primary-hover">
                      {feature.linkLabel}
                      <span aria-hidden="true" className="ml-2">→</span>
                    </span>
                  )}
                </>
              );

              return feature.href ? (
                <Link
                  className="rounded-2xl border border-background bg-surface p-5 transition hover:-translate-y-1 hover:border-primary-light hover:shadow-lg hover:shadow-primary/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                  href={feature.href}
                  key={feature.number}
                >
                  {content}
                </Link>
              ) : (
                <article
                  className="rounded-2xl border border-background bg-background p-5"
                  key={feature.number}
                >
                  {content}
                </article>
              );
            })}
          </div>
        </div>
      </section>

      <footer className="mx-auto flex w-full max-w-7xl flex-col gap-3 px-6 py-8 text-sm text-text-muted sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <p className="font-bold tracking-[0.12em] text-primary-hover">PARTAI</p>
        <p>Edukasi kewargaan dan pengawasan publik, bersama warga.</p>
      </footer>
    </main>
  );
}
