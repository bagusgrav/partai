"use client";

import Link from "next/link";
import { useEffect, useRef, useState, type FormEvent } from "react";
import { getActiveVotes, submitVote } from "@/lib/api";
import type { ActiveVote } from "@/types";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

function formatEndTime(value: string): string {
  return new Date(value).toLocaleString("id-ID", {
    dateStyle: "long",
    timeStyle: "short",
  });
}

export default function VotingPage() {
  const [votes, setVotes] = useState<ActiveVote[]>([]);
  const [selectedOptions, setSelectedOptions] = useState<Record<number, number>>({});
  const [submittingVotes, setSubmittingVotes] = useState<Record<number, boolean>>({});
  const [submittedVotes, setSubmittedVotes] = useState<Record<number, boolean>>({});
  const [submissionErrors, setSubmissionErrors] = useState<Record<number, string>>({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const requestIds = useRef<Record<number, string>>({});

  async function loadVotes(): Promise<void> {
    setLoading(true);
    setError(null);

    try {
      const response = await getActiveVotes();
      setVotes(response.votes);
    } catch (loadError) {
      setError(getErrorMessage(loadError));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    let active = true;

    getActiveVotes()
      .then(({ votes: activeVotes }) => {
        if (active) {
          setVotes(activeVotes);
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

  async function handleSubmit(
    event: FormEvent<HTMLFormElement>,
    vote: ActiveVote,
  ): Promise<void> {
    event.preventDefault();
    const optionId = selectedOptions[vote.id];

    if (optionId === undefined || submittingVotes[vote.id] || submittedVotes[vote.id]) {
      return;
    }

    const requestId = (requestIds.current[vote.id] ??= crypto.randomUUID());
    setSubmittingVotes((current) => ({ ...current, [vote.id]: true }));
    setSubmissionErrors((current) => ({ ...current, [vote.id]: "" }));

    try {
      await submitVote(vote.id, optionId, requestId);
      setSubmittedVotes((current) => ({ ...current, [vote.id]: true }));
    } catch (submitError) {
      setSubmissionErrors((current) => ({
        ...current,
        [vote.id]: getErrorMessage(submitError),
      }));
    } finally {
      setSubmittingVotes((current) => ({ ...current, [vote.id]: false }));
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
            <h1 className="mt-1 text-2xl font-extrabold text-slate-900">Voting</h1>
          </div>
          <Link
            className="text-sm font-bold text-emerald-700 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-900"
            href="/"
          >
            Kembali
          </Link>
        </header>

        <section aria-live="polite" className="space-y-5">
          {loading ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Memuat voting aktif...
            </p>
          ) : error ? (
            <div className="rounded-3xl border border-red-200 bg-white p-8 text-center">
              <p className="text-sm font-bold text-red-700" role="alert">{error}</p>
              <button
                className="mt-5 min-h-12 rounded-xl bg-emerald-600 px-6 font-bold text-white hover:bg-emerald-700"
                onClick={loadVotes}
                type="button"
              >
                Coba lagi
              </button>
            </div>
          ) : votes.length === 0 ? (
            <p className="rounded-3xl border border-slate-200 bg-white p-8 text-center font-semibold text-slate-600">
              Tidak ada voting yang sedang berlangsung.
            </p>
          ) : (
            votes.map((vote) => (
              <article
                className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8"
                key={vote.id}
              >
                <h2 className="text-xl font-extrabold text-slate-900">{vote.title}</h2>
                <p className="mt-3 whitespace-pre-wrap leading-relaxed text-slate-600">
                  {vote.description}
                </p>
                <p className="mt-4 text-sm font-semibold text-slate-500">
                  Berakhir: {formatEndTime(vote.ends_at)}
                </p>

                {submittedVotes[vote.id] ? (
                  <p className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 font-semibold text-emerald-800">
                    Terima kasih, suara Anda telah dicatat.
                  </p>
                ) : (
                  <form
                    className="mt-6 space-y-4"
                    onSubmit={(event) => handleSubmit(event, vote)}
                  >
                    <fieldset className="space-y-3">
                      <legend className="mb-3 text-sm font-bold text-slate-700">
                        Pilih satu opsi
                      </legend>
                      {vote.vote_options.map((option) => (
                        <label
                          className="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border-2 border-slate-200 px-4 py-3 text-slate-800 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50"
                          key={option.id}
                        >
                          <input
                            checked={selectedOptions[vote.id] === option.id}
                            className="size-4 accent-emerald-600"
                            disabled={
                              submittingVotes[vote.id] || requestIds.current[vote.id] !== undefined
                            }
                            name={`vote-${vote.id}`}
                            onChange={() =>
                              setSelectedOptions((current) => ({
                                ...current,
                                [vote.id]: option.id,
                              }))
                            }
                            required
                            type="radio"
                            value={option.id}
                          />
                          <span className="font-semibold">{option.option_text}</span>
                        </label>
                      ))}
                    </fieldset>
                    {submissionErrors[vote.id] && (
                      <p className="text-sm font-semibold text-red-700" role="alert">
                        {submissionErrors[vote.id]}
                      </p>
                    )}
                    <button
                      className="min-h-14 w-full rounded-2xl bg-emerald-600 px-6 font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-wait disabled:bg-slate-300"
                      disabled={
                        selectedOptions[vote.id] === undefined || submittingVotes[vote.id]
                      }
                      type="submit"
                    >
                      {submittingVotes[vote.id] ? "Mengirim suara..." : "Kirim Suara"}
                    </button>
                  </form>
                )}
              </article>
            ))
          )}
        </section>
      </div>
    </main>
  );
}
