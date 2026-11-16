"use client";

import { Suspense, useState } from "react";
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
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  return (
    <header className="sticky top-0 z-50 border-b border-background/80 bg-surface/95 shadow-sm shadow-primary/5 backdrop-blur">
      <nav
        aria-label="Navigasi utama"
        className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6 lg:px-8"
      >
        <Link
          aria-label="PARTAI, Beranda"
          className="w-fit rounded-md text-xl font-black tracking-[0.16em] text-primary transition hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
          href="/"
        >
          PARTAI
        </Link>
        <button
          aria-controls="mobile-navigation"
          aria-expanded={isMobileMenuOpen}
          aria-label={isMobileMenuOpen ? "Tutup menu navigasi" : "Buka menu navigasi"}
          className="inline-flex size-11 items-center justify-center rounded-md text-text-main transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary md:hidden"
          onClick={() => setIsMobileMenuOpen((open) => !open)}
          type="button"
        >
          <span aria-hidden="true" className="text-2xl leading-none">
            {isMobileMenuOpen ? "×" : "☰"}
          </span>
        </button>
        <div className="hidden flex-1 flex-wrap items-center gap-2 md:flex lg:gap-4">
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
        <div className="hidden flex-wrap items-center gap-2 md:flex">
          <Suspense fallback={null}>
            <NavbarRegistrationCTA onClick={() => setIsMobileMenuOpen(false)} />
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
        {isMobileMenuOpen && (
          <div
            className="w-full border-t border-background pt-3 md:hidden"
            id="mobile-navigation"
          >
            <div className="flex flex-col gap-1">
              {links.map(({ href, label }) => (
                <Link
                  className="rounded-md px-3 py-3 text-sm font-semibold text-text-muted transition hover:bg-primary-light hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                  href={href}
                  key={href}
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  {label}
                </Link>
              ))}
              {Object.entries(groupedLinks).map(([label, items]) => (
                <div className="px-3 py-2" key={label}>
                  <p className="text-xs font-bold uppercase tracking-wide text-text-muted">
                    {label}
                  </p>
                  <div className="mt-1 flex flex-col">
                    {items.map(({ href, label: itemLabel }) => (
                      <Link
                        className="rounded-md py-2 text-sm font-semibold text-text-main hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        href={href}
                        key={href}
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        {itemLabel}
                      </Link>
                    ))}
                  </div>
                </div>
              ))}
            </div>
            <div className="mt-2 flex flex-col gap-2 border-t border-background pt-3">
              <Suspense fallback={null}>
                <NavbarRegistrationCTA onClick={() => setIsMobileMenuOpen(false)} />
              </Suspense>
              <a
                className="rounded-md border border-primary px-4 py-3 text-center text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                href="https://api.akuhadir.my.id/admin"
                rel="noopener noreferrer"
                target="_blank"
              >
                Masuk Pengurus
              </a>
            </div>
          </div>
        )}
      </nav>
    </header>
  );
}
