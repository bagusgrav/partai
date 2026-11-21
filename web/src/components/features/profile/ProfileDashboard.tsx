"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { getCurrentUser, getUserReputation } from "@/lib/api";
import type { RegisteredUser, UserReputation } from "@/types";

const masteryLevelThresholds = [
  { level: 1, start: 0, next: 200 },
  { level: 2, start: 200, next: 300 },
  { level: 3, start: 300, next: 400 },
] as const;

const reputationActionLabels: Record<string, string> = {
  quiz_correct: "Menjawab kuis dengan benar",
  essay_passed: "Lulus peer-review esai",
  attendance_validated: "Kehadiran aksi tervalidasi",
  citizen_jury: "Atestasi sebagai juri warga",
};

export default function ProfileDashboard() {
  const [user, setUser] = useState<RegisteredUser | null>(null);
  const [reputation, setReputation] = useState<UserReputation | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    Promise.all([getCurrentUser(), getUserReputation()])
      .then(([currentUser, currentReputation]) => {
        if (active) {
          setUser(currentUser);
          setReputation(currentReputation);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(
            loadError instanceof Error
              ? loadError.message
              : "Profil gagal dimuat.",
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

  return (
    <main className="flex-1 px-5 py-10 sm:px-8 sm:py-14">
      <section className="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-surface p-6 shadow-xl shadow-primary/5 sm:p-10">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-primary">
          Akun anggota
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-text-main">
          Profil Saya
        </h1>
        <span className="mt-3 inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">
          Data Terlindungi
        </span>

        {isLoading ? (
          <p className="mt-8 rounded-2xl bg-background p-5 text-text-muted">
            Memuat profil...
          </p>
        ) : error ? (
          <div
            className="mt-8 rounded-2xl border border-slate-300 bg-slate-100 p-5 text-sm text-slate-800"
            role="alert"
          >
            Profil gagal dimuat: {error}
          </div>
        ) : user && reputation ? (
          <>
            <section
              aria-label="Kartu reputasi"
              className="mt-8 rounded-2xl bg-primary p-6 text-white shadow-lg shadow-primary/20"
            >
              <p className="text-sm font-bold uppercase tracking-wide text-white/75">
                Kartu Reputasi
              </p>
              <div className="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                  <h2 className="text-2xl font-black">
                    Level {user.overall_mastery_level}
                  </h2>
                  <p className="mt-1 text-sm text-white/80">
                    Level Kapasitas (Mastery)
                  </p>
                </div>
                <div className="grid grid-cols-2 gap-6 text-right">
                  <div>
                    <p className="text-2xl font-black">
                      {user.mastery_score.toLocaleString("id-ID")}
                    </p>
                    <p className="text-sm font-semibold text-white/80">
                      Mastery Score
                    </p>
                  </div>
                  <div>
                    <p className="text-2xl font-black">
                      {user.participation_score.toLocaleString("id-ID")}
                    </p>
                    <p className="text-sm font-semibold text-white/80">
                      Participation Score
                    </p>
                  </div>
                </div>
              </div>
              {(() => {
                const currentLevel = masteryLevelThresholds.find(
                  ({ level }) => level === user.overall_mastery_level,
                );
                const progress = currentLevel
                  ? Math.min(
                      100,
                      Math.max(
                        0,
                        ((user.mastery_score - currentLevel.start) /
                          (currentLevel.next - currentLevel.start)) *
                          100,
                      ),
                    )
                  : 100;

                return (
                  <div className="mt-5">
                    <div className="h-2 overflow-hidden rounded-full bg-white/25">
                      <div
                        aria-valuemax={100}
                        aria-valuemin={0}
                        aria-valuenow={Math.floor(progress)}
                        className="h-full rounded-full bg-white transition-all"
                        role="progressbar"
                        style={{ width: `${progress}%` }}
                      />
                    </div>
                    <p className="mt-2 text-sm text-white/80">
                      {currentLevel
                        ? `${Math.max(0, currentLevel.next - user.mastery_score).toLocaleString("id-ID")} poin lagi menuju level berikutnya`
                        : "Anda telah mencapai level Mastery tertinggi"}
                    </p>
                  </div>
                );
              })()}
            </section>

            <dl className="mt-6 divide-y divide-slate-100 rounded-2xl border border-slate-200 px-5">
            <div className="py-4">
              <dt className="text-sm font-semibold text-text-muted">Nama</dt>
              <dd className="mt-1 flex flex-wrap items-center gap-2 font-bold text-text-main">
                {user.name}
                <span className="rounded-full bg-primary-light px-3 py-1 text-xs font-bold text-primary">
                  Level Kapasitas {user.overall_mastery_level}
                </span>
              </dd>
            </div>
            <div className="py-4">
              <dt className="text-sm font-semibold text-text-muted">Email</dt>
              <dd className="mt-1 font-bold text-text-main">{user.email}</dd>
            </div>
            <div className="py-4">
              <dt className="text-sm font-semibold text-text-muted">Wilayah</dt>
              <dd className="mt-1 font-bold text-text-main">
                {user.cluster_name ?? user.cluster_id ?? "Belum ditentukan"}
              </dd>
            </div>
          </dl>
            <section className="mt-8" aria-labelledby="reputation-history-title">
              <h2
                className="text-lg font-extrabold text-text-main"
                id="reputation-history-title"
              >
                Riwayat Reputasi
              </h2>
              {reputation.logs.length === 0 ? (
                <p className="mt-3 rounded-xl bg-background p-4 text-sm text-text-muted">
                  Belum ada aktivitas yang menghasilkan poin.
                </p>
              ) : (
                <div className="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                  <table className="w-full min-w-[32rem] text-left text-sm">
                    <thead className="bg-background text-text-muted">
                      <tr>
                        <th className="px-4 py-3 font-semibold" scope="col">Waktu</th>
                        <th className="px-4 py-3 font-semibold" scope="col">Aksi</th>
                        <th className="px-4 py-3 text-right font-semibold" scope="col">Poin</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {reputation.logs.map((log) => (
                        <tr key={log.id}>
                          <td className="whitespace-nowrap px-4 py-3 text-text-muted">
                            {new Date(log.created_at).toLocaleDateString("id-ID", {
                              day: "numeric",
                              month: "short",
                              year: "numeric",
                            })}
                          </td>
                          <td className="px-4 py-3 text-text-main">
                            {reputationActionLabels[log.action] ?? log.description}
                          </td>
                          <td className="whitespace-nowrap px-4 py-3 text-right font-bold text-primary">
                            +{log.points.toLocaleString("id-ID")}
                            {log.score_type === "mastery" ? " Mastery" : ""}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </section>
            <Link
              className="mt-6 inline-flex rounded-full border border-primary px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
              href="/profil/kartu-anggota"
            >
              Lihat Kartu Anggota Digital
            </Link>
          </>
        ) : null}

        <Link
          className="mt-6 inline-flex rounded-full px-4 py-2 text-sm font-semibold text-primary transition hover:bg-primary-light focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
          href="/belajar"
        >
          Lanjut belajar
        </Link>
      </section>
    </main>
  );
}
