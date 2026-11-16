import { Suspense } from "react";
import Link from "next/link";
import NavbarRegistrationCTA from "@/components/NavbarRegistrationCTA";

const links = [
  { href: "/", label: "Beranda" },
  { href: "/belajar", label: "Belajar" },
  { href: "/review", label: "Juri Warga" },
  { href: "/events", label: "Aksi Lapangan" },
  { href: "/data", label: "Data Publik" },
  { href: "/info", label: "Info Indonesia" },
  { href: "/voting", label: "Voting" },
  { href: "/recall", label: "Tata Kelola" },
  { href: "/manifesto", label: "Manifesto" },
  { href: "/panduan", label: "Panduan" },
];

export default function Navbar() {
  return (
    <header className="sticky top-0 z-50 border-b border-background/80 bg-surface/95 shadow-sm shadow-primary/5 backdrop-blur">
      <nav
        aria-label="Navigasi utama"
        className="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-8"
      >
        <Link
          aria-label="PARTAI, Beranda"
          className="w-fit text-xl font-black tracking-[0.16em] text-primary"
          href="/"
        >
          PARTAI
        </Link>
        <div className="-mx-5 flex gap-4 overflow-x-auto px-5 pb-1 sm:mx-0 sm:min-w-0 sm:flex-1 sm:gap-4 sm:overflow-x-auto sm:px-0 sm:pb-0">
          {links.map(({ href, label }) => (
            <Link
              className="shrink-0 rounded-full px-3 py-2 text-sm font-semibold text-text-muted transition hover:bg-primary-light hover:text-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:px-4"
              href={href}
              key={href}
            >
              {label}
            </Link>
          ))}
        </div>
        <Suspense fallback={null}>
          <NavbarRegistrationCTA />
        </Suspense>
        <a
          className="self-end whitespace-nowrap rounded-full border border-primary bg-transparent px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:self-auto"
          href="https://api.akuhadir.my.id/admin"
          rel="noopener noreferrer"
          target="_blank"
        >
          Masuk Pengurus
        </a>
      </nav>
    </header>
  );
}
