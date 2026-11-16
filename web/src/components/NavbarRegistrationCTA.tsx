"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

export default function NavbarRegistrationCTA() {
  const pathname = usePathname();

  if (pathname === "/daftar") {
    return null;
  }

  return (
    <Link
      className="whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
      href="/daftar"
    >
      Daftar Anggota
    </Link>
  );
}
