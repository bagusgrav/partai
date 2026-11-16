"use client";

import { useEffect, useState } from "react";
import { getWelfareRegions, getWelfareSummary } from "@/lib/api";
import type { WelfareRegion, WelfareSummary } from "@/types";

const percentageFormatter = new Intl.NumberFormat("id-ID", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});
const numberFormatter = new Intl.NumberFormat("id-ID");

function formatPercentage(value: number | null): string {
  return value === null
    ? "Belum tersedia"
    : `${percentageFormatter.format(value)}%`;
}

export default function WelfareDashboard() {
  const [summary, setSummary] = useState<WelfareSummary | null>(null);
  const [regions, setRegions] = useState<WelfareRegion[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isCurrent = true;

    async function loadWelfareData() {
      try {
        const [summaryResponse, regionsResponse] = await Promise.all([
          getWelfareSummary(),
          getWelfareRegions(),
        ]);

        if (isCurrent) {
          setSummary(summaryResponse.summary);
          setRegions(regionsResponse.regions);
        }
      } catch (requestError) {
        if (isCurrent) {
          setError(
            requestError instanceof Error
              ? requestError.message
              : "Data kesejahteraan gagal dimuat.",
          );
        }
      } finally {
        if (isCurrent) {
          setIsLoading(false);
        }
      }
    }

    void loadWelfareData();

    return () => {
      isCurrent = false;
    };
  }, []);

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-5 py-10 sm:px-8">
      <section className="mb-8">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-primary">
          Transparansi publik
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
          Data Kesejahteraan
        </h1>
        <p className="mt-3 max-w-3xl text-base leading-7 text-text-muted">
          Pantau indikator kemiskinan, stunting, dan kejadian bencana berdasarkan
          data wilayah.
        </p>
      </section>

      {error && (
        <div
          className="mb-6 rounded-2xl border border-slate-300 bg-slate-100 px-5 py-4 text-sm text-slate-800"
          role="alert"
        >
          Gagal memuat data: {error}
        </div>
      )}

      <section
        aria-label="Ringkasan nasional"
        className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
      >
        <article className="rounded-2xl border border-slate-200 bg-surface p-6 shadow-sm">
          <p className="text-sm font-semibold text-text-muted">
            Rata-rata kemiskinan
          </p>
          <p className="mt-3 text-3xl font-black text-text-main">
            {isLoading
              ? "Memuat..."
              : formatPercentage(summary?.average_poverty ?? null)}
          </p>
          <p className="mt-2 text-sm text-text-muted">
            BPS {summary?.poverty_year ?? ""}
          </p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-surface p-6 shadow-sm">
          <p className="text-sm font-semibold text-text-muted">
            Rata-rata stunting
          </p>
          <p className="mt-3 text-3xl font-black text-text-main">
            {isLoading
              ? "Memuat..."
              : formatPercentage(summary?.average_stunting ?? null)}
          </p>
          <p className="mt-2 text-sm text-text-muted">
            BPS {summary?.stunting_year ?? ""}
          </p>
        </article>
        <article className="rounded-2xl border border-slate-200 bg-surface p-6 shadow-sm">
          <p className="text-sm font-semibold text-text-muted">
            Kejadian bencana
          </p>
          <p className="mt-3 text-3xl font-black text-text-main">
            {isLoading
              ? "Memuat..."
              : numberFormatter.format(summary?.disaster_events ?? 0)}
          </p>
          <p className="mt-2 text-sm text-text-muted">
            BNPB {summary?.disaster_year ?? ""}
          </p>
        </article>
      </section>

      <section className="rounded-2xl border border-slate-200 bg-surface shadow-sm">
        <div className="border-b border-slate-200 px-5 py-5 sm:px-6">
          <h2 className="text-xl font-bold text-text-main">Data per provinsi</h2>
          <p className="mt-1 text-sm text-text-muted">
            Nilai indikator terbaru yang tersedia untuk setiap wilayah.
          </p>
        </div>
        <div className="overflow-x-auto">
          <table className="w-full min-w-[720px] text-left text-sm">
            <thead className="bg-slate-50 text-xs uppercase tracking-wide text-text-muted">
              <tr>
                <th className="px-5 py-3 font-semibold sm:px-6">Provinsi</th>
                <th className="px-5 py-3 font-semibold">Kemiskinan</th>
                <th className="px-5 py-3 font-semibold">Stunting</th>
                <th className="px-5 py-3 font-semibold">Kejadian bencana</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {regions.map((region) => (
                <tr key={region.region_code} className="text-text-main">
                  <td className="px-5 py-4 sm:px-6">
                    <p className="font-semibold">{region.region_name}</p>
                    <p className="mt-1 text-xs text-text-muted">
                      Kode BPS {region.region_code}
                    </p>
                  </td>
                  <td className="px-5 py-4">
                    {formatPercentage(region.poverty)}
                    {region.poverty_year && (
                      <span className="mt-1 block text-xs text-text-muted">
                        BPS {region.poverty_year}
                      </span>
                    )}
                  </td>
                  <td className="px-5 py-4">
                    {formatPercentage(region.stunting)}
                    {region.stunting_year && (
                      <span className="mt-1 block text-xs text-text-muted">
                        BPS {region.stunting_year}
                      </span>
                    )}
                  </td>
                  <td className="px-5 py-4">
                    {numberFormatter.format(region.disaster_events)}
                    <span className="mt-1 block text-xs text-text-muted">
                      BNPB {region.disaster_year ?? "Belum tersedia"}
                    </span>
                  </td>
                </tr>
              ))}
              {!isLoading && regions.length === 0 && !error && (
                <tr>
                  <td
                    className="px-5 py-8 text-center text-text-muted sm:px-6"
                    colSpan={4}
                  >
                    Belum ada data wilayah.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
        <p className="border-t border-slate-100 px-5 py-4 text-xs leading-5 text-text-muted sm:px-6">
          Data diperbarui otomatis melalui pipeline API BPS dan BNPB. Jika API
          key belum dikonfigurasi, dasbor menampilkan data simulasi.
        </p>
      </section>
    </main>
  );
}
