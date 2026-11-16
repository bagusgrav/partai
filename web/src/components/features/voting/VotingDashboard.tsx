"use client";

import Link from "next/link";
import { useEffect, useRef, useState, type FormEvent } from "react";
import { getActiveVotes, submitVote } from "@/lib/api";
import type { ActiveVote } from "@/types";
import VoteCard from "./VoteCard";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function VotingDashboard() {
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
              <VoteCard
                isRequestLocked={requestIds.current[vote.id] !== undefined}
                isSubmitted={submittedVotes[vote.id] ?? false}
                isSubmitting={submittingVotes[vote.id] ?? false}
                key={vote.id}
                onSelectOption={(optionId) =>
                  setSelectedOptions((current) => ({
                    ...current,
                    [vote.id]: optionId,
                  }))
                }
                onSubmit={(event) => handleSubmit(event, vote)}
                selectedOption={selectedOptions[vote.id]}
                submissionError={submissionErrors[vote.id]}
                vote={vote}
              />
            ))
          )}
        </section>
      </div>
    </main>
  );
}
