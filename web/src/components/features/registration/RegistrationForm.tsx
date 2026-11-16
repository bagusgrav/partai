"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useRef, useState, type FormEvent } from "react";
import { getWelfareRegions, registerMember } from "@/lib/api";
import type { WelfareRegion } from "@/types";

interface RegistrationInput {
  name: string;
  email: string;
  password: string;
  cluster_id: string;
  nik?: string;
}

export default function RegistrationForm() {
  const router = useRouter();
  const [regions, setRegions] = useState<WelfareRegion[]>([]);
  const [isLoadingRegions, setIsLoadingRegions] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const pendingRequest = useRef<{ fingerprint: string; id: string } | null>(null);

  useEffect(() => {
    let active = true;

    getWelfareRegions()
      .then(({ regions: availableRegions }) => {
        if (active) {
          setRegions(availableRegions);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(
            loadError instanceof Error
              ? `Wilayah gagal dimuat. ${loadError.message}`
              : "Wilayah gagal dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setIsLoadingRegions(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError(null);
    setIsSubmitting(true);

    const formData = new FormData(event.currentTarget);
    const input: RegistrationInput = {
      name: String(formData.get("name") ?? "").trim(),
      email: String(formData.get("email") ?? "").trim(),
      password: String(formData.get("password") ?? ""),
      cluster_id: String(formData.get("cluster_id") ?? ""),
      nik: String(formData.get("nik") ?? "").trim() || undefined,
    };
    const fingerprint = JSON.stringify(input);
    const requestId =
      pendingRequest.current?.fingerprint === fingerprint
        ? pendingRequest.current.id
        : crypto.randomUUID();

    pendingRequest.current = { fingerprint, id: requestId };

    try {
      await registerMember(input, requestId);
      pendingRequest.current = null;
      router.push("/profil");
    } catch (submitError: unknown) {
      setError(
        submitError instanceof Error
          ? submitError.message
          : "Pendaftaran gagal. Silakan coba lagi.",
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
            Bergabung resmi
          </p>
          <h1 className="mt-2 text-3xl font-black tracking-tight sm:text-4xl">
            Pendaftaran Anggota
          </h1>
          <p className="mt-3 max-w-lg leading-7 text-white/90">
            Daftarkan diri untuk menjadi bagian dari komunitas yang belajar dan
            bergerak bersama.
          </p>
        </header>

        <form className="space-y-5 p-6 sm:p-10" onSubmit={handleSubmit}>
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
              htmlFor="name"
            >
              Nama Lengkap
            </label>
            <input
              autoComplete="name"
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"
              id="name"
              maxLength={255}
              name="name"
              required
            />
          </div>

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
              htmlFor="cluster_id"
            >
              Wilayah
            </label>
            <select
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 disabled:cursor-not-allowed disabled:bg-slate-100"
              disabled={isLoadingRegions || regions.length === 0}
              id="cluster_id"
              name="cluster_id"
              required
            >
              <option value="">
                {isLoadingRegions ? "Memuat wilayah..." : "Pilih wilayah"}
              </option>
              {regions.map((region) => (
                <option key={region.region_code} value={region.region_code}>
                  {region.region_name} ({region.region_code})
                </option>
              ))}
            </select>
            {!isLoadingRegions && regions.length === 0 && !error && (
              <p className="mt-2 text-sm text-text-muted">
                Data wilayah belum tersedia.
              </p>
            )}
          </div>

          <div>
            <label
              className="mb-2 block text-sm font-bold text-text-main"
              htmlFor="nik"
            >
              NIK <span className="font-normal text-text-muted">(opsional)</span>
            </label>
            <input
              className="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-text-main outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10"
              id="nik"
              maxLength={255}
              name="nik"
              type="text"
            />
          </div>

          <button
            className="w-full rounded-xl bg-primary px-5 py-3.5 font-bold text-white transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isSubmitting || isLoadingRegions || regions.length === 0}
            type="submit"
          >
            {isSubmitting ? "Mendaftarkan..." : "Daftar sebagai Anggota"}
          </button>

          <p className="text-center text-sm text-text-muted">
            Kembali ke{" "}
            <Link className="font-semibold text-primary hover:underline" href="/">
              beranda
            </Link>
          </p>
        </form>
      </section>
    </main>
  );
}
