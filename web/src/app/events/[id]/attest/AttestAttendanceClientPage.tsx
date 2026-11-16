"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import { getPendingAttendanceClaims, submitAttendanceAttestation } from "@/lib/api";
import type { PendingAttendanceClaim } from "@/types";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function AttestAttendanceClientPage({ eventId }: { eventId: string }) {
  const [claims, setClaims] = useState<PendingAttendanceClaim[]>([]);
  const [requestIds, setRequestIds] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [submittingClaimId, setSubmittingClaimId] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const loadClaims = useCallback(async (): Promise<void> => {
    setLoading(true);
    setError(null);

    try {
      const response = await getPendingAttendanceClaims(eventId);
      setClaims(response.claims);
    } catch (loadError) {
      setError(getErrorMessage(loadError));
    } finally {
      setLoading(false);
    }
  }, [eventId]);

  useEffect(() => {
    let active = true;

    getPendingAttendanceClaims(eventId)
      .then(({ claims: pendingClaims }) => {
        if (active) {
          setClaims(pendingClaims);
          setError(null);
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
  }, [eventId]);

  async function handleAttest(claimId: string): Promise<void> {
    setSubmittingClaimId(claimId);
    setError(null);
    const requestId = requestIds[claimId] ?? crypto.randomUUID();
    setRequestIds((current) => ({ ...current, [claimId]: requestId }));

    try {
      await submitAttendanceAttestation(claimId, requestId);
      setClaims((current) => current.filter((claim) => claim.id !== claimId));
      setRequestIds((current) => {
        const next = { ...current };
        delete next[claimId];
        return next;
      });
    } catch (submitError) {
      setError(getErrorMessage(submitError));
    } finally {
      setSubmittingClaimId(null);
    }
  }

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-3xl">
        <header className="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-slate-900">
              Aku Hadir
            </p>
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">Validasi Teman</h1>
          </div>
          <Link
            className="text-sm font-bold text-slate-900 underline decoration-slate-400 underline-offset-4 hover:text-slate-700"
            href="/events"
          >
            Kembali
          </Link>
        </header>

        <section className="space-y-4">
          {error && (
            <p className="rounded-2xl border border-slate-300 bg-slate-100 p-4 text-sm font-semibold text-slate-700" role="alert">
              {error}
            </p>
          )}
          {loading ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Memuat klaim yang menunggu...
            </p>
          ) : claims.length === 0 ? (
            <div className="rounded-3xl border border-slate-200 bg-white p-8 text-center">
              <p className="font-semibold text-slate-600">Tidak ada klaim yang menunggu validasi.</p>
              <button
                className="mt-5 min-h-12 rounded-xl bg-slate-900 px-6 font-bold text-white hover:bg-slate-700"
                onClick={loadClaims}
                type="button"
              >
                Muat ulang
              </button>
            </div>
          ) : (
            claims.map((claim) => (
              <article
                className="flex flex-col gap-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:flex-row sm:items-center sm:justify-between"
                key={claim.id}
              >
                <div>
                  <h2 className="font-extrabold text-slate-900">{claim.member_name}</h2>
                  <p className="mt-2 text-sm text-slate-600">
                    Jarak tercatat {claim.distance_m} meter ·{" "}
                    {new Date(claim.created_at).toLocaleString("id-ID")}
                  </p>
                </div>
                <button
                  className="min-h-12 rounded-xl bg-slate-900 px-6 font-extrabold text-white hover:bg-slate-700 disabled:cursor-wait disabled:bg-slate-300"
                  disabled={submittingClaimId !== null}
                  onClick={() => handleAttest(claim.id)}
                  type="button"
                >
                  {submittingClaimId === claim.id ? "Mengirim..." : "Validasi"}
                </button>
              </article>
            ))
          )}
        </section>
      </div>
    </main>
  );
}
