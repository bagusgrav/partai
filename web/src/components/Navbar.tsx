"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import NavbarRegistrationCTA from "@/components/NavbarRegistrationCTA";
import {
  getCurrentUser,
  getNotifications,
  NOTIFICATIONS_UPDATED_EVENT,
} from "@/lib/api";
import type { RegisteredUser } from "@/types";

const links = [
  { href: "/", label: "Beranda" },
  { href: "/belajar", label: "Belajar" },
  { href: "/review", label: "Juri Warga" },
  { href: "/events", label: "Gerak Warga" },
  { href: "/manifesto", label: "Manifesto" },
  { href: "/panduan", label: "Panduan" },
];

const groupedLinks = {
  "Transparansi Data": [
    { href: "/data", label: "Data Publik" },
    { href: "/jemput-hak", label: "Jemput Hak" },
    { href: "/kontrak-faksi", label: "Kontrak Faksi" },
    { href: "/info", label: "Info Indonesia" },
  ],
  Partisipasi: [
    { href: "/voting", label: "Voting" },
    { href: "/recall", label: "Tata Kelola" },
  ],
};

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
      className="relative inline-flex size-10 items-center justify-center rounded-md text-text-main transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
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
        <span className="absolute right-0 top-0 min-w-5 rounded-full bg-red-600 px-1 text-center text-xs font-bold leading-5 text-white">
          {unreadCount > 99 ? "99+" : unreadCount}
        </span>
      )}
    </Link>
  );
}

export default function Navbar() {
  const dropdownRef = useRef<HTMLDivElement>(null);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [dropdownOpen, setDropdownOpen] = useState<string | null>(null);
  const [currentUser, setCurrentUser] = useState<RegisteredUser | null>(null);
  const [profileError, setProfileError] = useState<string | null>(null);
  const [unreadCount, setUnreadCount] = useState(0);
  const [notificationError, setNotificationError] = useState<string | null>(null);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        event.target instanceof Node &&
        !dropdownRef.current?.contains(event.target)
      ) {
        setDropdownOpen(null);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setDropdownOpen(null);
      }
    };

    document.addEventListener("keydown", handleKeyDown);
    return () => {
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, []);

  useEffect(() => {
    let active = true;

    getCurrentUser()
      .then((user) => {
        if (active) {
          setCurrentUser(user);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setProfileError(
            loadError instanceof Error
              ? loadError.message
              : "Reputasi tidak dapat dimuat.",
          );
        }
      });

    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    let active = true;

    const loadUnreadCount = () => {
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
          <div
            className="flex flex-wrap items-center gap-2 lg:gap-4"
            ref={dropdownRef}
          >
            {Object.entries(groupedLinks).map(([label, items]) => (
              <details
                className="relative"
                key={label}
                open={dropdownOpen === label}
              >
                <summary
                  className="cursor-pointer list-none whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold text-text-muted transition hover:bg-primary-light hover:text-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                  onClick={(event) => {
                    event.preventDefault();
                    setDropdownOpen((open) => (open === label ? null : label));
                  }}
                >
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
        </div>
        <div className="hidden flex-wrap items-center gap-2 md:flex">
          {currentUser && (
            <NotificationBellLink
              error={notificationError}
              unreadCount={unreadCount}
            />
          )}
          {currentUser ? (
            <Link
              className="flex items-center gap-2 rounded-md px-2 py-1 text-sm font-semibold text-text-main hover:bg-primary-light"
              href="/profil"
            >
              <span>{currentUser.name}</span>
              <span className="rounded-full bg-primary-light px-2 py-1 text-xs font-bold text-primary">
                {currentUser.reputation_level}
              </span>
            </Link>
          ) : profileError ? (
            <span className="text-xs text-text-muted" title={profileError}>
              Reputasi tidak tersedia
            </span>
          ) : null}
          <NavbarRegistrationCTA onClick={() => setIsMobileMenuOpen(false)} />
          <a
            className="whitespace-nowrap rounded-md border border-primary bg-transparent px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
            href="https://id.akuhadir.my.id"
          >
            Masuk
          </a>
          <Link
            className="whitespace-nowrap text-sm font-semibold text-primary hover:underline"
            href="/lupa-password"
          >
            Lupa Password?
          </Link>
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
              {currentUser && (
                <Link
                  className="flex items-center justify-between rounded-md px-3 py-3 text-sm font-semibold text-text-main hover:bg-primary-light"
                  href="/notifikasi"
                  onClick={() => setIsMobileMenuOpen(false)}
                  title={notificationError ?? "Notifikasi"}
                >
                  <span className="flex items-center gap-2">
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
                    Notifikasi
                  </span>
                  {unreadCount > 0 && (
                    <span className="rounded-full bg-red-600 px-2 py-1 text-xs font-bold text-white">
                      {unreadCount > 99 ? "99+" : unreadCount}
                    </span>
                  )}
                </Link>
              )}
              {currentUser ? (
                <Link
                  className="flex items-center justify-between rounded-md px-3 py-3 text-sm font-semibold text-text-main hover:bg-primary-light"
                  href="/profil"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  <span>{currentUser.name}</span>
                  <span className="rounded-full bg-primary-light px-2 py-1 text-xs font-bold text-primary">
                    {currentUser.reputation_level}
                  </span>
                </Link>
              ) : profileError ? (
                <p className="px-3 text-xs text-text-muted" title={profileError}>
                  Reputasi tidak tersedia
                </p>
              ) : null}
              <NavbarRegistrationCTA onClick={() => setIsMobileMenuOpen(false)} />
              <a
                className="rounded-md border border-primary px-4 py-3 text-center text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                href="https://id.akuhadir.my.id"
              >
                Masuk
              </a>
              <Link
                className="py-2 text-center text-sm font-semibold text-primary hover:underline"
                href="/lupa-password"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                Lupa Password?
              </Link>
            </div>
          </div>
        )}
      </nav>
    </header>
  );
}
