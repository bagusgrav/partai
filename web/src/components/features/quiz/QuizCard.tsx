import type { Item } from "@/types";

interface QuizCardProps {
  item: Item;
  questionNumber: number;
  selectedOption: number | null;
  essayAnswer: string;
  isRevealed: boolean;
  loading: boolean;
  submitError: string | null;
  onSelectOption(optionIndex: number): void;
  onEssayAnswerChange(answer: string): void;
  onSubmitAnswer(): void;
  onSubmitEssay(): void;
  onNext(): void;
}

export default function QuizCard({
  item,
  questionNumber,
  selectedOption,
  essayAnswer,
  isRevealed,
  loading,
  submitError,
  onSelectOption,
  onEssayAnswerChange,
  onSubmitAnswer,
  onSubmitEssay,
  onNext,
}: QuizCardProps) {
  const correctAnswer = item.payload.type === "essay" ? null : item.payload.answer;

  return (
    <section className="rounded-3xl border border-background bg-surface p-6 shadow-xl shadow-primary/5 sm:p-10">
      <p className="text-sm font-bold uppercase tracking-[0.18em] text-primary-hover">
        Pertanyaan {questionNumber}
      </p>
      <h1 className="mt-4 text-2xl font-extrabold leading-snug tracking-tight text-text-main sm:text-3xl">
        {item.payload.question}
      </h1>

      {item.payload.type === "essay" ? (
        <div className="mt-8">
          <label
            className="mb-2 block text-sm font-bold text-text-main"
            htmlFor="essay-answer"
          >
            Jawaban esai
          </label>
          <textarea
            className="min-h-48 w-full rounded-2xl border-2 border-background bg-surface p-4 leading-relaxed text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary-light disabled:bg-background"
            disabled={isRevealed || loading}
            id="essay-answer"
            onChange={(event) => onEssayAnswerChange(event.target.value)}
            placeholder={item.payload.instructions ?? "Tuliskan jawaban Anda..."}
            value={essayAnswer}
          />
          {isRevealed && (
            <p className="mt-4 rounded-2xl border border-primary-light bg-primary-light p-4 font-semibold text-primary-hover">
              Esai berhasil dikirim dan menunggu penilaian anggota lain.
            </p>
          )}
          {submitError && (
            <p className="mt-4 text-sm font-semibold text-red-700" role="alert">
              Esai belum berhasil dikirim. {submitError}
            </p>
          )}
        </div>
      ) : (
        <div className="mt-8 space-y-3">
          {item.payload.options.map((option, optionIndex) => {
            const isSelected = selectedOption === optionIndex;
            const isCorrect = correctAnswer === optionIndex;
            const revealedStyle = isRevealed
              ? isCorrect
                ? "border-primary bg-primary-light text-primary-hover"
                : isSelected
                  ? "border-rose-400 bg-rose-50 text-rose-900"
                  : "border-background bg-surface text-text-muted"
              : isSelected
                ? "border-primary bg-primary-light text-primary-hover ring-2 ring-primary-light"
                : "border-background bg-surface text-text-main hover:border-primary hover:bg-primary-light";

            return (
              <button
                aria-pressed={isSelected}
                className={`flex min-h-16 w-full items-center gap-4 rounded-2xl border-2 px-4 py-3 text-left font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-default ${revealedStyle}`}
                disabled={isRevealed || loading}
                key={`${item.id}-${optionIndex}`}
                onClick={() => onSelectOption(optionIndex)}
                type="button"
              >
                <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-surface/80 text-sm font-black text-text-muted ring-1 ring-background">
                  {String.fromCharCode(65 + optionIndex)}
                </span>
                <span className="flex-1">{option}</span>
                {isRevealed && isCorrect && (
                  <span aria-label="Jawaban benar" className="font-black text-primary-hover">
                    ✓
                  </span>
                )}
                {isRevealed && isSelected && !isCorrect && (
                  <span aria-label="Jawaban salah" className="font-black text-rose-700">
                    ✕
                  </span>
                )}
              </button>
            );
          })}
        </div>
      )}

      {isRevealed && item.payload.type !== "essay" && (
        <div
          aria-live="polite"
          className={`mt-7 rounded-2xl border p-5 ${
            selectedOption === correctAnswer
              ? "border-primary-light bg-primary-light"
              : "border-amber-200 bg-amber-50"
          }`}
        >
          <h2
            className={`font-extrabold ${
              selectedOption === correctAnswer
                ? "text-primary-hover"
                : "text-amber-900"
            }`}
          >
            {selectedOption === correctAnswer ? "Benar!" : "Belum tepat"}
          </h2>
          <p className="mt-2 leading-relaxed text-text-main">
            {item.payload.explanation}
          </p>
          {item.payload.sources.length > 0 && (
            <ul className="mt-4 space-y-2 border-t border-background/80 pt-4 text-sm">
              {item.payload.sources.map((source, sourceIndex) => (
                <li key={`${source.citation}-${sourceIndex}`}>
                  {source.url ? (
                    <a
                      className="font-semibold text-primary-hover underline decoration-primary underline-offset-4 hover:text-primary"
                      href={source.url}
                      rel="noreferrer"
                      target="_blank"
                    >
                      {source.citation}
                    </a>
                  ) : (
                    <span className="text-text-muted">{source.citation}</span>
                  )}
                </li>
              ))}
            </ul>
          )}
          {submitError && (
            <p className="mt-4 text-sm font-semibold text-red-700" role="alert">
              {submitError}
            </p>
          )}
        </div>
      )}

      <div className="mt-8 border-t border-background pt-6">
        {!isRevealed ? (
          <button
            className="min-h-14 w-full rounded-2xl bg-primary px-6 text-base font-extrabold text-surface shadow-lg shadow-primary/20 transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:bg-background disabled:shadow-none"
            disabled={
              loading ||
              (item.payload.type === "essay"
                ? !essayAnswer.trim()
                : selectedOption === null)
            }
            onClick={item.payload.type === "essay" ? onSubmitEssay : onSubmitAnswer}
            type="button"
          >
            {item.payload.type === "essay" ? "Kirim Esai" : "Kirim Jawaban"}
          </button>
        ) : (
          <button
            className="min-h-14 w-full rounded-2xl bg-primary px-6 text-base font-extrabold text-surface shadow-lg shadow-primary/20 transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-wait disabled:bg-primary-light"
            disabled={loading}
            onClick={onNext}
            type="button"
          >
            {loading ? "Menyimpan jawaban..." : "Lanjut"}
          </button>
        )}
      </div>
    </section>
  );
}
