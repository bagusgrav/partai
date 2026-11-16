import Link from "next/link";

const links = [
  { href: "/", label: "Beranda" },
  { href: "/belajar", label: "Belajar" },
  { href: "/review", label: "Juri Warga" },
  { href: "/events", label: "Aksi Lapangan" },
];

export default function Navbar() {
  return (
    <header className="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 shadow-sm shadow-slate-900/[0.03] backdrop-blur">
      <nav
        aria-label="Navigasi utama"
        className="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-8"
      >
        <Link
          aria-label="PARTAI, Beranda"
          className="w-fit text-xl font-black tracking-[0.16em] text-emerald-700"
          href="/"
        >
          PARTAI
        </Link>
        <div className="-mx-5 flex gap-1 overflow-x-auto px-5 pb-1 sm:mx-0 sm:gap-2 sm:overflow-visible sm:px-0 sm:pb-0">
          {links.map(({ href, label }) => (
            <Link
              className="shrink-0 rounded-full px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:px-4"
              href={href}
              key={href}
            >
              {label}
            </Link>
          ))}
        </div>
      </nav>
    </header>
  );
}
