import type { FormEvent } from "react";
import { useState } from "react";
import type { ActiveVote } from "@/types";

interface VoteCardProps {
  vote: ActiveVote;
  canVote: boolean;
  selectedOption?: number;
  isSubmitting: boolean;
  isSubmitted: boolean;
  isRequestLocked: boolean;
  submissionError?: string;
  onSelectOption(optionId: number): void;
  onSubmit(event: FormEvent<HTMLFormElement>): void;
}

function formatEndTime(value: string): string {
  return new Date(value).toLocaleString("id-ID", {
    dateStyle: "long",
    timeStyle: "short",
  });
}

export default function VoteCard({
  vote,
  canVote,
  selectedOption,
  isSubmitting,
  isSubmitted,
  isRequestLocked,
  submissionError,
  onSelectOption,
  onSubmit,
}: VoteCardProps) {
  const [isConfirming, setIsConfirming] = useState(false);
  const totalVotes = vote.vote_options.reduce(
    (total, option) => total + (option.votes_count ?? 0),
    0,
  );

  function handleSubmit(event: FormEvent<HTMLFormElement>): void {
    event.preventDefault();

    if (isConfirming) {
      onSubmit(event);
      return;
    }

    setIsConfirming(true);
  }

  return (
    <article className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
      <h2 className="text-xl font-extrabold text-slate-900">{vote.title}</h2>
      <p className="mt-3 whitespace-pre-wrap leading-relaxed text-slate-600">
        {vote.description}
      </p>
      <p className="mt-4 text-sm font-semibold text-slate-500">
        Berakhir: {formatEndTime(vote.ends_at)}
      </p>

      {isSubmitted ? (
        <div className="mt-6 space-y-4">
          <p className="rounded-2xl border border-slate-300 bg-slate-100 p-5 font-semibold text-slate-800">
            Anda sudah memilih. Terima kasih atas partisipasi Anda.
          </p>
          {vote.has_voted && vote.vote_options.every((option) => option.votes_count !== undefined) && (
            <section aria-label={`Hasil ${vote.title}`} className="rounded-2xl border border-slate-200 p-5">
              <h3 className="font-extrabold text-slate-900">Hasil sementara</h3>
              <p className="mt-1 text-sm text-slate-600">{totalVotes} suara masuk</p>
              <ul className="mt-4 space-y-4">
                {vote.vote_options.map((option) => {
                  const count = option.votes_count ?? 0;
                  const percentage = totalVotes === 0 ? 0 : (count / totalVotes) * 100;

                  return (
                    <li key={option.id}>
                      <div className="flex justify-between gap-4 text-sm">
                        <span className="font-semibold text-slate-800">{option.option_text}</span>
                        <span className="shrink-0 text-slate-600">
                          {count} suara ({percentage.toFixed(0)}%)
                        </span>
                      </div>
                      <div
                        aria-hidden="true"
                        className="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"
                      >
                        <div
                          className="h-full rounded-full bg-slate-800"
                          style={{ width: `${percentage}%` }}
                        />
                      </div>
                    </li>
                  );
                })}
              </ul>
            </section>
          )}
          {submissionError && (
            <p className="text-sm font-semibold text-slate-700" role="alert">
              {submissionError}
            </p>
          )}
        </div>
      ) : (
        <div className="mt-6 space-y-4">
          {!canVote && (
            <p
              className="rounded-2xl border border-slate-300 bg-slate-100 p-4 text-sm font-semibold text-slate-700"
              role="status"
            >
              Voting hanya tersedia bagi anggota terverifikasi.
            </p>
          )}
          <form className="space-y-4" onSubmit={handleSubmit}>
            <fieldset className="space-y-3" disabled={!canVote}>
              <legend className="mb-3 text-sm font-bold text-slate-700">
                Pilih satu opsi
              </legend>
              {vote.vote_options.map((option) => (
                <label
                  className="flex min-h-14 cursor-pointer items-center gap-3 rounded-xl border-2 border-slate-200 px-4 py-3 text-slate-800 transition has-[:checked]:border-slate-900 has-[:checked]:bg-slate-100"
                  key={option.id}
                >
                  <input
                    checked={selectedOption === option.id}
                    className="size-4 accent-slate-900"
                    disabled={!canVote || isSubmitting || isRequestLocked || isConfirming}
                    name={`vote-${vote.id}`}
                    onChange={() => onSelectOption(option.id)}
                    required
                    type="radio"
                    value={option.id}
                  />
                  <span className="font-semibold">{option.option_text}</span>
                </label>
              ))}
            </fieldset>
            {submissionError && (
              <p className="text-sm font-semibold text-slate-700" role="alert">
                {submissionError}
              </p>
            )}
            {isConfirming && (
              <div className="rounded-2xl border border-slate-300 bg-slate-50 p-4" role="status">
                <p className="font-bold text-slate-900">Konfirmasi pilihan Anda</p>
                <p className="mt-1 text-slate-700">
                  {vote.vote_options.find((option) => option.id === selectedOption)?.option_text}
                </p>
                <button
                  className="mt-3 text-sm font-bold text-slate-700 underline underline-offset-4"
                  disabled={isSubmitting || isRequestLocked}
                  onClick={() => setIsConfirming(false)}
                  type="button"
                >
                  Ubah pilihan
                </button>
              </div>
            )}
            <button
              className="min-h-14 w-full rounded-2xl bg-slate-900 px-6 font-extrabold text-white transition hover:bg-slate-700 disabled:cursor-wait disabled:bg-slate-300"
              disabled={!canVote || selectedOption === undefined || isSubmitting}
              type="submit"
            >
              {isSubmitting
                ? "Mengirim suara..."
                : isConfirming
                  ? "Konfirmasi dan Kirim Suara"
                  : "Tinjau Pilihan"}
            </button>
          </form>
        </div>
      )}
    </article>
  );
}
