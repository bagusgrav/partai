import type { Metadata } from "next";
import { Suspense } from "react";
import Link from "next/link";
import Navbar from "@/components/Navbar";
import "./globals.css";

export const metadata: Metadata = {
  title: "Partai | Aku Hadir",
  description:
    "Platform edukasi kewargaan, pengawasan publik, dan tata kelola partai politik terbuka.",
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="id" className="h-full antialiased">
      <body className="min-h-full flex flex-col">
        <Suspense
          fallback={
            <header className="sticky top-0 z-50 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm sm:px-6 lg:px-8">
              <nav
                aria-label="Navigasi utama"
                className="mx-auto flex max-w-7xl items-center justify-between"
              >
                <Link
                  aria-label="PARTAI, Beranda"
                  className="text-lg font-black tracking-[0.16em] text-slate-950 sm:text-xl"
                  href="/"
                >
                  PARTAI
                </Link>
              </nav>
            </header>
          }
        >
          <Navbar />
        </Suspense>
        {children}
      </body>
    </html>
  );
}
