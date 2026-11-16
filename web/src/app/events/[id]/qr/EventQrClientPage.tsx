"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { QRCodeSVG } from "qrcode.react";
import { getEventQrToken } from "@/lib/api";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function EventQrClientPage({ eventId }: { eventId: string }) {
  const [token, setToken] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    const refreshToken = (): void => {
      getEventQrToken(eventId)
        .then((nextToken) => {
          if (active) {
            setToken(nextToken);
            setError(null);
          }
        })
        .catch((loadError: unknown) => {
          if (active) {
            setToken(null);
            setError(getErrorMessage(loadError));
          }
        })
        .finally(() => {
          if (active) {
            setLoading(false);
          }
        });
    };

    refreshToken();
    const intervalId = window.setInterval(refreshToken, 90_000);

    return () => {
      active = false;
      window.clearInterval(intervalId);
    };
  }, [eventId]);

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-xl">
        <header className="mb-10 flex items-center justify-between gap-4">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-emerald-700">
              Aku Hadir
            </p>
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">QR Kehadiran</h1>
          </div>
          <Link
            className="text-sm font-bold text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-900"
            href="/events"
          >
            Kembali ke acara
          </Link>
        </header>

        <section className="rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-xl shadow-slate-200/50 sm:p-10">
          {error ? (
            <p className="text-sm font-bold text-red-700" role="alert">{error}</p>
          ) : token ? (
            <>
              <div className="inline-flex rounded-2xl border border-slate-100 bg-white p-4">
                <QRCodeSVG value={token} size={256} level="M" />
              </div>
              <p className="mt-6 break-all rounded-xl bg-slate-50 p-4 font-mono text-xs text-slate-600">
                {token}
              </p>
              <p className="mt-4 text-sm font-semibold text-slate-500">
                QR diperbarui setiap 90 detik. Anggota dapat memasukkan token untuk mengklaim kehadiran.
              </p>
            </>
          ) : (
            <p className="font-semibold text-slate-600">
              {loading ? "Memuat token QR..." : "Token QR belum tersedia."}
            </p>
          )}
        </section>
      </div>
    </main>
  );
}
