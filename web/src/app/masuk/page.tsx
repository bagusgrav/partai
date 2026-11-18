"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState, type FormEvent } from "react";
import { loginMember } from "@/lib/api/auth";

export default function LoginPage() {
  const router = useRouter();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    const formData = new FormData(event.currentTarget);

    try {
      await loginMember({
        email: String(formData.get("email") ?? "").trim(),
        password: String(formData.get("password") ?? ""),
      });
      router.push("/profil");
      router.refresh();
    } catch (loginError: unknown) {
      setError(
        loginError instanceof Error
          ? loginError.message
          : "Masuk gagal. Silakan coba lagi.",
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="flex-1 px-5 py-10 sm:px-8 sm:py-14">
      <section className="mx-auto max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-surface shadow-xl shadow-primary/5">
        <header className="bg-primary px-6 py-8 text-white sm:px-10">
          <p className="text-sm font-bold uppercase tracking-[0.16em] text-white/80">
            Selamat datang kembali
          </p>
          <h1 className="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
            Masuk ke PARTAI
          </h1>
          <p className="mt-3 max-w-lg leading-7 text-white/90">
            Gunakan email dan kata sandi yang terdaftar untuk melanjutkan
            aktivitas dan melihat progres Anda.
          </p>
        </header>

        <form className="space-y-5 p-6 sm:p-10" onSubmit={handleSubmit}>
          {error && (
            <div
              className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
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

          <div>
            <label
              className="mb-2 block text-sm font-bold text-text-main"
              htmlFor="password"
            >
              Password
            </label>
            <input
              autoComplete="current-password"
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"
              id="password"
              name="password"
              required
              type="password"
            />
          </div>

          <button
            className="w-full rounded-xl bg-primary px-5 py-3.5 font-bold text-white transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isSubmitting}
            type="submit"
          >
            {isSubmitting ? "Memeriksa..." : "Masuk"}
          </button>

          <div className="flex flex-col items-center gap-3 text-center text-sm text-text-muted sm:flex-row sm:justify-between">
            <Link
              className="font-semibold text-primary hover:underline"
              href="/lupa-password"
            >
              Lupa kata sandi?
            </Link>
            <p>
              Belum menjadi anggota?{" "}
              <Link
                className="font-semibold text-primary hover:underline"
                href="/daftar"
              >
                Daftar
              </Link>
            </p>
          </div>
        </form>
      </section>
    </main>
  );
}
