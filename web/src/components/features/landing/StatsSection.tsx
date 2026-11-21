"use client";

import { useEffect, useState } from "react";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "@/lib/api/client";

interface LandingStats {
  total_members: number;
  total_votes_cast: number;
  total_quizzes_completed: number;
  poverty_rate: number;
  active_votes: number;
}

const fallbackStats: LandingStats = {
  total_members: 0,
  total_votes_cast: 0,
  total_quizzes_completed: 0,
  poverty_rate: 0,
  active_votes: 0,
};

const statKeys = [
  "total_members",
  "total_votes_cast",
  "total_quizzes_completed",
  "poverty_rate",
  "active_votes",
] as const satisfies readonly (keyof LandingStats)[];

function isLandingStats(value: unknown): value is LandingStats {
  if (typeof value !== "object" || value === null) {
    return false;
  }

  return statKeys.every((key) => {
    const stat = (value as Record<string, unknown>)[key];

    return typeof stat === "number" && Number.isFinite(stat);
  });
}

const numberFormatter = new Intl.NumberFormat("id-ID");
const povertyFormatter = new Intl.NumberFormat("id-ID", {
  maximumFractionDigits: 1,
});

export default function StatsSection() {
  const [stats, setStats] = useState(fallbackStats);

  useEffect(() => {
    let active = true;

    async function loadStats(): Promise<void> {
      try {
        const response = await fetchWithRetry(`${API_BASE_URL}/stats/landing`, {
          method: "GET",
          headers: { Accept: "application/json" },
        });
        await ensureSuccessfulResponse(response);

        const data: unknown = await response.json();
        if (!isLandingStats(data)) {
          throw new Error("Respons statistik beranda tidak valid.");
        }

        if (active) {
          setStats(data);
        }
      } catch (error: unknown) {
        console.error("Gagal memuat statistik beranda.", error);
      }
    }

    void loadStats();

    return () => {
      active = false;
    };
  }, []);

  const partyStats = [
    { label: "Pengguna terdaftar", value: numberFormatter.format(stats.total_members) },
    { label: "Suara masuk", value: numberFormatter.format(stats.total_votes_cast) },
    {
      label: "Jawaban kuis benar",
      value: numberFormatter.format(stats.total_quizzes_completed),
    },
    { label: "Voting aktif", value: numberFormatter.format(stats.active_votes) },
  ];

  return (
    <section
      aria-labelledby="landing-stats-title"
      className="animate-fade-in border-y border-primary/10 bg-primary-light motion-reduce:animate-none"
    >
      <div className="mx-auto max-w-7xl px-6 py-16 sm:px-8 sm:py-20">
        <div className="max-w-2xl">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-primary-hover">
            Infografik (Statistik)
          </p>
          <h2
            className="mt-3 text-3xl font-black tracking-tight text-text-main sm:text-4xl"
            id="landing-stats-title"
          >
            Kondisi Partai dan Indonesia
          </h2>
        </div>

        <div className="mt-9">
          <h3 className="text-xl font-extrabold text-text-main">Kondisi Partai</h3>
          <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {partyStats.map(({ label, value }) => (
              <article
                className="rounded-2xl border border-primary/10 bg-surface p-6 shadow-sm"
                key={label}
              >
                <p className="text-3xl font-black text-text-main">{value}</p>
                <p className="mt-2 text-sm text-gray-500">{label}</p>
              </article>
            ))}
          </div>
        </div>

        <div className="mt-8">
          <h3 className="text-xl font-extrabold text-text-main">Kondisi Indonesia</h3>
          <div className="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article className="rounded-2xl border border-primary/10 bg-surface p-6 shadow-sm">
              <p className="text-3xl font-black text-text-main">
                {povertyFormatter.format(stats.poverty_rate)}%
              </p>
              <p className="mt-2 text-sm text-gray-500">Angka kemiskinan</p>
            </article>
          </div>
        </div>
      </div>
    </section>
  );
}
