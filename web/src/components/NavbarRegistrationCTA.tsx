import Link from "next/link";

export default function NavbarRegistrationCTA({
  onClick,
}: {
  onClick?: () => void;
} = {}) {
  return (
    <Link
      className="w-full whitespace-nowrap rounded-md bg-primary px-4 py-3 text-center text-sm font-bold text-white shadow-sm transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50 md:w-auto md:py-2"
      href="/daftar"
      onClick={onClick}
    >
      Gabung Anggota
    </Link>
  );
}
