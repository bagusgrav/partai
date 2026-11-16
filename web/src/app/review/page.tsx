"use client";

import Link from "next/link";
import { useEffect, useState, type FormEvent } from "react";
import { getPendingEssay, submitEssayReview } from "@/lib/api";
import type { PendingEssaySubmission } from "@/types";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function ReviewPage() {
  const [submission, setSubmission] = useState<PendingEssaySubmission | null>(null);
  const [score, setScore] = useState("80");
  const [feedback, setFeedback] = useState("");
  const [requestId, setRequestId] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [reviewed, setReviewed] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function loadSubmission(): Promise<void> {
    setLoading(true);
    setSubmission(null);
    setError(null);

    try {
      const response = await getPendingEssay();
      setSubmission(response.submission);
      setReviewed(false);
      setScore("80");
      setFeedback("");
      setRequestId(null);
    } catch (loadError) {
      setError(getErrorMessage(loadError));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    let active = true;

    getPendingEssay()
      .then(({ submission: pendingSubmission }) => {
        if (active) {
          setSubmission(pendingSubmission);
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

  async function handleSubmit(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    if (!submission || !score) {
      return;
    }

    const idempotencyKey = requestId ?? crypto.randomUUID();
    setRequestId(idempotencyKey);
    setSubmitting(true);
    setError(null);

    try {
      await submitEssayReview(
        submission.id,
        Number(score),
        feedback.trim() || null,
        idempotencyKey,
      );
      setReviewed(true);
      setRequestId(null);
    } catch (submitError) {
      setError(getErrorMessage(submitError));
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-3xl">
        <header className="mb-10 flex items-center justify-between gap-4">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-emerald-700">
              Partai
            </p>
            <h1 className="mt-1 text-xl font-extrabold text-slate-900">Juri Warga</h1>
          </div>
          <Link
            className="text-sm font-bold text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-900"
            href="/belajar"
          >
            Kembali ke belajar
          </Link>
        </header>

        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-10">
          {loading ? (
            <p className="text-center font-semibold text-slate-600">Memuat esai untuk dinilai...</p>
          ) : error && !submission ? (
            <div className="text-center">
              <p className="text-sm font-bold text-red-700" role="alert">{error}</p>
              <button
                className="mt-6 min-h-12 rounded-xl bg-emerald-600 px-6 font-bold text-white hover:bg-emerald-700"
                onClick={loadSubmission}
                type="button"
              >
                Coba lagi
              </button>
            </div>
          ) : !submission ? (
            <div className="text-center">
              <h2 className="text-2xl font-extrabold text-slate-900">Belum ada esai untuk dinilai</h2>
              <p className="mt-3 text-slate-600">
                Esai anggota lain yang masih menunggu penilaian akan muncul di sini.
              </p>
              <button
                className="mt-6 min-h-12 rounded-xl bg-emerald-600 px-6 font-bold text-white hover:bg-emerald-700"
                onClick={loadSubmission}
                type="button"
              >
                Muat ulang
              </button>
            </div>
          ) : (
            <>
              <p className="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
                Esai anggota
              </p>
              <h2 className="mt-4 text-2xl font-extrabold leading-snug text-slate-900">
                {submission.item.payload.question}
              </h2>
              <div className="mt-6 rounded-2xl bg-slate-50 p-5 leading-relaxed text-slate-700">
                {submission.answer_text}
              </div>

              {reviewed ? (
                <div className="mt-8">
                  <p className="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 font-semibold text-emerald-800">
                    Penilaian berhasil dikirim. Terima kasih telah berkontribusi.
                  </p>
                  <button
                    className="mt-6 min-h-14 w-full rounded-2xl bg-emerald-600 px-6 font-extrabold text-white hover:bg-emerald-700"
                    onClick={loadSubmission}
                    type="button"
                  >
                    Nilai Esai Berikutnya
                  </button>
                </div>
              ) : (
                <form className="mt-8 space-y-5" onSubmit={handleSubmit}>
                  <div>
                    <label className="mb-2 block text-sm font-bold text-slate-700" htmlFor="score">
                      Nilai (0-100)
                    </label>
                    <input
                      className="min-h-12 w-full rounded-xl border-2 border-slate-200 px-4 text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                      id="score"
                      max={100}
                      min={0}
                      onChange={(event) => setScore(event.target.value)}
                      required
                      type="number"
                      value={score}
                    />
                  </div>
                  <div>
                    <label className="mb-2 block text-sm font-bold text-slate-700" htmlFor="feedback">
                      Feedback
                    </label>
                    <textarea
                      className="min-h-32 w-full rounded-xl border-2 border-slate-200 p-4 text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                      id="feedback"
                      onChange={(event) => setFeedback(event.target.value)}
                      placeholder="Berikan catatan yang membangun..."
                      value={feedback}
                    />
                  </div>
                  {error && (
                    <p className="text-sm font-semibold text-red-700" role="alert">{error}</p>
                  )}
                  <button
                    className="min-h-14 w-full rounded-2xl bg-emerald-600 px-6 font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:bg-slate-300"
                    disabled={submitting}
                    type="submit"
                  >
                    {submitting ? "Mengirim penilaian..." : "Kirim Penilaian"}
                  </button>
                </form>
              )}
            </>
          )}
        </section>
      </div>
    </main>
  );
}
