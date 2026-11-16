"use client";

import { FormEvent, useState } from "react";
import {
  checkSocialAssistance,
  RightsCheckError,
  type SocialAssistanceStatus,
} from "@/lib/api/rights";

const programNames: Record<SocialAssistanceStatus["program"], string> = {
  PKH: "Program Keluarga Harapan (PKH)",
  BPNT: "Bantuan Pangan Non Tunai (BPNT)",
  PIP: "Program Indonesia Pintar (PIP)",
};

const statusNames: Record<SocialAssistanceStatus["status"], string> = {
  DITERIMA: "Terdaftar sebagai penerima",
  TIDAK_DITERIMA: "Tidak ditetapkan sebagai penerima",
  PROSES: "Sedang diproses",
};

const statusStyles: Record<SocialAssistanceStatus["status"], string> = {
  DITERIMA: "border-green-300 bg-green-50 text-green-950",
  TIDAK_DITERIMA: "border-red-300 bg-red-50 text-red-950",
  PROSES: "border-amber-300 bg-amber-50 text-amber-950",
};

export default function RightsChecker() {
  const [nik, setNik] = useState("");
  const [result, setResult] = useState<SocialAssistanceStatus | null>(null);
  const [notRegistered, setNotRegistered] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setResult(null);
    setNotRegistered(false);
    setError(null);

    if (!/^[0-9]{16}$/.test(nik)) {
      setError("Masukkan NIK yang terdiri dari 16 digit angka.");
      return;
    }

    setIsLoading(true);
    try {
      setResult(await checkSocialAssistance(nik));
    } catch (requestError) {
      if (requestError instanceof RightsCheckError && requestError.status === 404) {
        setNotRegistered(true);
      } else {
        setError(
          requestError instanceof Error
            ? requestError.message
            : "Pengecekan gagal. Silakan coba kembali.",
        );
      }
    } finally {
      setIsLoading(false);
    }
  }

  return (
    <main className="mx-auto w-full max-w-2xl flex-1 px-4 py-8 sm:px-6 sm:py-12">
      <section className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-8">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-primary">
          Jemput Hak
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
          Cek Status Bansos Anda
        </h1>
        <p className="mt-3 text-base leading-7 text-text-muted">
          Masukkan NIK untuk melihat status bantuan sosial yang tercatat.
        </p>

        <form className="mt-7 space-y-4" onSubmit={handleSubmit}>
          <div>
            <label
              className="mb-2 block text-sm font-bold text-text-main"
              htmlFor="nik"
            >
              Nomor Induk Kependudukan (NIK)
            </label>
            <input
              autoComplete="off"
              className="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-lg tracking-[0.12em] text-text-main outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
              id="nik"
              inputMode="numeric"
              maxLength={16}
              onChange={(event) =>
                setNik(event.target.value.replace(/\D/g, "").slice(0, 16))
              }
              pattern="[0-9]{16}"
              placeholder="Masukkan 16 digit NIK"
              required
              value={nik}
            />
            <p className="mt-2 text-sm text-text-muted">
              {nik.length}/16 digit. NIK digunakan untuk mencari data bansos.
            </p>
          </div>
          <button
            className="min-h-12 w-full rounded-xl bg-primary px-5 py-3 text-base font-bold text-white transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            disabled={isLoading}
            type="submit"
          >
            {isLoading ? "Memeriksa..." : "Cek Sekarang"}
          </button>
        </form>

        {error && (
          <div
            className="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-amber-950"
            role="alert"
          >
            {error}
          </div>
        )}

        {notRegistered && (
          <div
            className="mt-6 rounded-xl border border-red-300 bg-red-50 p-4 text-sm leading-6 text-red-950 sm:p-5"
            role="status"
          >
            <p className="font-bold">
              NIK tidak terdaftar sebagai penerima bansos.
            </p>
            <p className="mt-2">
              Jika Anda merasa berhak namun tidak terdaftar, laporkan ke
              pengurus partai untuk diaudit.
            </p>
          </div>
        )}

        {result && (
          <div
            className={`mt-6 rounded-xl border p-4 sm:p-5 ${statusStyles[result.status]}`}
            role="status"
          >
            <p className="text-lg font-bold">{statusNames[result.status]}</p>
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="font-semibold">Program</dt>
                <dd className="mt-1">{programNames[result.program]}</dd>
              </div>
              <div>
                <dt className="font-semibold">Periode</dt>
                <dd className="mt-1">{result.period}</dd>
              </div>
            </dl>
          </div>
        )}
      </section>
      <p className="mx-auto mt-5 max-w-xl text-center text-xs leading-5 text-text-muted">
        Gunakan layanan ini hanya untuk memeriksa data Anda sendiri. Hasil
        mengikuti data yang tersedia dan bukan keputusan resmi penyaluran.
      </p>
    </main>
  );
}
