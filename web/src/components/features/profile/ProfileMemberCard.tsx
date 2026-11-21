"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { getCurrentUser } from "@/lib/api";
import type { RegisteredUser } from "@/types";
import MemberCard from "./MemberCard";

export default function ProfileMemberCard() {
  const [user, setUser] = useState<RegisteredUser | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let active = true;

    getCurrentUser()
      .then((currentUser) => {
        if (active) {
          setUser(currentUser);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(
            loadError instanceof Error ? loadError.message : "Profil gagal dimuat.",
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
      <section className="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-surface p-6 shadow-xl shadow-primary/5 sm:p-10">
        <Link
          className="text-sm font-bold text-primary underline underline-offset-4"
          href="/profil"
        >
          Kembali ke profil
        </Link>
        <h1 className="mt-4 text-3xl font-black tracking-tight text-text-main">
          Kartu Anggota Digital
        </h1>

        {isLoading ? (
          <p className="mt-8 rounded-2xl bg-background p-5 text-text-muted">
            Memuat kartu anggota...
          </p>
        ) : error ? (
          <p className="mt-8 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800" role="alert">
            Kartu anggota gagal dimuat: {error}
          </p>
        ) : user ? (
          <MemberCard user={user} />
        ) : null}
      </section>
    </main>
  );
}
