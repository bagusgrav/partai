"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import {
  Award,
  BarChart3,
  BookOpen,
  CalendarDays,
  ClipboardList,
  Database,
  FileCheck,
  FileText,
  HandHeart,
  Home,
  MessageCircle,
  Map,
  Menu,
  MessageCircleQuestion,
  Network,
  Scale,
  ShieldCheck,
  Vote,
  X,
  type LucideIcon,
} from "lucide-react";
import {
  AUTH_STATE_CHANGED_EVENT,
  getCurrentUser,
  getNotifications,
  logoutMember,
  NOTIFICATIONS_UPDATED_EVENT,
} from "@/lib/api";
import type { NotificationReadResponse } from "@/lib/api/notifications";
import type { RegisteredUser } from "@/types";

const navigationGroups: {
  title: string;
  links: {
    href: string;
    label: string;
    description: string;
    icon: LucideIcon;
  }[];
}[] = [
  {
    title: "Navigasi Utama",
    links: [
      {
        href: "/",
        label: "Beranda",
        description: "Ringkasan gerakan & kabar terbaru",
        icon: Home,
      },
      {
        href: "/belajar",
        label: "Belajar",
        description: "Kurikulum & uji kapasitas warga",
        icon: BookOpen,
      },
      {
        href: "/juri-warga",
        label: "Juri Warga",
        description: "Panel pengawasan & etik acak",
        icon: Scale,
      },
      {
        href: "/events",
        label: "Gerak Warga",
        description: "Agenda aksi & kegiatan lapangan",
        icon: CalendarDays,
      },
      {
        href: "/komunitas",
        label: "Komunitas",
        description: "Berbagi gagasan dalam feed teks",
        icon: MessageCircle,
      },
      {
        href: "/manifesto",
        label: "Manifesto",
        description: "Prinsip dasar & arah perjuangan",
        icon: FileText,
      },
      {
        href: "/peta-jalan",
        label: "Peta Jalan",
        description: "Tahapan legalitas & pembangunan",
        icon: Map,
      },
      {
        href: "/data",
        label: "Data Publik",
        description: "Anggaran, audit, & log terbuka",
        icon: Database,
      },
      {
        href: "/pengawasan-birokrasi",
        label: "Pengawasan Birokrasi",
        description: "Rapor instansi & lapor maladministrasi",
        icon: ShieldCheck,
      },
      {
        href: "/jemput-hak",
        label: "Jemput Hak",
        description: "Cek status bansos & hak warga",
        icon: HandHeart,
      },
      {
        href: "/kontrak-faksi",
        label: "Kontrak Faksi",
        description: "Komitmen mengikat wakil rakyat",
        icon: FileCheck,
      },
      {
        href: "/reputasi",
        label: "Skor Reputasi",
        description: "Kapasitas intelektual & kontribusi",
        icon: Award,
      },
      {
        href: "/info-indonesia",
        label: "Info Indonesia",
        description: "Data sosial-ekonomi & kerentanan",
        icon: BarChart3,
      },
    ],
  },
  {
    title: "Bantuan & Literasi",
    links: [
      {
        href: "/panduan",
        label: "Panduan",
        description: "Memahami data, skor, dan cara bergabung",
        icon: ClipboardList,
      },
      {
        href: "/tanya-jawab",
        label: "Tanya Jawab",
        description: "FAQ & kirim pertanyaan publik",
        icon: MessageCircleQuestion,
      },
      {
        href: "/glosarium",
        label: "Glosarium",
        description: "Arti istilah data dan tata kelola",
        icon: BookOpen,
      },
    ],
  },
  {
    title: "Partisipasi & Layanan",
    links: [
      {
        href: "/voting",
        label: "Voting",
        description: "Pengambilan keputusan demokratis",
        icon: Vote,
      },
      {
        href: "/tata-kelola",
        label: "Tata Kelola",
        description: "Struktur, sortisi, & konstitusi kode",
        icon: Network,
      },
      {
        href: "/ad-art",
        label: "AD/ART (Draf)",
        description: "Draf konstitusi untuk dimusyawarahkan",
        icon: FileText,
      },
    ],
  },
];

function NotificationBellLink({
  unreadCount,
  error,
}: {
  unreadCount: number;
  error: string | null;
}) {
  return (
    <Link
      aria-label={`Notifikasi${unreadCount > 0 ? `, ${unreadCount} belum dibaca` : ""}`}
      className="relative inline-flex size-10 items-center justify-center rounded-md text-slate-700 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800"
      href="/notifikasi"
      title={error ?? "Notifikasi"}
    >
      <svg
        aria-hidden="true"
        className="size-5"
        fill="none"
        stroke="currentColor"
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeWidth="1.8"
        viewBox="0 0 24 24"
      >
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
        <path d="M10 21h4" />
      </svg>
      {unreadCount > 0 && (
        <span className="absolute right-0 top-0 min-w-5 rounded-full bg-red-700 px-1 text-center text-xs font-bold leading-5 text-white">
          {unreadCount > 99 ? "99+" : unreadCount}
        </span>
      )}
    </Link>
  );
}

function NavigationLink({
  href,
  label,
  description,
  icon: Icon,
  pathname,
  onNavigate,
}: {
  href: string;
  label: string;
  description: string;
  icon: LucideIcon;
  pathname: string;
  onNavigate: () => void;
}) {
  const isActive =
    href === "/"
      ? pathname === "/"
      : pathname === href || pathname.startsWith(`${href}/`);

  return (
    <Link
      aria-current={isActive ? "page" : undefined}
      className={`relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 ${
        isActive
          ? "bg-slate-100 font-semibold text-slate-950 before:absolute before:inset-y-2 before:left-0 before:w-0.5 before:rounded-full before:bg-slate-950"
          : "text-slate-700 hover:bg-slate-50 hover:text-slate-950"
      }`}
      href={href}
      onClick={onNavigate}
    >
      <Icon aria-hidden="true" className="size-[18px] shrink-0" strokeWidth={1.75} />
      <span className="min-w-0">
        <span className="block">{label}</span>
        <span className="hidden text-xs font-normal text-slate-500 md:block">
          {description}
        </span>
      </span>
    </Link>
  );
}

export default function Navbar() {
  const pathname = usePathname();
  const router = useRouter();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const previousPathname = useRef(pathname);
  const [currentUser, setCurrentUser] = useState<RegisteredUser | null>(null);
  const [authResolved, setAuthResolved] = useState(false);
  const [profileError, setProfileError] = useState<string | null>(null);
  const [logoutError, setLogoutError] = useState<string | null>(null);
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const [notificationError, setNotificationError] = useState<string | null>(null);
  const isAuthenticated =
    currentUser !== null && currentUser.role !== "guest";

  useEffect(() => {
    if (previousPathname.current !== pathname) {
      previousPathname.current = pathname;
      setIsMenuOpen(false);
    }
  }, [pathname]);

  useEffect(() => {
    if (!isMenuOpen) {
      return;
    }

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsMenuOpen(false);
      }
    };

    document.addEventListener("keydown", handleKeyDown);
    return () => document.removeEventListener("keydown", handleKeyDown);
  }, [isMenuOpen]);

  useEffect(() => {
    let active = true;
    let requestNumber = 0;

    const loadCurrentUser = () => {
      const currentRequest = ++requestNumber;
      setAuthResolved(false);

      getCurrentUser()
        .then((user) => {
          if (active && currentRequest === requestNumber) {
            setCurrentUser(user);
            setProfileError(null);
          }
        })
        .catch((loadError: unknown) => {
          if (active && currentRequest === requestNumber) {
            setCurrentUser(null);
            setProfileError(
              loadError instanceof Error
                ? loadError.message
                : "Sesi pengguna tidak dapat dimuat.",
            );
          }
        })
        .finally(() => {
          if (active && currentRequest === requestNumber) {
            setAuthResolved(true);
          }
        });
    };

    loadCurrentUser();
    window.addEventListener(AUTH_STATE_CHANGED_EVENT, loadCurrentUser);
    window.addEventListener("storage", loadCurrentUser);

    return () => {
      active = false;
      window.removeEventListener(AUTH_STATE_CHANGED_EVENT, loadCurrentUser);
      window.removeEventListener("storage", loadCurrentUser);
    };
  }, []);

  async function handleLogout(): Promise<void> {
    setIsLoggingOut(true);
    setLogoutError(null);

    try {
      await logoutMember();
      router.push("/");
      router.refresh();
    } catch (logoutFailure: unknown) {
      setLogoutError(
        logoutFailure instanceof Error
          ? logoutFailure.message
          : "Keluar gagal. Sesi lokal telah dihapus.",
      );
    } finally {
      setIsLoggingOut(false);
    }
  }

  useEffect(() => {
    let active = true;

    const loadUnreadCount = (event?: Event) => {
      if (event instanceof CustomEvent) {
        const detail = event.detail as NotificationReadResponse | undefined;
        if (typeof detail?.unread_count === "number") {
          if (active) {
            setUnreadCount(detail.unread_count);
            setNotificationError(null);
          }
          return;
        }
      }

      getNotifications()
        .then((result) => {
          if (active) {
            setUnreadCount(result.unread_count);
            setNotificationError(null);
          }
        })
        .catch((loadError: unknown) => {
          if (active) {
            setNotificationError(
              loadError instanceof Error
                ? loadError.message
                : "Notifikasi tidak dapat dimuat.",
            );
          }
        });
    };

    loadUnreadCount();
    window.addEventListener(NOTIFICATIONS_UPDATED_EVENT, loadUnreadCount);

    return () => {
      active = false;
      window.removeEventListener(NOTIFICATIONS_UPDATED_EVENT, loadUnreadCount);
    };
  }, []);

  return (
    <header className="sticky top-0 z-50 border-b border-slate-200 bg-white/95 shadow-sm shadow-slate-950/5 backdrop-blur">
      <nav
        aria-label="Navigasi utama"
        className="relative mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8"
      >
        <Link
          aria-label="PARTAI, Beranda"
          className="w-fit shrink-0 rounded-md text-lg font-black tracking-[0.16em] text-slate-950 transition hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2 sm:text-xl"
          href="/"
        >
          PARTAI
        </Link>

        <div className="flex shrink-0 items-center gap-2 sm:gap-3">
          {!authResolved ? (
            <span aria-label="Memeriksa sesi" className="size-10" role="status" />
          ) : isAuthenticated ? (
            <>
              <NotificationBellLink
                error={notificationError}
                unreadCount={unreadCount}
              />
              <Link
                className="max-w-24 truncate rounded-md px-2 py-2 text-xs font-semibold text-slate-800 hover:bg-slate-100 sm:max-w-48 sm:text-sm"
                href="/profil"
              >
                <span className="hidden sm:inline">{currentUser.name}</span>
                <span className="sm:hidden">Profil</span>
                <span className="ml-1 hidden rounded-full bg-slate-100 px-2 py-1 text-xs font-bold text-slate-800 md:inline-block">
                  {currentUser.reputation_level}
                </span>
              </Link>
              <button
                className="whitespace-nowrap rounded-md border border-slate-400 px-2.5 py-2 text-xs font-semibold text-slate-800 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-60 sm:px-3 sm:text-sm"
                disabled={isLoggingOut}
                onClick={() => void handleLogout()}
                type="button"
              >
                {isLoggingOut ? "Keluar..." : "Keluar"}
              </button>
            </>
          ) : (
            <>
              <Link
                className="whitespace-nowrap rounded-md bg-slate-950 px-2.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2 sm:px-3 sm:text-sm"
                href="/daftar"
              >
                <span className="sm:hidden">Gabung</span>
                <span className="hidden sm:inline">Gabung Anggota</span>
              </Link>
              <Link
                className="whitespace-nowrap rounded-md border border-slate-400 px-2.5 py-2 text-xs font-semibold text-slate-800 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2 sm:px-3 sm:text-sm"
                href="/masuk"
                title={profileError ?? undefined}
              >
                Masuk
              </Link>
            </>
          )}

          <button
            aria-controls="main-navigation-panel"
            aria-expanded={isMenuOpen}
            aria-label={isMenuOpen ? "Tutup menu navigasi" : "Buka menu navigasi"}
            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-md px-2 text-slate-800 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 sm:px-3"
            onClick={() => setIsMenuOpen((open) => !open)}
            type="button"
          >
            {isMenuOpen ? (
              <X aria-hidden="true" className="size-5" strokeWidth={1.75} />
            ) : (
              <Menu aria-hidden="true" className="size-5" strokeWidth={1.75} />
            )}
            <span className="hidden text-sm font-semibold sm:inline">
              {isMenuOpen ? "Tutup" : "Menu"}
            </span>
          </button>
        </div>

        {isMenuOpen && (
          <button
            aria-label="Tutup menu navigasi"
            className="fixed inset-0 z-40 bg-slate-950/20 backdrop-blur-[1px]"
            onClick={() => setIsMenuOpen(false)}
            tabIndex={-1}
            type="button"
          />
        )}
        <div
          className="absolute inset-x-0 top-full z-50 max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-slate-200 bg-white shadow-xl shadow-slate-950/10"
          hidden={!isMenuOpen}
          id="main-navigation-panel"
        >
          <div className="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-6 sm:px-6 md:grid-cols-3 lg:gap-8 lg:px-8">
            {navigationGroups.map(({ title, links }) => (
              <section aria-label={title} key={title}>
                <h2 className="mb-2 px-3 text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                  {title}
                </h2>
                <div className="grid gap-1">
                  {links.map((link) => (
                    <NavigationLink
                      {...link}
                      key={link.href}
                      onNavigate={() => setIsMenuOpen(false)}
                      pathname={pathname}
                    />
                  ))}
                </div>
              </section>
            ))}

            <div className="flex flex-col gap-2 border-t border-slate-200 pt-4 md:hidden">
              {authResolved && isAuthenticated ? (
                <>
                  <Link
                    className="rounded-md px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-100"
                    href="/notifikasi"
                    onClick={() => setIsMenuOpen(false)}
                    title={notificationError ?? "Notifikasi"}
                  >
                    Notifikasi
                    {unreadCount > 0 && (
                      <span className="ml-2 rounded-full bg-red-700 px-2 py-1 text-xs font-bold text-white">
                        {unreadCount > 99 ? "99+" : unreadCount}
                      </span>
                    )}
                  </Link>
                  <Link
                    className="rounded-md px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-100"
                    href="/profil"
                    onClick={() => setIsMenuOpen(false)}
                  >
                    {currentUser.name}
                  </Link>
                  <button
                    className="rounded-md border border-slate-300 px-3 py-2 text-left text-sm font-semibold text-slate-800 hover:bg-slate-100 disabled:cursor-wait disabled:opacity-60"
                    disabled={isLoggingOut}
                    onClick={() => void handleLogout()}
                    type="button"
                  >
                    {isLoggingOut ? "Keluar..." : "Keluar"}
                  </button>
                </>
              ) : authResolved ? (
                <>
                  <Link
                    className="rounded-md bg-slate-950 px-4 py-3 text-center text-sm font-bold text-white hover:bg-slate-800"
                    href="/daftar"
                  >
                    Gabung Anggota
                  </Link>
                  <Link
                    className="rounded-md border border-slate-400 px-4 py-3 text-center text-sm font-semibold text-slate-800 hover:bg-slate-100"
                    href="/masuk"
                  >
                    Masuk
                  </Link>
                </>
              ) : null}
            </div>
          </div>
        </div>
      </nav>
      {logoutError && (
        <p
          className="mx-auto max-w-7xl px-4 pb-2 text-right text-xs text-red-700 sm:px-6 lg:px-8"
          role="alert"
        >
          {logoutError}
        </p>
      )}
    </header>
  );
}
