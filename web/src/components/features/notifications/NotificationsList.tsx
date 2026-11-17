"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import {
  getNotifications,
  markAsRead,
  type AppNotification,
} from "@/lib/api/notifications";

function formatDate(value: string): string {
  return new Intl.DateTimeFormat("id-ID", {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(value));
}

export default function NotificationsList() {
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [markingId, setMarkingId] = useState<string | null>(null);

  const loadNotifications = useCallback(async () => {
    setIsLoading(true);
    setError(null);

    try {
      const result = await getNotifications();
      setNotifications(result.notifications);
      setUnreadCount(result.unread_count);
    } catch (loadError: unknown) {
      setError(
        loadError instanceof Error
          ? loadError.message
          : "Notifikasi tidak dapat dimuat.",
      );
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    let active = true;

    getNotifications()
      .then((result) => {
        if (active) {
          setNotifications(result.notifications);
          setUnreadCount(result.unread_count);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(
            loadError instanceof Error
              ? loadError.message
              : "Notifikasi tidak dapat dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setIsLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  async function handleMarkAsRead(notification: AppNotification) {
    setMarkingId(notification.id);
    setError(null);

    try {
      await markAsRead(notification.id);
      setNotifications((current) =>
        current.map((item) =>
          item.id === notification.id
            ? { ...item, read_at: new Date().toISOString() }
            : item,
        ),
      );
      setUnreadCount((count) => Math.max(0, count - 1));
    } catch (markError: unknown) {
      setError(
        markError instanceof Error
          ? markError.message
          : "Notifikasi tidak dapat ditandai sudah dibaca.",
      );
    } finally {
      setMarkingId(null);
    }
  }

  return (
    <main className="mx-auto w-full max-w-4xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
      <header className="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
          <p className="text-sm font-bold uppercase tracking-wide text-primary">
            Kotak masuk
          </p>
          <h1 className="mt-1 text-3xl font-black text-text-main">Notifikasi</h1>
        </div>
        <p className="text-sm font-semibold text-text-muted">
          {unreadCount} belum dibaca
        </p>
      </header>

      {error && (
        <div
          className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"
          role="alert"
        >
          <p>{error}</p>
          <button
            className="rounded-md border border-red-300 px-3 py-2 font-semibold hover:bg-red-100"
            onClick={() => void loadNotifications()}
            type="button"
          >
            Coba lagi
          </button>
        </div>
      )}

      {isLoading ? (
        <p aria-live="polite" className="rounded-lg bg-surface p-6 text-text-muted">
          Memuat notifikasi...
        </p>
      ) : notifications.length === 0 ? (
        <section className="rounded-lg border border-background bg-surface p-6 text-center">
          <p className="font-semibold text-text-main">Belum ada notifikasi.</p>
          <p className="mt-2 text-sm text-text-muted">
            Ringkasan harian akan muncul di sini.
          </p>
        </section>
      ) : (
        <ul className="space-y-4">
          {notifications.map((notification) => {
            const digest = notification.data;
            const isDailyDigest = digest.type === "daily_digest";

            return (
              <li
                className={`rounded-lg border bg-surface p-5 shadow-sm ${
                  notification.read_at
                    ? "border-background"
                    : "border-primary/40 ring-1 ring-primary/10"
                }`}
                key={notification.id}
              >
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <h2 className="text-lg font-bold text-text-main">
                      {digest.title || "Notifikasi PARTAI"}
                    </h2>
                    <time
                      className="mt-1 block text-xs text-text-muted"
                      dateTime={notification.created_at}
                    >
                      {formatDate(notification.created_at)}
                    </time>
                  </div>
                  {notification.read_at ? (
                    <span className="rounded-full bg-background px-3 py-1 text-xs font-semibold text-text-muted">
                      Sudah dibaca
                    </span>
                  ) : (
                    <span className="rounded-full bg-primary-light px-3 py-1 text-xs font-bold text-primary">
                      Baru
                    </span>
                  )}
                </div>

                {isDailyDigest && (
                  <div className="mt-5 grid gap-4 sm:grid-cols-3">
                    <section className="rounded-md bg-background/60 p-4">
                      <h3 className="text-xs font-bold uppercase tracking-wide text-primary">
                        Soal hari ini
                      </h3>
                      <p className="mt-2 text-sm leading-6 text-text-main">
                        {digest.quiz.question}
                      </p>
                      <p className="mt-2 text-xs text-text-muted">
                        {digest.quiz.domain} · {digest.quiz.level}
                      </p>
                      <Link
                        className="mt-4 inline-flex rounded-md bg-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                        href="/belajar"
                      >
                        Kerjakan Soal
                      </Link>
                    </section>

                    <section className="rounded-md bg-background/60 p-4">
                      <h3 className="text-xs font-bold uppercase tracking-wide text-primary">
                        Berita pengawasan
                      </h3>
                      <p className="mt-2 text-sm font-bold text-text-main">
                        {digest.news.title}
                      </p>
                      <p className="mt-2 text-sm leading-6 text-text-muted">
                        {digest.news.content}
                      </p>
                      {digest.news.source_url && (
                        <a
                          className="mt-3 inline-block text-sm font-semibold text-primary underline"
                          href={digest.news.source_url}
                          rel="noreferrer"
                          target="_blank"
                        >
                          Baca sumber berita
                        </a>
                      )}
                    </section>

                    <section className="rounded-md bg-background/60 p-4">
                      <h3 className="text-xs font-bold uppercase tracking-wide text-primary">
                        Data kesejahteraan
                      </h3>
                      <p className="mt-2 text-sm font-bold text-text-main">
                        {digest.welfare.indicator}
                      </p>
                      <p className="mt-2 text-2xl font-black text-text-main">
                        {digest.welfare.value} {digest.welfare.unit}
                      </p>
                      <p className="mt-1 text-sm text-text-muted">
                        {digest.welfare.region_name} · {digest.welfare.year}
                      </p>
                      <p className="mt-2 text-xs text-text-muted">
                        Sumber: {digest.welfare.source}
                      </p>
                    </section>
                  </div>
                )}

                {!notification.read_at && (
                  <div className="mt-4 flex justify-end">
                    <button
                      className="rounded-md px-3 py-2 text-sm font-semibold text-primary hover:bg-primary-light disabled:opacity-50"
                      disabled={markingId === notification.id}
                      onClick={() => void handleMarkAsRead(notification)}
                      type="button"
                    >
                      {markingId === notification.id
                        ? "Menyimpan..."
                        : "Tandai sudah dibaca"}
                    </button>
                  </div>
                )}
              </li>
            );
          })}
        </ul>
      )}
    </main>
  );
}
