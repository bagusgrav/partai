"use client";

import { useEffect, useRef, useState, type FormEvent } from "react";
import {
  getFaqs,
  submitInquiry,
  type Faq,
} from "@/lib/api/faqs";

const successMessage =
  "Pertanyaan Anda telah dikirim. Pengurus akan segera meninjau.";

export default function TanyaJawabPage() {
  const [faqs, setFaqs] = useState<Faq[]>([]);
  const [isLoadingFaqs, setIsLoadingFaqs] = useState(true);
  const [faqError, setFaqError] = useState<string | null>(null);
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [question, setQuestion] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submissionError, setSubmissionError] = useState<string | null>(null);
  const [submitted, setSubmitted] = useState(false);
  const requestIdRef = useRef<string | null>(null);
  const submittingRef = useRef(false);

  useEffect(() => {
    let isCurrent = true;

    getFaqs()
      .then((result) => {
        if (isCurrent) {
          setFaqs(result);
          setFaqError(null);
        }
      })
      .catch((loadError: unknown) => {
        if (isCurrent) {
          setFaqError(
            loadError instanceof Error
              ? loadError.message
              : "FAQ belum dapat dimuat. Silakan coba kembali.",
          );
        }
      })
      .finally(() => {
        if (isCurrent) {
          setIsLoadingFaqs(false);
        }
      });

    return () => {
      isCurrent = false;
    };
  }, []);

  function handleFieldChange(): void {
    if (!submittingRef.current) {
      requestIdRef.current = null;
      setSubmissionError(null);
      setSubmitted(false);
    }
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();
    if (submittingRef.current) {
      return;
    }

    submittingRef.current = true;
    setIsSubmitting(true);
    setSubmissionError(null);

    const requestId = requestIdRef.current ?? crypto.randomUUID();
    requestIdRef.current = requestId;

    try {
      await submitInquiry({
        request_id: requestId,
        name: name.trim() || undefined,
        email: email.trim() || undefined,
        question: question.trim(),
      });
      setSubmitted(true);
      setName("");
      setEmail("");
      setQuestion("");
      requestIdRef.current = null;
    } catch (submitError: unknown) {
      setSubmissionError(
        submitError instanceof Error
          ? submitError.message
          : "Pertanyaan belum dapat dikirim. Silakan coba kembali.",
      );
    } finally {
      submittingRef.current = false;
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex-1 bg-slate-50">
      <section className="bg-slate-950 text-white">
        <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-amber-300">
            Komunikasi publik
          </p>
          <h1 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
            Tanya Jawab
          </h1>
          <p className="mt-5 max-w-2xl text-lg leading-8 text-slate-300">
            Temukan jawaban atas pertanyaan umum atau kirim pertanyaan baru
            kepada pengurus.
          </p>
        </div>
      </section>

      <div className="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)] lg:px-8">
        <section aria-labelledby="faq-heading">
          <p className="text-sm font-bold uppercase tracking-wide text-slate-500">
            Informasi
          </p>
          <h2
            className="mt-1 text-2xl font-black text-slate-950"
            id="faq-heading"
          >
            Pertanyaan yang sering diajukan
          </h2>

          {isLoadingFaqs ? (
            <p className="mt-6 rounded-xl border border-slate-200 bg-white p-5 text-slate-600">
              Memuat daftar pertanyaan...
            </p>
          ) : faqError ? (
            <p
              className="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 font-semibold text-red-900"
              role="alert"
            >
              {faqError}
            </p>
          ) : faqs.length === 0 ? (
            <p className="mt-6 rounded-xl border border-slate-200 bg-white p-5 text-slate-600">
              Belum ada FAQ yang dipublikasikan.
            </p>
          ) : (
            <div className="mt-6 space-y-3">
              {faqs.map((faq) => (
                <details
                  className="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                  key={faq.id}
                >
                  <summary className="cursor-pointer list-none pr-8 text-base font-bold text-slate-950 marker:hidden focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-900">
                    <span>{faq.question}</span>
                    <span
                      aria-hidden="true"
                      className="float-right -mr-6 text-slate-500 transition group-open:rotate-180"
                    >
                      ▾
                    </span>
                  </summary>
                  <p className="mt-4 whitespace-pre-line border-t border-slate-100 pt-4 text-sm leading-7 text-slate-700">
                    {faq.answer}
                  </p>
                </details>
              ))}
            </div>
          )}
        </section>

        <section aria-labelledby="inquiry-heading">
          <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <p className="text-sm font-bold uppercase tracking-wide text-slate-500">
              Suara Anda
            </p>
            <h2
              className="mt-1 text-2xl font-black text-slate-950"
              id="inquiry-heading"
            >
              Kirim pertanyaan
            </h2>
            <p className="mt-3 text-sm leading-6 text-slate-600">
              Nama dan email bersifat opsional. Pertanyaan Anda akan ditinjau
              pengurus sebelum dijawab.
            </p>

            <form className="mt-6 space-y-5" onSubmit={handleSubmit}>
              <div>
                <label
                  className="mb-2 block text-sm font-bold text-slate-800"
                  htmlFor="inquiry-name"
                >
                  Nama <span className="font-normal text-slate-500">(opsional)</span>
                </label>
                <input
                  autoComplete="name"
                  className="min-h-11 w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:bg-slate-100"
                  disabled={isSubmitting}
                  id="inquiry-name"
                  maxLength={255}
                  onChange={(event) => {
                    handleFieldChange();
                    setName(event.target.value);
                  }}
                  value={name}
                />
              </div>

              <div>
                <label
                  className="mb-2 block text-sm font-bold text-slate-800"
                  htmlFor="inquiry-email"
                >
                  Email <span className="font-normal text-slate-500">(opsional)</span>
                </label>
                <input
                  autoComplete="email"
                  className="min-h-11 w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:bg-slate-100"
                  disabled={isSubmitting}
                  id="inquiry-email"
                  maxLength={255}
                  onChange={(event) => {
                    handleFieldChange();
                    setEmail(event.target.value);
                  }}
                  type="email"
                  value={email}
                />
              </div>

              <div>
                <label
                  className="mb-2 block text-sm font-bold text-slate-800"
                  htmlFor="inquiry-question"
                >
                  Pertanyaan
                </label>
                <textarea
                  className="min-h-36 w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:bg-slate-100"
                  disabled={isSubmitting}
                  id="inquiry-question"
                  maxLength={5000}
                  onChange={(event) => {
                    handleFieldChange();
                    setQuestion(event.target.value);
                  }}
                  required
                  value={question}
                />
              </div>

              {submitted && (
                <p
                  className="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-900"
                  role="status"
                >
                  {successMessage}
                </p>
              )}
              {submissionError && (
                <p
                  className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-900"
                  role="alert"
                >
                  {submissionError}
                </p>
              )}

              <button
                className="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 disabled:cursor-not-allowed disabled:opacity-60"
                disabled={isSubmitting}
                type="submit"
              >
                {isSubmitting ? "Mengirim..." : "Kirim Pertanyaan"}
              </button>
            </form>
          </div>
        </section>
      </div>
    </main>
  );
}
