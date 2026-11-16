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
      className="self-end whitespace-nowrap rounded-full bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary sm:self-auto"
      href="/daftar"
    >
      Daftar Anggota
    </Link>
  );
}
