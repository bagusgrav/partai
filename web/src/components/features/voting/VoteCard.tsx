import type { FormEvent } from "react";
import type { ActiveVote } from "@/types";

interface VoteCardProps {
  vote: ActiveVote;
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
  selectedOption,
  isSubmitting,
  isSubmitted,
  isRequestLocked,
  submissionError,
  onSelectOption,
  onSubmit,
}: VoteCardProps) {
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
        <p className="mt-6 rounded-2xl border border-slate-300 bg-slate-100 p-5 font-semibold text-slate-800">
          Terima kasih, suara Anda telah dicatat.
        </p>
      ) : (
        <form className="mt-6 space-y-4" onSubmit={onSubmit}>
          <fieldset className="space-y-3">
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
                  disabled={isSubmitting || isRequestLocked}
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
          <button
            className="min-h-14 w-full rounded-2xl bg-slate-900 px-6 font-extrabold text-white transition hover:bg-slate-700 disabled:cursor-wait disabled:bg-slate-300"
            disabled={selectedOption === undefined || isSubmitting}
            type="submit"
          >
            {isSubmitting ? "Mengirim suara..." : "Kirim Suara"}
          </button>
        </form>
      )}
    </article>
  );
}
