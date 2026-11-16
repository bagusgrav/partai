"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { getCurrentUser } from "@/lib/api";
import type { RegisteredUser } from "@/types";

export default function ProfilePage() {
  const [user, setUser] = useState<RegisteredUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

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

        {isLoading ? (
          <p className="mt-8 rounded-2xl bg-background p-5 text-text-muted">
            Memuat profil...
          </p>
        ) : error ? (
          <div
            className="mt-8 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-800"
            role="alert"
          >
            Profil gagal dimuat: {error}
          </div>
        ) : user ? (
          <dl className="mt-8 divide-y divide-slate-100 rounded-2xl border border-slate-200 px-5">
            <div className="py-4">
              <dt className="text-sm font-semibold text-text-muted">Nama</dt>
              <dd className="mt-1 font-bold text-text-main">{user.name}</dd>
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
