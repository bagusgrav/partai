"use client";

import Link from "next/link";
import { useCallback, useEffect, useRef, useState, type FormEvent } from "react";
import { getActiveRecalls, submitRecallRequest, submitRecallVote } from "@/lib/api";
import type { ActiveRecall } from "@/types";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function RecallPage() {
  const [recalls, setRecalls] = useState<ActiveRecall[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingError, setLoadingError] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [voteErrors, setVoteErrors] = useState<Record<string, string>>({});
  const [submittingRequest, setSubmittingRequest] = useState(false);
  const [submittingVotes, setSubmittingVotes] = useState<Record<string, boolean>>({});
  const [submittedVotes, setSubmittedVotes] = useState<Record<string, boolean>>({});
  const [notice, setNotice] = useState<string | null>(null);
  const requestId = useRef<string | null>(null);
  const voteRequests = useRef<Record<string, { requestId: string; vote: boolean }>>({});

  const loadRecalls = useCallback(async () => {
    setLoading(true);
    setLoadingError(null);

    try {
      const response = await getActiveRecalls();
      setRecalls(response.recalls);
    } catch (error) {
      setLoadingError(getErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void loadRecalls();
  }, [loadRecalls]);

  async function handleRequest(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();
    if (submittingRequest) {
      return;
    }

    const form = event.currentTarget;
    const formData = new FormData(form);
    setSubmittingRequest(true);
    setFormError(null);
    setNotice(null);

    try {
      await submitRecallRequest({
        target_user_id: String(formData.get("target_user_id")),
        reason: String(formData.get("reason")),
        evidence_url: String(formData.get("evidence_url") ?? ""),
        request_id: (requestId.current ??= crypto.randomUUID()),
      });
      requestId.current = null;
      form.reset();
      setNotice("Pengajuan recall berhasil dikirim dan sudah memasuki tahap voting.");
      await loadRecalls();
    } catch (error) {
      setFormError(getErrorMessage(error));
    } finally {
      setSubmittingRequest(false);
    }
  }

  async function handleVote(recallId: string, vote: boolean): Promise<void> {
    if (submittingVotes[recallId] || submittedVotes[recallId]) {
      return;
    }

    const pending = (voteRequests.current[recallId] ??= {
      requestId: crypto.randomUUID(),
      vote,
    });
    if (pending.vote !== vote) {
      return;
    }

    setSubmittingVotes((current) => ({ ...current, [recallId]: true }));
    setVoteErrors((current) => ({ ...current, [recallId]: "" }));
    setNotice(null);

    try {
      await submitRecallVote(recallId, vote, pending.requestId);
      setSubmittedVotes((current) => ({ ...current, [recallId]: true }));
      setNotice("Terima kasih, suara Anda telah dicatat.");
      await loadRecalls();
    } catch (error) {
      setVoteErrors((current) => ({
        ...current,
        [recallId]: getErrorMessage(error),
      }));
    } finally {
      setSubmittingVotes((current) => ({ ...current, [recallId]: false }));
    }
  }

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-4xl">
        <header className="mb-10 flex items-center justify-between gap-4">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-emerald-700">
              Tata Kelola Partai
            </p>
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">Recall Anggota Dewan</h1>
          </div>
          <Link
            className="text-sm font-bold text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-900"
            href="/"
          >
            Kembali
          </Link>
        </header>

        {notice && (
          <p className="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-800" role="status">
            {notice}
          </p>
        )}

        <section className="mb-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
          <h2 className="text-xl font-extrabold text-slate-900">Ajukan Recall</h2>
          <p className="mt-2 leading-relaxed text-slate-600">
            Pengajuan dari anggota terverifikasi langsung dibuka untuk voting anggota.
          </p>
          <form className="mt-6 space-y-4" onSubmit={handleRequest}>
            <label className="block space-y-2 text-sm font-bold text-slate-700">
              ID Anggota yang Di-recall
              <input
                autoComplete="off"
                className="min-h-12 w-full rounded-xl border border-slate-300 px-4 font-normal"
                name="target_user_id"
                onChange={() => { requestId.current = null; }}
                placeholder="UUID anggota"
                required
              />
            </label>
            <label className="block space-y-2 text-sm font-bold text-slate-700">
              Alasan
              <textarea
                className="min-h-28 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal"
                name="reason"
                onChange={() => { requestId.current = null; }}
                required
                rows={4}
              />
            </label>
            <label className="block space-y-2 text-sm font-bold text-slate-700">
              URL Bukti (opsional)
              <input
                className="min-h-12 w-full rounded-xl border border-slate-300 px-4 font-normal"
                name="evidence_url"
                onChange={() => { requestId.current = null; }}
                type="url"
              />
            </label>
            {formError && <p className="text-sm font-semibold text-red-700" role="alert">{formError}</p>}
            <button
              className="min-h-12 w-full rounded-xl bg-emerald-600 px-6 font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:bg-slate-300"
              disabled={submittingRequest}
              type="submit"
            >
              {submittingRequest ? "Mengirim pengajuan..." : "Ajukan Recall"}
            </button>
          </form>
        </section>

        <section aria-live="polite" className="space-y-5">
          <h2 className="text-xl font-extrabold text-slate-900">Recall Aktif</h2>
          {loading ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Memuat pengajuan recall...
            </p>
          ) : loadingError ? (
            <div className="rounded-3xl border border-red-200 bg-white p-8 text-center">
              <p className="text-sm font-bold text-red-700" role="alert">{loadingError}</p>
              <button
                className="mt-5 min-h-12 rounded-xl bg-emerald-600 px-6 font-bold text-white hover:bg-emerald-700"
                onClick={() => void loadRecalls()}
                type="button"
              >
                Coba lagi
              </button>
            </div>
          ) : recalls.length === 0 ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Tidak ada recall yang sedang dalam proses voting.
            </p>
          ) : (
            recalls.map((recall) => {
              const hasVoted = recall.has_voted || submittedVotes[recall.id] === true;
              const pendingVote = voteRequests.current[recall.id];
              const isSubmitting = submittingVotes[recall.id] ?? false;

              return (
                <article className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8" key={recall.id}>
                  <h3 className="text-xl font-extrabold text-slate-900">{recall.target_name}</h3>
                  <p className="mt-1 break-all text-xs text-slate-500">ID: {recall.target_user_id}</p>
                  <p className="mt-4 whitespace-pre-wrap leading-relaxed text-slate-700">{recall.reason}</p>
                  {recall.evidence_url && (
                    <a
                      className="mt-3 inline-block font-semibold text-emerald-700 underline underline-offset-4"
                      href={recall.evidence_url}
                      rel="noopener noreferrer"
                      target="_blank"
                    >
                      Lihat bukti
                    </a>
                  )}
                  <p className="mt-4 text-sm font-semibold text-slate-600">
                    Setuju: {recall.agree_count} · Tolak: {recall.reject_count} · Ambang persetujuan: {recall.votes_required} dari {recall.active_member_count} anggota aktif
                  </p>
                  {hasVoted ? (
                    <p className="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-800">
                      Anda sudah memberikan suara untuk pengajuan ini.
                    </p>
                  ) : (
                    <div className="mt-5">
                      {voteErrors[recall.id] && (
                        <p className="mb-3 text-sm font-semibold text-red-700" role="alert">
                          {voteErrors[recall.id]}
                        </p>
                      )}
                      <div className="flex flex-col gap-3 sm:flex-row">
                        <button
                          className="min-h-12 flex-1 rounded-xl bg-emerald-600 px-5 font-bold text-white hover:bg-emerald-700 disabled:bg-slate-300"
                          disabled={isSubmitting || (pendingVote !== undefined && pendingVote.vote !== true)}
                          onClick={() => void handleVote(recall.id, true)}
                          type="button"
                        >
                          Setuju Recall
                        </button>
                        <button
                          className="min-h-12 flex-1 rounded-xl bg-slate-700 px-5 font-bold text-white hover:bg-slate-800 disabled:bg-slate-300"
                          disabled={isSubmitting || (pendingVote !== undefined && pendingVote.vote !== false)}
                          onClick={() => void handleVote(recall.id, false)}
                          type="button"
                        >
                          Tolak Recall
                        </button>
                      </div>
                    </div>
                  )}
                </article>
              );
            })
          )}
        </section>
      </div>
    </main>
  );
}
