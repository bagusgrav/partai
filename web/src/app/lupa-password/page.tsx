"use client";

import Link from "next/link";
import { useState, type FormEvent } from "react";
import { forgotPassword } from "@/lib/api";

export default function ForgotPasswordPage() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setMessage(null);
    setIsSubmitting(true);

    const formData = new FormData(event.currentTarget);
    const email = String(formData.get("email") ?? "").trim();

    try {
      await forgotPassword(email);
      setMessage(
        "Jika alamat email terdaftar, tautan untuk mengatur ulang kata sandi akan segera dikirim.",
      );
    } catch {
      setError("Permintaan gagal dikirim. Silakan coba beberapa saat lagi.");
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex-1 px-5 py-10 sm:px-8 sm:py-14">
      <section className="mx-auto max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-surface shadow-xl shadow-primary/5">
        <header className="bg-primary px-6 py-8 text-white sm:px-10">
          <p className="text-sm font-bold uppercase tracking-[0.16em] text-white/80">
            Bantuan akun
          </p>
          <h1 className="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
            Lupa Password?
          </h1>
          <p className="mt-3 leading-7 text-white/90">
            Masukkan email akun Anda. Jika terdaftar, kami akan mengirim tautan
            untuk membuat kata sandi baru.
          </p>
        </header>

        <form className="space-y-5 p-6 sm:p-10" onSubmit={handleSubmit}>
          {message && (
            <div
              className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
              role="status"
            >
              {message}
            </div>
          )}
          {error && (
            <div
              className="rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800"
              role="alert"
            >
              {error}
            </div>
          )}

          <div>
            <label
              className="mb-2 block text-sm font-bold text-text-main"
              htmlFor="email"
            >
              Email
            </label>
            <input
              autoComplete="email"
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"
              id="email"
              maxLength={255}
              name="email"
              required
              type="email"
            />
          </div>

          <button
            className="w-full rounded-xl bg-primary px-5 py-3.5 font-bold text-white transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isSubmitting}
            type="submit"
          >
            {isSubmitting ? "Mengirim..." : "Kirim Tautan Reset"}
          </button>

          <p className="text-center text-sm text-text-muted">
            <Link
              className="font-semibold text-primary hover:underline"
              href="https://id.akuhadir.my.id"
            >
              Kembali ke halaman masuk
            </Link>
          </p>
        </form>
      </section>
    </main>
  );
}
