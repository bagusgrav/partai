"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useState, type FormEvent } from "react";
import { resetPassword } from "@/lib/api";

const LOGIN_URL = "https://id.akuhadir.my.id";

export default function ResetPasswordForm() {
  const searchParams = useSearchParams();
  const token = searchParams.get("token") ?? "";
  const initialEmail = searchParams.get("email") ?? "";
  const [email, setEmail] = useState(initialEmail);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState<string | null>(
    token && initialEmail
      ? null
      : "Tautan reset tidak lengkap. Silakan minta tautan reset yang baru.",
  );

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    const formData = new FormData(event.currentTarget);
    const password = String(formData.get("password") ?? "");
    const passwordConfirmation = String(
      formData.get("password_confirmation") ?? "",
    );

    try {
      await resetPassword({
        token,
        email: email.trim(),
        password,
        password_confirmation: passwordConfirmation,
      });
      setSuccess(true);
      window.setTimeout(() => window.location.assign(LOGIN_URL), 1500);
    } catch (submitError: unknown) {
      setError(
        submitError instanceof Error
          ? submitError.message
          : "Kata sandi gagal diubah. Periksa kembali tautan dan isian Anda, lalu coba lagi.",
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex-1 px-5 py-10 sm:px-8 sm:py-14">
      <section className="mx-auto max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-surface shadow-xl shadow-primary/5">
        <header className="bg-primary px-6 py-8 text-white sm:px-10">
          <p className="text-sm font-bold uppercase tracking-[0.16em] text-white/80">
            Keamanan akun
          </p>
          <h1 className="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
            Buat Password Baru
          </h1>
          <p className="mt-3 leading-7 text-white/90">
            Gunakan kata sandi baru yang kuat untuk melindungi akun PARTAI Anda.
          </p>
        </header>

        <form className="space-y-5 p-6 sm:p-10" onSubmit={handleSubmit}>
          {success && (
            <div
              className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
              role="status"
            >
              Kata sandi berhasil diubah. Anda akan diarahkan ke halaman masuk.
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
              onChange={(event) => setEmail(event.target.value)}
              required
              type="email"
              value={email}
            />
          </div>

          <div>
            <label
              className="mb-2 block text-sm font-bold text-text-main"
              htmlFor="password"
            >
              Password Baru
            </label>
            <input
              autoComplete="new-password"
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"
              id="password"
              minLength={8}
              name="password"
              required
              type="password"
            />
            <p className="mt-2 text-xs text-text-muted">
              Gunakan minimal 8 karakter.
            </p>
          </div>

          <div>
            <label
              className="mb-2 block text-sm font-bold text-text-main"
              htmlFor="password_confirmation"
            >
              Konfirmasi Password Baru
            </label>
            <input
              autoComplete="new-password"
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"
              id="password_confirmation"
              minLength={8}
              name="password_confirmation"
              required
              type="password"
            />
          </div>

          <button
            className="w-full rounded-xl bg-primary px-5 py-3.5 font-bold text-white transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isSubmitting || !token || !initialEmail || success}
            type="submit"
          >
            {isSubmitting ? "Menyimpan..." : "Simpan Password Baru"}
          </button>

          <p className="text-center text-sm text-text-muted">
            <Link
              className="font-semibold text-primary hover:underline"
              href={LOGIN_URL}
            >
              Kembali ke halaman masuk
            </Link>
          </p>
        </form>
      </section>
    </main>
  );
}
