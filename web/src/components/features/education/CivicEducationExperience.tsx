"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import {
  ArrowRight,
  BookOpenCheck,
  CalendarClock,
  Check,
  CircleHelp,
  ExternalLink,
  Landmark,
  RotateCcw,
  ShieldCheck,
} from "lucide-react";
import { getCurrentUser, SSO_URL } from "@/lib/api/auth";
import {
  CIVIC_DOMAINS,
  getEducationOverview,
  getEducationSession,
  submitEducationAnswer,
  type AnswerOptionKey,
  type CivicDomain,
  type CivicQuestion,
  type EducationAnswerResponse,
  type EducationOverview,
} from "@/lib/api/education";
import type { RegisteredUser } from "@/types";

const DOMAIN_LABELS: Record<CivicDomain, string> = {
  hak_konstitusional: "Hak Konstitusional",
  alasan_bernegara: "Alasan Bernegara",
  keuangan_negara: "Keuangan Negara",
  hukum: "Hukum",
  politik_bernegara: "Politik Bernegara",
};

const MASTERY_STEPS = ["Kenali", "Pahami", "Terapkan", "Kritisi"];
function errorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Terjadi kesalahan saat memuat materi.";
}

export default function CivicEducationExperience() {
  const arenaRef = useRef<HTMLElement>(null);
  const [overview, setOverview] = useState<EducationOverview | null>(null);
  const [user, setUser] = useState<RegisteredUser | null>(null);
  const [sessionQuestions, setSessionQuestions] = useState<CivicQuestion[]>([]);
  const [masteryScores, setMasteryScores] = useState<Record<CivicDomain, number>>({
    hak_konstitusional: 0,
    alasan_bernegara: 0,
    keuangan_negara: 0,
    hukum: 0,
    politik_bernegara: 0,
  });
  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedOption, setSelectedOption] = useState<AnswerOptionKey | null>(null);
  const [confidence, setConfidence] = useState<"ragu" | "yakin">("ragu");
  const [requestId, setRequestId] = useState<string | null>(null);
  const [answer, setAnswer] = useState<EducationAnswerResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    async function loadEducation(): Promise<void> {
      try {
        const [publicOverview, currentUser] = await Promise.all([
          getEducationOverview(),
          getCurrentUser(),
        ]);
        if (!active) return;

        setOverview(publicOverview);
        setUser(currentUser);

        if (currentUser.role === "member") {
          const memberSession = await getEducationSession();
          if (active) {
            setSessionQuestions(memberSession.questions);
            setMasteryScores(memberSession.mastery_scores);
          }
        }
      } catch (loadError: unknown) {
        if (active) setError(errorMessage(loadError));
      } finally {
        if (active) setLoading(false);
      }
    }

    void loadEducation();
    return () => {
      active = false;
    };
  }, []);

  const isMember = user?.role === "member";
  const questions = isMember
    ? sessionQuestions
    : overview?.preview_questions ?? [];
  const question = questions[currentIndex];
  const context = overview?.daily_digest.context;
  const totalMaterialCount = CIVIC_DOMAINS.reduce(
    (total, domain) => total + (overview?.distribution[domain]?.count ?? 0),
    0,
  );

  function startPractice(): void {
    arenaRef.current?.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  async function submitAnswer(): Promise<void> {
    if (!question || !selectedOption || answer || submitting) return;
    const stableRequestId = requestId ?? crypto.randomUUID();
    setRequestId(stableRequestId);
    setSubmitting(true);
    setError(null);
    try {
      const result = await submitEducationAnswer(
        question.id,
        selectedOption,
        confidence,
        stableRequestId,
      );
      setAnswer(result);
      setRequestId(null);
      if (result.updated_reputation) {
        setMasteryScores(result.updated_reputation.mastery_scores);
      }
    } catch (submitError: unknown) {
      setError(errorMessage(submitError));
    } finally {
      setSubmitting(false);
    }
  }

  function showNextQuestion(): void {
    setCurrentIndex((index) => index + 1);
    setSelectedOption(null);
    setConfidence("ragu");
    setRequestId(null);
    setAnswer(null);
    setError(null);
  }

  return (
    <main className="min-h-screen bg-slate-50 px-4 py-8 text-slate-950 sm:px-6 sm:py-12">
      <div className="mx-auto max-w-6xl">
        <header className="mb-9 flex flex-wrap items-end justify-between gap-5">
          <div>
            <p className="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">
              Kurikulum warga
            </p>
            <h1 className="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
              Belajar kewargaan
            </h1>
            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
              Pahami konstitusi melalui skenario netral, sumber primer, dan latihan yang membangun penguasaan.
            </p>
          </div>
          <Link
            className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:bg-white"
            href="/"
          >
            Kembali ke beranda
          </Link>
        </header>

        {error && (
          <p className="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            {error}
          </p>
        )}

        {loading || !overview ? (
          <div className="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">
            Memuat kurikulum dan materi hari ini...
          </div>
        ) : (
          <>
            <section
              aria-labelledby="daily-digest-title"
              className="overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 text-white shadow-xl shadow-slate-950/10"
            >
              <div className="grid gap-8 p-6 sm:p-9 lg:grid-cols-[1fr_0.72fr] lg:items-center">
                <div>
                  <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    <Landmark aria-hidden="true" className="size-4" />
                    Daily Digest · {overview.daily_digest.date}
                  </p>
                  <h2 className="mt-4 text-2xl font-bold leading-tight sm:text-3xl" id="daily-digest-title">
                    {overview.daily_digest.featured_question?.prompt ?? "Sorotan konstitusional harian"}
                  </h2>
                  <p className="mt-4 max-w-2xl text-sm leading-6 text-slate-300">
                    Latihan ini menghubungkan prinsip konstitusi dengan pengawasan publik atas kebijakan dan komitmen yang dapat diperiksa.
                  </p>
                  <button
                    className="mt-6 inline-flex min-h-11 items-center gap-2 rounded-lg bg-white px-5 text-sm font-bold text-slate-950 transition hover:bg-slate-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    onClick={startPractice}
                    type="button"
                  >
                    Mulai latihan cepat <ArrowRight aria-hidden="true" className="size-4" />
                  </button>
                </div>
                <div className="rounded-2xl border border-white/15 bg-white/[0.06] p-5">
                  <p className="text-sm font-bold text-white">Konteks pengawasan publik</p>
                  <div className="mt-4 flex items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <span className="text-sm text-slate-300">Kontrak Faksi berjalan</span>
                    <span className="rounded-full bg-white/10 px-3 py-1 text-sm font-bold">
                      {context?.active_faction_contracts ?? 0}
                    </span>
                  </div>
                  <div className="pt-4">
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                      Agenda Gerak Warga mendatang
                    </p>
                    {context?.upcoming_civic_agendas.length ? (
                      <ul className="mt-2 space-y-2">
                        {context.upcoming_civic_agendas.map((agenda) => (
                          <li className="flex gap-2 text-sm text-slate-200" key={agenda.id}>
                            <CalendarClock aria-hidden="true" className="mt-0.5 size-4 shrink-0 text-slate-400" />
                            <span>{agenda.title}</span>
                          </li>
                        ))}
                      </ul>
                    ) : (
                      <p className="mt-2 text-sm text-slate-400">
                        Belum ada agenda mendatang yang dipublikasikan.
                      </p>
                    )}
                  </div>
                </div>
              </div>
            </section>

            <section aria-labelledby="mastery-title" className="mt-10">
              <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                  <p className="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                    Peta kurikulum
                  </p>
                  <h2 className="mt-1 text-2xl font-bold" id="mastery-title">
                    Penguasaan lima domain
                  </h2>
                </div>
                {!isMember && (
                  <p className="text-sm text-slate-500">Masuk sebagai anggota untuk menyimpan progres.</p>
                )}
              </div>
              {totalMaterialCount === 0 && (
                <p
                  className="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                  role="status"
                >
                  Materi Segera Hadir. Bank soal kewargaan sedang disiapkan.
                </p>
              )}
              {totalMaterialCount > 0 && (
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                  {CIVIC_DOMAINS.filter(
                    (domain) => (overview.distribution[domain]?.count ?? 0) > 0,
                  ).map((domain) => {
                  const domainOverview = overview.distribution[domain];
                  const progressCapacity = Math.max(
                    1,
                    Math.min(100, domainOverview.mastery_points),
                  );
                  const score = Math.max(
                    0,
                    Math.min(
                      100,
                      Math.round(
                        (masteryScores[domain] ?? 0) / progressCapacity * 100,
                      ),
                    ),
                  );
                  const levelReached = score >= 80 ? 4 : score >= 60 ? 3 : score >= 40 ? 2 : score > 0 ? 1 : 0;
                  return (
                    <article className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" key={domain}>
                      <div className="flex items-start justify-between gap-3">
                        <div>
                          <h3 className="font-bold">{DOMAIN_LABELS[domain]}</h3>
                          <p className="mt-1 text-xs text-slate-500">
                            {domainOverview.count} materi tersedia
                          </p>
                        </div>
                        <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                          {score}/100
                        </span>
                      </div>
                      <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div className="h-full rounded-full bg-slate-800" style={{ width: `${score}%` }} />
                      </div>
                      <ol className="mt-4 grid grid-cols-4 gap-1">
                        {MASTERY_STEPS.map((step, index) => (
                          <li
                            className={`rounded-md px-1 py-1.5 text-center text-[10px] font-semibold sm:text-xs ${
                              index < levelReached
                                ? "bg-slate-800 text-white"
                                : "bg-slate-100 text-slate-500"
                            }`}
                            key={step}
                          >
                            {step}
                          </li>
                        ))}
                      </ol>
                    </article>
                  );
                  })}
                </div>
              )}
            </section>

            <section
              aria-labelledby="practice-title"
              className="mt-10 scroll-mt-24 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8"
              ref={arenaRef}
            >
              <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                    <BookOpenCheck aria-hidden="true" className="size-4" />
                    {isMember ? "Mode anggota · ulasan SM-2" : "Mode pratinjau tamu"}
                  </p>
                  <h2 className="mt-2 text-2xl font-bold" id="practice-title">Arena latihan</h2>
                </div>
                {question && (
                  <span className="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    Soal {currentIndex + 1} dari {questions.length}
                    {question.is_due ? " · ulasan jatuh tempo" : ""}
                  </span>
                )}
              </div>

              {!isMember && (
                <div className="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 p-3">
                  <p className="flex gap-2 text-sm leading-5 text-slate-600">
                    <CircleHelp aria-hidden="true" className="mt-0.5 size-4 shrink-0" />
                    Coba soal publik tanpa menyimpan progres atau skor reputasi.
                  </p>
                  <div className="flex shrink-0 gap-2">
                    <a className="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700" href={SSO_URL}>
                      Masuk
                    </a>
                    <a className="rounded-lg border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-white" href="/daftar">
                      Gabung Anggota
                    </a>
                  </div>
                </div>
              )}

              {question ? (
                <div className="mt-7">
                  <div className="flex flex-wrap gap-2 text-xs font-semibold text-slate-500">
                    <span className="rounded-full border border-slate-200 px-2.5 py-1">
                      {DOMAIN_LABELS[question.domain]}
                    </span>
                    <span className="rounded-full border border-slate-200 px-2.5 py-1">
                      Level {question.mastery_level} · {MASTERY_STEPS[question.mastery_level - 1]}
                    </span>
                  </div>
                  <p className="mt-4 text-lg font-semibold leading-7">{question.prompt}</p>
                  <div className="mt-5 grid gap-3">
                    {question.options.map((option) => {
                      const correctChoice = answer?.correct_option === option.key;
                      const selectedChoice = selectedOption === option.key;
                      return (
                        <button
                          aria-pressed={selectedChoice}
                          className={`flex min-h-14 items-start gap-3 rounded-xl border p-4 text-left text-sm leading-6 transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-800 ${
                            correctChoice
                              ? "border-emerald-700 bg-emerald-50 text-emerald-950"
                              : selectedChoice
                                ? "border-slate-800 bg-slate-100"
                                : "border-slate-200 hover:border-slate-400 hover:bg-slate-50"
                          }`}
                          disabled={Boolean(answer) || submitting}
                          key={option.key}
                          onClick={() => setSelectedOption(option.key)}
                          type="button"
                        >
                          <span className="flex size-6 shrink-0 items-center justify-center rounded-full border border-current text-xs font-bold">
                            {correctChoice ? <Check aria-hidden="true" className="size-4" /> : option.key}
                          </span>
                          <span>{option.text}</span>
                        </button>
                      );
                    })}
                  </div>

                  {!answer ? (
                    <div className="mt-5 flex flex-wrap items-center justify-between gap-4">
                      <div aria-label="Tingkat keyakinan" className="flex items-center gap-2">
                        <span className="mr-1 text-xs font-semibold text-slate-500">Keyakinan:</span>
                        {(["ragu", "yakin"] as const).map((value) => (
                          <button
                            aria-pressed={confidence === value}
                            className={`rounded-lg px-3 py-2 text-xs font-bold ${
                              confidence === value ? "bg-slate-800 text-white" : "bg-slate-100 text-slate-600"
                            }`}
                            key={value}
                            onClick={() => setConfidence(value)}
                            type="button"
                          >
                            {value === "ragu" ? "Masih ragu" : "Yakin"}
                          </button>
                        ))}
                      </div>
                      <button
                        className="min-h-11 rounded-lg bg-slate-950 px-5 text-sm font-bold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled={!selectedOption || submitting}
                        onClick={() => void submitAnswer()}
                        type="button"
                      >
                        {submitting ? "Memeriksa..." : "Periksa jawaban"}
                      </button>
                    </div>
                  ) : (
                    <div className="mt-6 space-y-4">
                      <p className={`font-bold ${answer.correct ? "text-emerald-800" : "text-amber-800"}`}>
                        {answer.correct ? "Tepat." : "Belum tepat."}
                        {answer.mastery_awarded && ` +${answer.points_delta} Mastery Score`}
                      </p>
                      <div className="rounded-2xl border-l-4 border-slate-800 bg-slate-50 p-5">
                        <p className="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-slate-600">
                          <ShieldCheck aria-hidden="true" className="size-4" />
                          Sitasi hukum primer
                        </p>
                        <p className="mt-2 font-semibold leading-6">{answer.source_citation}</p>
                        {answer.source_url && (
                          <a
                            className="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-slate-700 underline underline-offset-2"
                            href={answer.source_url}
                            rel="noreferrer"
                            target="_blank"
                          >
                            Buka rujukan resmi <ExternalLink aria-hidden="true" className="size-3.5" />
                          </a>
                        )}
                        <p className="mt-3 text-sm leading-6 text-slate-700">{answer.explanation}</p>
                      </div>
                      {isMember && (
                        <p className="flex items-center gap-2 text-sm text-slate-600">
                          <RotateCcw aria-hidden="true" className="size-4" />
                          Ulas kembali {answer.next_review_at
                            ? new Date(answer.next_review_at).toLocaleString("id-ID", {
                                dateStyle: "medium",
                                timeStyle: "short",
                              })
                            : "sesuai jadwal berikutnya"}
                        </p>
                      )}
                      <button
                        className="min-h-11 rounded-lg border border-slate-300 px-5 text-sm font-bold text-slate-800 transition hover:bg-slate-100"
                        onClick={showNextQuestion}
                        type="button"
                      >
                        Soal berikutnya
                      </button>
                    </div>
                  )}
                </div>
              ) : (
                <div className="mt-7 rounded-2xl bg-slate-50 p-6 text-center">
                  <p className="font-semibold text-slate-800">
                    {questions.length === 0
                      ? "Belum ada materi yang siap dilatihkan."
                      : "Latihan hari ini selesai. Penguasaan tumbuh lewat belajar yang konsisten."}
                  </p>
                  {!isMember && (
                    <div className="mt-4 flex flex-wrap justify-center gap-3">
                      <a
                        className="rounded-lg bg-slate-950 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700"
                        href={SSO_URL}
                      >
                        Masuk
                      </a>
                      <a
                        className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-white"
                        href="/daftar"
                      >
                        Gabung Anggota
                      </a>
                    </div>
                  )}
                </div>
              )}
            </section>
          </>
        )}
      </div>
    </main>
  );
}
