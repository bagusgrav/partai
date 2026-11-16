"use client";

import Link from "next/link";
import { useState } from "react";
import { submitAttendanceClaim } from "@/lib/api";
import type { AttendanceClaimResult } from "@/types";

interface PendingClaim {
  token: string;
  lat: number;
  lng: number;
  requestId: string;
}

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

function getCurrentPosition(): Promise<GeolocationPosition> {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error("Browser ini tidak mendukung akses lokasi."));
      return;
    }

    navigator.geolocation.getCurrentPosition(
      resolve,
      (error) => reject(new Error(error.message || "Lokasi tidak dapat diakses.")),
      { enableHighAccuracy: true, timeout: 15_000, maximumAge: 0 },
    );
  });
}

export default function ClaimAttendanceClientPage({ eventId }: { eventId: string }) {
  const [token, setToken] = useState("");
  const [pendingClaim, setPendingClaim] = useState<PendingClaim | null>(null);
  const [result, setResult] = useState<AttendanceClaimResult | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleClaim(): Promise<void> {
    setSubmitting(true);
    setError(null);

    try {
      let claim = pendingClaim;

      if (!claim) {
        const cleanedToken = token.trim();
        if (!cleanedToken) {
          throw new Error("Masukkan token dari QR acara terlebih dahulu.");
        }

        const position = await getCurrentPosition();
        claim = {
          token: cleanedToken,
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          requestId: crypto.randomUUID(),
        };
        setPendingClaim(claim);
      }

      const claimResult = await submitAttendanceClaim(
        eventId,
        claim.token,
        claim.lat,
        claim.lng,
        claim.requestId,
      );
      setResult(claimResult);
      setPendingClaim(null);
    } catch (submitError) {
      setError(getErrorMessage(submitError));
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-xl">
        <header className="mb-10 flex items-center justify-between gap-4">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-emerald-700">
              Aku Hadir
            </p>
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">Klaim Kehadiran</h1>
          </div>
          <Link
            className="text-sm font-bold text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-900"
            href="/events"
          >
            Kembali
          </Link>
        </header>

        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-10">
          {result ? (
            <div className="text-center">
              <h2 className="text-xl font-extrabold text-emerald-800">
                {result.claim_status === "rejected" || result.status === "rejected"
                  ? "Klaim ditolak"
                  : "Klaim berhasil dikirim"}
              </h2>
              <p className="mt-3 text-slate-600">
                {result.claim_status === "rejected" || result.status === "rejected"
                  ? `Jarak Anda ${result.distance_m} meter dari lokasi acara.`
                  : "Klaim menunggu validasi anggota lain."}
              </p>
            </div>
          ) : (
            <>
              <p className="leading-relaxed text-slate-600">
                Pindai QR koordinator lalu masukkan tokennya. Browser akan meminta izin lokasi untuk memeriksa jarak Anda dari acara.
              </p>
              <label className="mt-6 block text-sm font-bold text-slate-700" htmlFor="qr-token">
                Token QR
              </label>
              <input
                autoComplete="off"
                className="mt-2 min-h-12 w-full rounded-xl border-2 border-slate-200 px-4 font-mono text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                id="qr-token"
                onChange={(event) => setToken(event.target.value)}
                placeholder="Tempel token QR di sini"
                readOnly={pendingClaim !== null}
                value={pendingClaim?.token ?? token}
              />
              {error && (
                <p className="mt-4 text-sm font-semibold text-red-700" role="alert">{error}</p>
              )}
              <button
                className="mt-6 min-h-14 w-full rounded-2xl bg-emerald-600 px-6 font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:bg-slate-300"
                disabled={submitting}
                onClick={handleClaim}
                type="button"
              >
                {submitting
                  ? "Mengirim klaim..."
                  : pendingClaim
                    ? "Coba kirim ulang klaim"
                    : "Klaim Kehadiran"}
              </button>
            </>
          )}
        </section>
      </div>
    </main>
  );
}
