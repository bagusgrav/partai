"use client";

import { useEffect, useState } from "react";
import { getReviewItems, submitAnswer } from "@/lib/api";
import type { Item } from "@/types";

interface QuizStats {
  correct: number;
  incorrect: number;
}

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function Home() {
  const [items, setItems] = useState<Item[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedOption, setSelectedOption] = useState<number | null>(null);
  const [isRevealed, setIsRevealed] = useState(false);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<QuizStats>({ correct: 0, incorrect: 0 });
  const [loadError, setLoadError] = useState<string | null>(null);
  const [submitError, setSubmitError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    getReviewItems()
      .then(({ items: reviewItems }) => {
        if (active) {
          setItems(reviewItems);
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setLoadError(getErrorMessage(error));
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

  const item = items[currentIndex];
  const progress = items.length
    ? Math.min((currentIndex / items.length) * 100, 100)
    : 0;

  async function handleSubmit() {
    if (!item || selectedOption === null || isRevealed) {
      return;
    }

    const correct = selectedOption === item.payload.answer;
    setIsRevealed(true);
    setSubmitError(null);
    setStats((currentStats) => ({
      correct: currentStats.correct + (correct ? 1 : 0),
      incorrect: currentStats.incorrect + (correct ? 0 : 1),
    }));
    setLoading(true);

    try {
      await submitAnswer(item.id, correct);
    } catch (error) {
      setSubmitError(
        `Jawaban ditampilkan, tetapi gagal disimpan ke server. ${getErrorMessage(error)}`,
      );
    } finally {
      setLoading(false);
    }
  }

  function handleNext() {
    setCurrentIndex((index) => index + 1);
    setSelectedOption(null);
    setIsRevealed(false);
    setSubmitError(null);
  }

  if (loading && items.length === 0) {
    return (
      <main className="flex min-h-screen items-center justify-center px-6">
        <p className="text-lg font-semibold text-slate-600">Memuat sesi belajar...</p>
      </main>
    );
  }

  if (loadError) {
    return (
      <main className="flex min-h-screen items-center justify-center px-6">
        <section className="w-full max-w-lg rounded-3xl border border-red-200 bg-white p-8 text-center shadow-xl shadow-slate-200/60">
          <p className="text-sm font-bold uppercase tracking-[0.2em] text-red-600">
            Tidak dapat memuat sesi
          </p>
          <p className="mt-4 text-slate-700" role="alert">
            {loadError}
          </p>
          <button
            className="mt-7 min-h-12 rounded-xl bg-emerald-600 px-6 font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
            onClick={() => window.location.reload()}
            type="button"
          >
            Coba lagi
          </button>
        </section>
      </main>
    );
  }

  if (currentIndex >= items.length) {
    const total = stats.correct + stats.incorrect;
    const score = total ? Math.round((stats.correct / total) * 100) : 0;

    return (
      <main className="flex min-h-screen items-center justify-center px-6 py-12">
        <section className="w-full max-w-xl rounded-3xl border border-emerald-100 bg-white p-8 text-center shadow-xl shadow-emerald-900/5 sm:p-12">
          <div className="mx-auto flex size-20 items-center justify-center rounded-full bg-emerald-100 text-4xl">
            🎉
          </div>
          <p className="mt-6 text-sm font-bold uppercase tracking-[0.22em] text-emerald-700">
            Sesi belajar
          </p>
          <h1 className="mt-3 text-4xl font-black tracking-tight text-slate-900">
            Sesi Selesai
          </h1>
          <p className="mt-3 text-slate-600">Terima kasih sudah belajar hari ini.</p>
          <div className="mt-9 grid grid-cols-3 gap-3">
            <div className="rounded-2xl bg-emerald-50 p-4">
              <p className="text-2xl font-black text-emerald-700">{score}%</p>
              <p className="mt-1 text-xs font-semibold text-slate-600">Skor</p>
            </div>
            <div className="rounded-2xl bg-emerald-50 p-4">
              <p className="text-2xl font-black text-emerald-700">{stats.correct}</p>
              <p className="mt-1 text-xs font-semibold text-slate-600">Benar</p>
            </div>
            <div className="rounded-2xl bg-rose-50 p-4">
              <p className="text-2xl font-black text-rose-700">{stats.incorrect}</p>
              <p className="mt-1 text-xs font-semibold text-slate-600">Salah</p>
            </div>
          </div>
        </section>
      </main>
    );
  }

  return (
    <main className="min-h-screen px-4 py-8 sm:px-6 sm:py-12">
      <div className="mx-auto w-full max-w-3xl">
        <header className="mb-10 flex items-center justify-between gap-4">
          <div>
            <p className="text-xs font-black uppercase tracking-[0.24em] text-emerald-700">
              Partai
            </p>
            <p className="mt-1 text-sm font-medium text-slate-500">Belajar satu langkah setiap hari</p>
          </div>
          <div className="rounded-full bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm ring-1 ring-slate-200">
            {currentIndex + 1} <span className="text-slate-400">/ {items.length}</span>
          </div>
        </header>

        <div
          aria-label={`Progres ${currentIndex} dari ${items.length}`}
          className="mb-8 h-3 overflow-hidden rounded-full bg-emerald-100"
          role="progressbar"
          aria-valuemax={items.length}
          aria-valuemin={0}
          aria-valuenow={currentIndex}
        >
          <div
            className="h-full rounded-full bg-emerald-500 transition-[width] duration-500"
            style={{ width: `${progress}%` }}
          />
        </div>

        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-10">
          <p className="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
            Pertanyaan {currentIndex + 1}
          </p>
          <h1 className="mt-4 text-2xl font-extrabold leading-snug tracking-tight text-slate-900 sm:text-3xl">
            {item.payload.question}
          </h1>

          <div className="mt-8 space-y-3">
            {item.payload.options.map((option, optionIndex) => {
              const isSelected = selectedOption === optionIndex;
              const isCorrect = item.payload.answer === optionIndex;
              const revealedStyle = isRevealed
                ? isCorrect
                  ? "border-emerald-500 bg-emerald-50 text-emerald-900"
                  : isSelected
                    ? "border-rose-400 bg-rose-50 text-rose-900"
                    : "border-slate-200 bg-white text-slate-500"
                : isSelected
                  ? "border-emerald-500 bg-emerald-50 text-emerald-900 ring-2 ring-emerald-100"
                  : "border-slate-200 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50/50";

              return (
                <button
                  aria-pressed={isSelected}
                  className={`flex min-h-16 w-full items-center gap-4 rounded-2xl border-2 px-4 py-3 text-left font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:cursor-default ${revealedStyle}`}
                  disabled={isRevealed || loading}
                  key={`${item.id}-${optionIndex}`}
                  onClick={() => setSelectedOption(optionIndex)}
                  type="button"
                >
                  <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white/80 text-sm font-black text-slate-500 ring-1 ring-slate-200">
                    {String.fromCharCode(65 + optionIndex)}
                  </span>
                  <span className="flex-1">{option}</span>
                  {isRevealed && isCorrect && (
                    <span aria-label="Jawaban benar" className="font-black text-emerald-700">
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

          {isRevealed && (
            <div
              aria-live="polite"
              className={`mt-7 rounded-2xl border p-5 ${
                selectedOption === item.payload.answer
                  ? "border-emerald-200 bg-emerald-50"
                  : "border-amber-200 bg-amber-50"
              }`}
            >
              <h2
                className={`font-extrabold ${
                  selectedOption === item.payload.answer
                    ? "text-emerald-800"
                    : "text-amber-900"
                }`}
              >
                {selectedOption === item.payload.answer ? "Benar!" : "Belum tepat"}
              </h2>
              <p className="mt-2 leading-relaxed text-slate-700">
                {item.payload.explanation}
              </p>
              {item.payload.sources.length > 0 && (
                <ul className="mt-4 space-y-2 border-t border-slate-200/80 pt-4 text-sm">
                  {item.payload.sources.map((source, sourceIndex) => (
                    <li key={`${source.citation}-${sourceIndex}`}>
                      {source.url ? (
                        <a
                          className="font-semibold text-emerald-800 underline decoration-emerald-300 underline-offset-4 hover:text-emerald-950"
                          href={source.url}
                          rel="noreferrer"
                          target="_blank"
                        >
                          {source.citation}
                        </a>
                      ) : (
                        <span className="text-slate-600">{source.citation}</span>
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

          <div className="mt-8 border-t border-slate-100 pt-6">
            {!isRevealed ? (
              <button
                className="min-h-14 w-full rounded-2xl bg-emerald-600 px-6 text-base font-extrabold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                disabled={selectedOption === null || loading}
                onClick={handleSubmit}
                type="button"
              >
                Kirim Jawaban
              </button>
            ) : (
              <button
                className="min-h-14 w-full rounded-2xl bg-emerald-600 px-6 text-base font-extrabold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 disabled:cursor-wait disabled:bg-emerald-400"
                disabled={loading}
                onClick={handleNext}
                type="button"
              >
                {loading ? "Menyimpan jawaban..." : "Lanjut"}
              </button>
            )}
          </div>
        </section>
      </div>
    </main>
  );
}
