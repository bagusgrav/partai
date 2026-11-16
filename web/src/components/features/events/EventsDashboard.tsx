"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { getActiveEvents } from "@/lib/api";
import type { ActiveEvent } from "@/types";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function EventsDashboard() {
  const [events, setEvents] = useState<ActiveEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  async function loadEvents(): Promise<void> {
    setLoading(true);
    setError(null);

    try {
      const response = await getActiveEvents();
      setEvents(response.events);
    } catch (loadError) {
      setError(getErrorMessage(loadError));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    let active = true;

    getActiveEvents()
      .then(({ events: activeEvents }) => {
        if (active) {
          setEvents(activeEvents);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(getErrorMessage(loadError));
        }
      })
      .finally(() => {
        if (active) {
          setLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-4xl">
        <header className="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-slate-900">
              Aku Hadir
            </p>
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">Aksi Lapangan</h1>
          </div>
          <Link
            className="text-sm font-bold text-slate-900 underline decoration-slate-400 underline-offset-4 hover:text-slate-700"
            href="/"
          >
            Kembali
          </Link>
        </header>

        <section className="space-y-5">
          {loading ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Memuat acara aktif...
            </p>
          ) : error ? (
            <div className="rounded-3xl border border-slate-300 bg-white p-8 text-center">
              <p className="text-sm font-bold text-slate-700" role="alert">{error}</p>
              <button
                className="mt-5 min-h-12 rounded-xl bg-slate-900 px-6 font-bold text-white hover:bg-slate-700"
                onClick={loadEvents}
                type="button"
              >
                Coba lagi
              </button>
            </div>
          ) : events.length === 0 ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Tidak ada acara yang sedang berlangsung.
            </p>
          ) : (
            events.map((event) => (
              <article
                className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8"
                key={event.id}
              >
                <div className="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <h2 className="text-xl font-extrabold text-slate-900">{event.title}</h2>
                    <p className="mt-3 whitespace-pre-wrap leading-relaxed text-slate-600">
                      {event.description}
                    </p>
                    <p className="mt-4 text-sm font-semibold text-slate-500">
                      Radius kehadiran {event.radius_m} meter
                    </p>
                  </div>
                  <div className="flex shrink-0 flex-col gap-3 sm:w-48">
                    <Link
                      className="rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-extrabold text-white hover:bg-slate-700"
                      href={`/events/${event.id}/claim`}
                    >
                      Klaim Kehadiran
                    </Link>
                    <Link
                      className="rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 text-center text-sm font-extrabold text-slate-800 hover:bg-slate-200"
                      href={`/events/${event.id}/attest`}
                    >
                      Validasi Teman
                    </Link>
                    <Link
                      className="text-center text-xs font-bold text-slate-500 underline underline-offset-4 hover:text-slate-900"
                      href={`/events/${event.id}/qr`}
                    >
                      QR Koordinator
                    </Link>
                  </div>
                </div>
              </article>
            ))
          )}
        </section>
      </div>
    </main>
  );
}
