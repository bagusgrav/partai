"use client";

import { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { getGlossaryTerms, type GlossaryTerm } from "@/lib/api/glossary";

function termId(term: string): string {
  return term
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
}

export default function GlossaryPage() {
  const [terms, setTerms] = useState<GlossaryTerm[]>([]);
  const [search, setSearch] = useState("");
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    getGlossaryTerms()
      .then((result) => {
        if (active) {
          setTerms(result);
          setError(null);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(
            loadError instanceof Error
              ? loadError.message
              : "Glosarium belum dapat dimuat. Silakan coba kembali.",
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

  const filteredTerms = useMemo(() => {
    const query = search.trim().toLocaleLowerCase("id-ID");
    if (!query) {
      return terms;
    }

    return terms.filter(({ term, definition, category }) =>
      `${term} ${definition} ${category}`
        .toLocaleLowerCase("id-ID")
        .includes(query),
    );
  }, [search, terms]);

  return (
    <main className="flex-1 bg-slate-50">
      <header className="bg-slate-950 text-white">
        <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-amber-300">
            Bantuan &amp; literasi
          </p>
          <h1 className="mt-4 text-4xl font-black leading-tight sm:text-5xl">
            Glosarium
          </h1>
          <p className="mt-5 max-w-2xl text-lg leading-8 text-slate-300">
            Arti istilah data publik, ekonomi, partisipasi, dan tata kelola
            dengan penjelasan yang mudah dipahami.
          </p>
        </div>
      </header>

      <div className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
          <div className="w-full max-w-xl">
            <label
              className="mb-2 block text-sm font-bold text-slate-800"
              htmlFor="glossary-search"
            >
              Cari istilah atau penjelasan
            </label>
            <input
              className="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900"
              id="glossary-search"
              onChange={(event) => setSearch(event.target.value)}
              placeholder="Contoh: IKK, pengangguran, kontribusi"
              type="search"
              value={search}
            />
          </div>
          <Link
            className="font-semibold text-slate-700 underline decoration-slate-400 underline-offset-4 hover:text-slate-950"
            href="/tanya-jawab"
          >
            Tidak menemukan istilah? Ajukan pertanyaan
          </Link>
        </div>

        {isLoading ? (
          <p
            className="mt-8 rounded-xl border border-slate-200 bg-white p-5 text-slate-600"
            role="status"
          >
            Memuat glosarium...
          </p>
        ) : error ? (
          <p
            className="mt-8 rounded-xl border border-red-200 bg-red-50 p-5 font-semibold text-red-900"
            role="alert"
          >
            {error}
          </p>
        ) : filteredTerms.length === 0 ? (
          <p className="mt-8 rounded-xl border border-slate-200 bg-white p-5 text-slate-600">
            Tidak ada istilah yang cocok dengan “{search}”.
          </p>
        ) : (
          <div className="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {filteredTerms.map(({ id, term, definition, category }) => (
              <article
                className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                id={termId(term)}
                key={id}
              >
                <h2 className="text-lg font-bold text-slate-950">{term}</h2>
                <p className="mt-3 text-sm leading-7 text-slate-700">
                  {definition}
                </p>
                <p className="mt-4 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                  {category}
                </p>
              </article>
            ))}
          </div>
        )}
      </div>
    </main>
  );
}
