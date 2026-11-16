"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import {
  getEssayQuestion,
  getReviewItems,
  submitAnswer,
  submitEssay,
} from "@/lib/api";
import type { Item } from "@/types";
import QuizCard from "./QuizCard";

interface QuizStats {
  correct: number;
  incorrect: number;
}

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan yang tidak diketahui.";
}

export default function LearningSession() {
  const [items, setItems] = useState<Item[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedOption, setSelectedOption] = useState<number | null>(null);
  const [essayAnswer, setEssayAnswer] = useState("");
  const [essayRequestId, setEssayRequestId] = useState<string | null>(null);
  const [isRevealed, setIsRevealed] = useState(false);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState<QuizStats>({ correct: 0, incorrect: 0 });
  const [loadError, setLoadError] = useState<string | null>(null);
  const [submitError, setSubmitError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    Promise.all([getReviewItems(), getEssayQuestion()])
      .then(([{ items: reviewItems }, { item: essayItem }]) => {
        if (active) {
          setItems(essayItem ? [...reviewItems, essayItem] : reviewItems);
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
    if (!item || item.payload.type === "essay" || selectedOption === null || isRevealed) {
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

  async function handleEssaySubmit() {
    if (!item || item.payload.type !== "essay" || !essayAnswer.trim() || isRevealed) {
      return;
    }

    const requestId = essayRequestId ?? crypto.randomUUID();
    setEssayRequestId(requestId);
    setSubmitError(null);
    setLoading(true);

    try {
      await submitEssay(item.id, essayAnswer.trim(), requestId);
      setIsRevealed(true);
      setEssayRequestId(null);
    } catch (error) {
      setSubmitError(getErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }

  function handleNext() {
    setCurrentIndex((index) => index + 1);
    setSelectedOption(null);
    setEssayAnswer("");
    setEssayRequestId(null);
    setIsRevealed(false);
    setSubmitError(null);
  }

  if (loading && items.length === 0) {
    return (
      <main className="flex min-h-screen items-center justify-center px-6">
        <p className="text-lg font-semibold text-text-muted">Memuat sesi belajar...</p>
      </main>
    );
  }

  if (loadError) {
    return (
      <main className="flex min-h-screen items-center justify-center px-6">
        <section className="w-full max-w-lg rounded-3xl border border-red-200 bg-surface p-8 text-center shadow-xl shadow-primary/5">
          <p className="text-sm font-bold uppercase tracking-[0.2em] text-red-600">
            Tidak dapat memuat sesi
          </p>
          <p className="mt-4 text-text-main" role="alert">
            {loadError}
          </p>
          <button
            className="mt-7 min-h-12 rounded-xl bg-primary px-6 font-bold text-surface transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
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
        <section className="w-full max-w-xl rounded-3xl border border-primary-light bg-surface p-8 text-center shadow-xl shadow-primary/5 sm:p-12">
          <div className="mx-auto flex size-20 items-center justify-center rounded-full bg-primary-light text-4xl">
            🎉
          </div>
          <p className="mt-6 text-sm font-bold uppercase tracking-[0.22em] text-primary-hover">
            Sesi belajar
          </p>
          <h1 className="mt-3 text-4xl font-black tracking-tight text-text-main">
            Sesi Selesai
          </h1>
          <p className="mt-3 text-text-muted">Terima kasih sudah belajar hari ini.</p>
          <div className="mt-9 grid grid-cols-3 gap-3">
            <div className="rounded-2xl bg-primary-light p-4">
              <p className="text-2xl font-black text-primary-hover">{score}%</p>
              <p className="mt-1 text-xs font-semibold text-text-muted">Skor</p>
            </div>
            <div className="rounded-2xl bg-primary-light p-4">
              <p className="text-2xl font-black text-primary-hover">{stats.correct}</p>
              <p className="mt-1 text-xs font-semibold text-text-muted">Benar</p>
            </div>
            <div className="rounded-2xl bg-rose-50 p-4">
              <p className="text-2xl font-black text-rose-700">{stats.incorrect}</p>
              <p className="mt-1 text-xs font-semibold text-text-muted">Salah</p>
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
            <p className="text-xs font-black uppercase tracking-[0.24em] text-primary-hover">
              Partai
            </p>
            <p className="mt-1 text-sm font-medium text-text-muted">Belajar satu langkah setiap hari</p>
          </div>
          <div className="flex items-center gap-4">
            <Link
              className="text-sm font-bold text-primary-hover underline decoration-primary underline-offset-4 hover:text-primary"
              href="/"
            >
              ← Kembali ke Beranda
            </Link>
            <div className="rounded-full bg-surface px-4 py-2 text-sm font-bold text-text-muted shadow-sm ring-1 ring-background">
              {currentIndex + 1} <span className="text-text-muted/70">/ {items.length}</span>
            </div>
          </div>
        </header>

        <div
          aria-label={`Progres ${currentIndex} dari ${items.length}`}
          className="mb-8 h-3 overflow-hidden rounded-full bg-primary-light"
          role="progressbar"
          aria-valuemax={items.length}
          aria-valuemin={0}
          aria-valuenow={currentIndex}
        >
          <div
            className="h-full rounded-full bg-primary transition-[width] duration-500"
            style={{ width: `${progress}%` }}
          />
        </div>

        <QuizCard
          essayAnswer={essayAnswer}
          isRevealed={isRevealed}
          item={item}
          loading={loading}
          onEssayAnswerChange={setEssayAnswer}
          onNext={handleNext}
          onSelectOption={setSelectedOption}
          onSubmitAnswer={handleSubmit}
          onSubmitEssay={handleEssaySubmit}
          questionNumber={currentIndex + 1}
          selectedOption={selectedOption}
          submitError={submitError}
        />
      </div>
    </main>
  );
}
