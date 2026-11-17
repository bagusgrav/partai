"use client";

import { useEffect, useState } from "react";
import {
  getReputationLeaderboardStats,
  type ReputationLeaderboardStats,
} from "@/lib/api/reputation";

const domains = [
  { key: "hak_konstitusional", label: "Hak Konstitusional" },
  { key: "alasan_bernegara", label: "Alasan Bernegara" },
  { key: "keuangan_negara", label: "Keuangan Negara" },
  { key: "hukum", label: "Hukum" },
  { key: "politik_bernegara", label: "Politik Bernegara" },
] as const;

const roles = [
  { key: "panel_audit", label: "Panel Audit" },
  { key: "panel_etik", label: "Panel Etik" },
  { key: "bakal_caleg", label: "Bakal Caleg" },
] as const;

type DomainKey = (typeof domains)[number]["key"];

function masteryLevel(average: number): number {
  if (average >= 80) {
    return 4;
  }
  if (average >= 60) {
    return 3;
  }
  if (average >= 40) {
    return 2;
  }
  return 1;
}

function formatNumber(value: number): string {
  return new Intl.NumberFormat("id-ID").format(value);
}

export default function ReputasiPage() {
  const [stats, setStats] = useState<ReputationLeaderboardStats | null>(null);
  const [loadError, setLoadError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [scores, setScores] = useState<Record<DomainKey, number>>({
    hak_konstitusional: 0,
    alasan_bernegara: 0,
    keuangan_negara: 0,
    hukum: 0,
    politik_bernegara: 0,
  });

  useEffect(() => {
    let active = true;

    getReputationLeaderboardStats()
      .then((result) => {
        if (active) {
          setStats(result);
          setLoadError(null);
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setLoadError(
            error instanceof Error
              ? error.message
              : "Statistik reputasi belum dapat dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setIsLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  const average = Math.floor(
    Object.values(scores).reduce((total, score) => total + score, 0) /
      domains.length,
  );
  const level = masteryLevel(average);
  const maxMembers = Math.max(
    1,
    ...(stats?.mastery_distribution.map((item) => item.members) ?? []),
  );

  return (
    <main className="flex-1 bg-slate-50">
      <section className="bg-slate-950 text-white">
        <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-amber-300">
            Transparansi tata kelola
          </p>
          <h1 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
            Reputasi Anggota
          </h1>
          <p className="mt-5 max-w-3xl text-lg leading-8 text-slate-300">
            Kapasitas belajar dan kontribusi dihitung terpisah. Skor bukan
            peringkat, dan tidak pernah menggantikan prinsip satu anggota, satu
            suara.
          </p>
        </div>
      </section>

      <div className="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:px-8">
        <section
          aria-labelledby="score-separation"
          className="grid gap-5 md:grid-cols-2"
        >
          <article className="rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm">
            <p className="text-sm font-bold uppercase tracking-wide text-indigo-700">
              Kapasitas intelektual
            </p>
            <h2
              className="mt-2 text-2xl font-black text-slate-950"
              id="score-separation"
            >
              Mastery Score
            </h2>
            <p className="mt-3 leading-7 text-slate-700">
              Nilai 0–100 pada lima domain pendidikan kewargaan. Nilai ini
              membentuk tingkat penguasaan 1–4 dan menjadi ambang kelayakan
              absolut untuk peran fungsional atau pencalonan, bukan posisi
              seseorang dalam peringkat.
            </p>
          </article>
          <article className="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
            <p className="text-sm font-bold uppercase tracking-wide text-emerald-700">
              Kontribusi anggota
            </p>
            <h2 className="mt-2 text-2xl font-black text-slate-950">
              Participation Score
            </h2>
            <p className="mt-3 leading-7 text-slate-700">
              Poin kontribusi terverifikasi yang terpisah dari penguasaan
              materi. Kerja lapangan dan kontribusi non-fisik—riset, bantuan
              hukum, logistik, serta editorial/asinkron—mendapat bobot setara.
              Poin ini tidak memengaruhi hak suara.
            </p>
          </article>
        </section>

        <section
          aria-labelledby="statistics-heading"
          className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
        >
          <div className="flex flex-wrap items-end justify-between gap-4">
            <div>
              <p className="text-sm font-bold uppercase tracking-wide text-slate-500">
                Data agregat anonim
              </p>
              <h2
                className="mt-1 text-2xl font-black text-slate-950"
                id="statistics-heading"
              >
                Gambaran Reputasi
              </h2>
            </div>
            {stats && (
              <p className="text-sm font-semibold text-slate-600">
                {formatNumber(stats.members_count)} profil anggota
              </p>
            )}
          </div>

          {isLoading ? (
            <p className="mt-6 rounded-xl bg-slate-50 p-5 text-slate-600">
              Memuat statistik reputasi...
            </p>
          ) : loadError ? (
            <p
              className="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 font-semibold text-red-900"
              role="alert"
            >
              {loadError}
            </p>
          ) : stats ? (
            <div className="mt-7 grid gap-8 lg:grid-cols-2">
              <div>
                <h3 className="font-bold text-slate-900">
                  Distribusi tingkat mastery
                </h3>
                <div className="mt-4 space-y-4">
                  {stats.mastery_distribution.map((item) => (
                    <div key={item.level}>
                      <div className="mb-1 flex justify-between text-sm">
                        <span className="font-medium text-slate-700">
                          Level {item.level}
                        </span>
                        <span className="tabular-nums text-slate-600">
                          {formatNumber(item.members)}
                        </span>
                      </div>
                      <div
                        aria-label={`${item.members} anggota pada level ${item.level}`}
                        className="h-3 overflow-hidden rounded-full bg-slate-100"
                        role="img"
                      >
                        <div
                          className="h-full rounded-full bg-indigo-600"
                          style={{
                            width: `${(item.members / maxMembers) * 100}%`,
                          }}
                        />
                      </div>
                    </div>
                  ))}
                </div>
              </div>
              <div>
                <h3 className="font-bold text-slate-900">
                  Kontribusi terverifikasi
                </h3>
                <div className="mt-4 space-y-4">
                  {[
                    {
                      key: "lapangan",
                      label: "Lapangan",
                      points: stats.contributions.lapangan,
                      color: "bg-emerald-600",
                    },
                    {
                      key: "non-fisik",
                      label: "Non-fisik",
                      points: stats.contributions.non_fisik,
                      color: "bg-teal-600",
                    },
                  ].map((item) => (
                    <div key={item.key}>
                      <div className="mb-1 flex justify-between text-sm">
                        <span className="font-medium text-slate-700">
                          {item.label}
                        </span>
                        <span className="tabular-nums text-slate-600">
                          {formatNumber(item.points)} poin
                        </span>
                      </div>
                      <div
                        aria-label={`${item.points} poin kontribusi ${item.label.toLowerCase()}`}
                        className="h-3 overflow-hidden rounded-full bg-slate-100"
                        role="img"
                      >
                        <div
                          className={`h-full rounded-full ${item.color}`}
                          style={{
                            width: `${
                              stats.contributions.total > 0
                                ? (item.points / stats.contributions.total) * 100
                                : 0
                            }%`,
                          }}
                        />
                      </div>
                    </div>
                  ))}
                </div>
                <p className="mt-4 text-sm text-slate-600">
                  Total {formatNumber(stats.contributions.total)} poin, dengan
                  bobot kategori yang setara.
                </p>
              </div>
            </div>
          ) : null}
          <p className="mt-6 border-t border-slate-100 pt-4 text-sm leading-6 text-slate-500">
            Statistik tidak menampilkan identitas, nilai per anggota, atau
            riwayat kontribusi individu.
          </p>
        </section>

        <section
          aria-labelledby="eligibility-heading"
          className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
        >
          <p className="text-sm font-bold uppercase tracking-wide text-slate-500">
            Simulasi mandiri
          </p>
          <h2
            className="mt-1 text-2xl font-black text-slate-950"
            id="eligibility-heading"
          >
            Cek Ambang Kelayakan
          </h2>
          <p className="mt-3 max-w-3xl leading-7 text-slate-700">
            Atur skor contoh pada setiap domain. Tingkat keseluruhan adalah
            rata-rata lima domain; level 4 (rata-rata minimal 80) adalah syarat
            ambang untuk peran di bawah. Participation Score tidak dihitung.
          </p>

          <div className="mt-6 grid gap-4 md:grid-cols-2">
            {domains.map(({ key, label }) => (
              <label className="block" htmlFor={`score-${key}`} key={key}>
                <span className="flex justify-between gap-4 text-sm font-semibold text-slate-800">
                  {label}
                  <span className="tabular-nums">{scores[key]}/100</span>
                </span>
                <input
                  className="mt-2 w-full accent-indigo-700"
                  id={`score-${key}`}
                  max={100}
                  min={0}
                  onChange={(event) => {
                    setScores((current) => ({
                      ...current,
                      [key]: Number(event.target.value),
                    }));
                  }}
                  type="range"
                  value={scores[key]}
                />
              </label>
            ))}
          </div>

          <div
            aria-live="polite"
            className="mt-6 rounded-xl bg-indigo-50 p-5"
          >
            <p className="font-bold text-indigo-950">
              Simulasi rata-rata: {average}/100 — Level {level}
            </p>
            <div className="mt-4 grid gap-3 sm:grid-cols-3">
              {roles.map(({ key, label }) => (
                <div
                  className="rounded-lg border border-indigo-100 bg-white p-4"
                  key={key}
                >
                  <p className="font-bold text-slate-900">{label}</p>
                  <p
                    className={`mt-1 text-sm font-semibold ${
                      level >= 4 ? "text-emerald-700" : "text-slate-600"
                    }`}
                  >
                    {level >= 4 ? "Ambang terpenuhi" : "Belum memenuhi"}
                  </p>
                  <p className="mt-1 text-xs text-slate-500">
                    Syarat: Mastery Level 4
                  </p>
                </div>
              ))}
            </div>
          </div>
          <p className="mt-4 text-sm leading-6 text-slate-500">
            Ini simulator transparansi, bukan keputusan pencalonan atau
            sertifikasi resmi. Evaluasi resmi tetap mengikuti asesmen dan
            aturan organisasi yang berlaku.
          </p>
        </section>
      </div>
    </main>
  );
}
