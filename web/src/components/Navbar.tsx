import { Suspense } from "react";
import Link from "next/link";
import NavbarRegistrationCTA from "@/components/NavbarRegistrationCTA";

const links = [
  { href: "/", label: "Beranda" },
  { href: "/belajar", label: "Belajar" },
  { href: "/review", label: "Juri Warga" },
  { href: "/events", label: "Aksi Lapangan" },
  { href: "/manifesto", label: "Manifesto" },
  { href: "/panduan", label: "Panduan" },
];

const groupedLinks = {
  "Data & Info": [
    { href: "/data", label: "Data Publik" },
    { href: "/info", label: "Info Indonesia" },
  ],
  Partisipasi: [
    { href: "/voting", label: "Voting" },
    { href: "/recall", label: "Tata Kelola" },
  ],
};

export default function Navbar() {
  return (
    <header className="sticky top-0 z-50 border-b border-background/80 bg-surface/95 shadow-sm shadow-primary/5 backdrop-blur">
      <nav
        aria-label="Navigasi utama"
        className="mx-auto flex max-w-7xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3 sm:px-6 lg:px-8"
      >
        <Link
          aria-label="PARTAI, Beranda"
          className="w-fit rounded-md text-xl font-black tracking-[0.16em] text-primary transition hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
          href="/"
        >
          PARTAI
        </Link>
        <div className="flex flex-1 flex-wrap items-center gap-2 lg:gap-6">
          {links.map(({ href, label }) => (
            <Link
              className="whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold text-text-muted transition hover:bg-primary-light hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
              href={href}
              key={href}
            >
              {label}
            </Link>
          ))}
          {Object.entries(groupedLinks).map(([label, items]) => (
            <details className="relative" key={label}>
              <summary className="cursor-pointer list-none whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold text-text-muted transition hover:bg-primary-light hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                {label}
                <span aria-hidden="true" className="ml-2">▾</span>
              </summary>
              <div className="absolute left-0 top-full z-10 mt-2 min-w-44 rounded-md border border-background bg-surface p-2 shadow-lg">
                {items.map(({ href, label: itemLabel }) => (
                  <Link
                    className="block whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold text-text-muted transition hover:bg-primary-light hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
                    href={href}
                    key={href}
                  >
                    {itemLabel}
                  </Link>
                ))}
              </div>
            </details>
          ))}
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Suspense fallback={null}>
            <NavbarRegistrationCTA />
          </Suspense>
          <a
            className="whitespace-nowrap rounded-md border border-primary bg-transparent px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
            href="https://api.akuhadir.my.id/admin"
            rel="noopener noreferrer"
            target="_blank"
          >
            Masuk Pengurus
          </a>
        </div>
      </nav>
    </header>
  );
}
