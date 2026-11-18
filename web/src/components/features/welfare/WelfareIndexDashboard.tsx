"use client";

import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import {
  getWelfareIkk,
  getWelfareIkkRegion,
  type VulnerabilityTier,
  type WelfareIkkDetail,
  type WelfareIkkFilters,
  type WelfareIkkResponse,
} from "@/lib/api/welfare";

const tierLabels: Record<VulnerabilityTier, string> = {
  kritis: "Kritis",
  tinggi: "Tinggi",
  sedang: "Sedang",
  rendah: "Rendah",
};

const tierStyles: Record<VulnerabilityTier, string> = {
  kritis: "border-slate-950 bg-slate-950 text-white",
  tinggi: "border-slate-700 bg-slate-700 text-white",
  sedang: "border-slate-400 bg-slate-100 text-slate-800",
  rendah: "border-slate-200 bg-white text-slate-600",
};

const numberFormat = new Intl.NumberFormat("id-ID", {
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});
const integerFormat = new Intl.NumberFormat("id-ID");

const detailIndicators = [
  { key: "poverty_rate", label: "Kemiskinan", unit: "%" },
  { key: "stunting_rate", label: "Stunting", unit: "%" },
  { key: "gini_ratio", label: "Rasio Gini", unit: "" },
  { key: "open_unemployment_rate", label: "TPT", unit: "%" },
  { key: "disaster_risk_score", label: "Risiko bencana BNPB", unit: "/100" },
] as const;

function TierBadge({ tier }: { tier: VulnerabilityTier }) {
  return (
    <span
      className={`inline-flex rounded-full border px-3 py-1 text-xs font-bold ${tierStyles[tier]}`}
    >
      {tierLabels[tier]}
    </span>
  );
}

function errorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Data IKK gagal dimuat.";
}

export default function WelfareIndexDashboard() {
  const [filters, setFilters] = useState<WelfareIkkFilters>({
    sort: "ikk_desc",
  });
  const selectedCodeRef = useRef<string | null>(null);
  const [searchInput, setSearchInput] = useState("");
  const [result, setResult] = useState<WelfareIkkResponse | null>(null);
  const [selectedCode, setSelectedCode] = useState<string | null>(null);
  const [detail, setDetail] = useState<WelfareIkkDetail | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [detailLoading, setDetailLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [detailError, setDetailError] = useState<string | null>(null);

  useEffect(() => {
    const timerId = window.setTimeout(() => {
      setIsLoading(true);
      setFilters((current) => ({
        ...current,
        search: searchInput.trim() || undefined,
        page: 1,
      }));
    }, 250);

    return () => window.clearTimeout(timerId);
  }, [searchInput]);

  useEffect(() => {
    let active = true;

    void getWelfareIkk(filters)
      .then((response) => {
        if (!active) {
          return;
        }
        setResult(response);
        const nextCode = response.regions.data.some(
          (region) => region.region_code === selectedCodeRef.current,
        )
          ? selectedCodeRef.current
          : (response.regions.data[0]?.region_code ?? null);
        if (nextCode !== selectedCodeRef.current) {
          selectedCodeRef.current = nextCode;
          setDetail(null);
          setDetailError(null);
          setSelectedCode(nextCode);
          setDetailLoading(nextCode !== null);
        } else if (nextCode === null) {
          setDetail(null);
          setDetailError(null);
          setDetailLoading(false);
        }
        setError(null);
      })
      .catch((requestError: unknown) => {
        if (active) {
          setError(errorMessage(requestError));
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
  }, [filters]);

  useEffect(() => {
    if (!selectedCode) {
      return;
    }

    let active = true;
    void getWelfareIkkRegion(selectedCode)
      .then((response) => {
        if (active) {
          setDetail(response);
          setDetailError(null);
        }
      })
      .catch((requestError: unknown) => {
        if (active) {
          setDetailError(errorMessage(requestError));
        }
      })
      .finally(() => {
        if (active) {
          setDetailLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, [selectedCode]);

  const summary = result?.summary;
  const regions = result?.regions.data ?? [];
  const currentPage = result?.regions.current_page ?? 1;
  const lastPage = result?.regions.last_page ?? 1;

  function updateFilter<K extends keyof WelfareIkkFilters>(
    key: K,
    value: WelfareIkkFilters[K],
  ) {
    setIsLoading(true);
    setFilters((current) => ({ ...current, [key]: value, page: 1 }));
  }

  function selectRegion(regionCode: string) {
    selectedCodeRef.current = regionCode;
    setDetail(null);
    setDetailError(null);
    setDetailLoading(true);
    setSelectedCode(regionCode);
  }

  function updatePage(page: number) {
    setIsLoading(true);
    setFilters((current) => ({ ...current, page }));
  }

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-5 py-10 sm:px-8">
      <header className="mb-8 max-w-4xl">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-slate-600">
          Transparansi publik · Data BPS &amp; BNPB
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
          Indeks Kerentanan Kesejahteraan
        </h1>
        <p className="mt-3 text-base leading-7 text-slate-600">
          Peringkat kabupaten/kota untuk membantu membaca kebutuhan intervensi
          secara terbuka. Skor lebih tinggi menunjukkan kerentanan yang lebih
          mendesak; setiap nilai disertai tahun dan sitasi sumber.
        </p>
        <p className="mt-4 rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700">
          Data IKK level kabupaten/kota sedang dalam proses perluasan cakupan
          indikator BPS.
        </p>
      </header>

      {error && (
        <p
          className="mb-6 rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800"
          role="alert"
        >
          Gagal memuat peringkat: {error}
        </p>
      )}

      <section aria-labelledby="ikk-summary-title" className="mb-6">
        <div className="mb-3 flex flex-wrap items-end justify-between gap-2">
          <div>
            <h2 id="ikk-summary-title" className="text-xl font-bold text-slate-950">
              Ringkasan nasional
            </h2>
            <p className="mt-1 text-sm text-slate-500">
              Tahun {summary?.year ?? "—"} · rata-rata IKK{" "}
              <strong className="text-slate-900">
                {summary?.average_ikk == null
                  ? "Belum tersedia"
                  : numberFormat.format(summary.average_ikk)}
              </strong>
              {" · "}
              <Link
                className="font-semibold text-slate-700 underline underline-offset-2 hover:text-slate-950"
                href="/glosarium#ikk"
              >
                Apa itu IKK?
              </Link>
            </p>
          </div>
          {isLoading && (
            <span className="text-sm text-slate-500" role="status">
              Memuat data...
            </span>
          )}
        </div>
        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
          {(["kritis", "tinggi", "sedang", "rendah"] as const).map((tier) => (
            <button
              aria-pressed={filters.tier === tier}
              className={`rounded-2xl border p-4 text-left shadow-sm transition hover:border-slate-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-800 ${
                filters.tier === tier ? "ring-2 ring-slate-700" : "border-slate-200"
              }`}
              key={tier}
              onClick={() =>
                updateFilter("tier", filters.tier === tier ? undefined : tier)
              }
              type="button"
            >
              <span className="flex items-center justify-between gap-2">
                <TierBadge tier={tier} />
                <span className="text-2xl font-black text-slate-950">
                  {summary?.distribution[tier] ?? 0}
                </span>
              </span>
              <span className="mt-2 block text-xs text-slate-500">wilayah</span>
            </button>
          ))}
        </div>
      </section>

      <section
        aria-labelledby="ikk-method-title"
        className="mb-8 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-6"
      >
        <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div className="max-w-md">
            <h2 id="ikk-method-title" className="text-lg font-bold text-slate-950">
              Metodologi terbuka
            </h2>
            <p className="mt-1 text-sm leading-6 text-slate-600">
              Lima indikator dinormalisasi ke skala 0–100 dan dibatasi pada rentang
              acuan nasional; Gini memakai rentang 0,250–0,550. Skor merupakan
              jumlah sub-skor dikalikan bobot.
            </p>
          </div>
          <div className="grid flex-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            {summary &&
              Object.entries(summary.methodology).map(([key, component]) => (
                <div
                  className="rounded-xl border border-slate-200 bg-white px-3 py-2"
                  key={key}
                >
                  <p className="text-sm font-semibold text-slate-900">
                    {component.label}
                  </p>
                  <p className="text-xs text-slate-500">
                    BPS {component.weight}% · acuan {component.min}–{component.max}{" "}
                    {component.unit}
                  </p>
                </div>
              ))}
            <div className="rounded-xl border border-slate-300 bg-slate-100 px-3 py-2 sm:col-span-2 lg:col-span-1">
              <p className="text-sm font-bold text-slate-950">BPS 85% + BNPB 15%</p>
              <p className="text-xs text-slate-600">
                Kemiskinan, stunting, Gini, TPT, dan paparan bencana
              </p>
            </div>
          </div>
        </div>
      </section>

      <section aria-label="Peringkat kerentanan wilayah" className="space-y-5">
        <div className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 lg:grid-cols-4">
          <label className="text-sm font-semibold text-slate-700">
            Cari kabupaten/kota atau kode BPS
            <input
              className="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 font-normal text-slate-950 outline-none focus:border-slate-700 focus:ring-2 focus:ring-slate-200"
              onChange={(event) => {
                setIsLoading(true);
                setSearchInput(event.target.value);
              }}
              placeholder="Contoh: Bandung atau 3273"
              type="search"
              value={searchInput}
            />
          </label>
          <label className="text-sm font-semibold text-slate-700">
            Provinsi
            <select
              className="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-normal text-slate-950 outline-none focus:border-slate-700 focus:ring-2 focus:ring-slate-200"
              onChange={(event) =>
                updateFilter("province", event.target.value || undefined)
              }
              value={filters.province ?? ""}
            >
              <option value="">Semua provinsi</option>
              {(result?.provinces ?? []).map((province) => (
                <option key={province} value={province}>
                  {province}
                </option>
              ))}
            </select>
          </label>
          <label className="text-sm font-semibold text-slate-700">
            Tingkat kerentanan
            <select
              className="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-normal text-slate-950 outline-none focus:border-slate-700 focus:ring-2 focus:ring-slate-200"
              onChange={(event) =>
                updateFilter(
                  "tier",
                  (event.target.value || undefined) as VulnerabilityTier | undefined,
                )
              }
              value={filters.tier ?? ""}
            >
              <option value="">Semua tingkat</option>
              {(["kritis", "tinggi", "sedang", "rendah"] as const).map((tier) => (
                <option key={tier} value={tier}>
                  {tierLabels[tier]}
                </option>
              ))}
            </select>
          </label>
          <label className="text-sm font-semibold text-slate-700">
            Urutkan
            <select
              className="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 font-normal text-slate-950 outline-none focus:border-slate-700 focus:ring-2 focus:ring-slate-200"
              onChange={(event) =>
                updateFilter(
                  "sort",
                  event.target.value as WelfareIkkFilters["sort"],
                )
              }
              value={filters.sort ?? "ikk_desc"}
            >
              <option value="ikk_desc">Skor tertinggi</option>
              <option value="ikk_asc">Skor terendah</option>
              <option value="poverty_desc">Kemiskinan tertinggi</option>
              <option value="disaster_desc">Risiko bencana tertinggi</option>
            </select>
          </label>
        </div>

        <div className="grid gap-5 lg:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
          <section aria-label="Daftar wilayah" className="space-y-3">
            {regions.map((region, index) => (
              <button
                aria-pressed={selectedCode === region.region_code}
                className={`flex w-full items-center justify-between gap-4 rounded-2xl border p-4 text-left transition hover:border-slate-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-800 ${
                  selectedCode === region.region_code
                    ? "border-slate-700 bg-slate-50 ring-1 ring-slate-700"
                    : "border-slate-200 bg-white"
                }`}
                key={region.id}
                onClick={() => selectRegion(region.region_code)}
                type="button"
              >
                <span className="flex min-w-0 items-center gap-3">
                  <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700">
                    {(currentPage - 1) * (result?.regions.per_page ?? 20) + index + 1}
                  </span>
                  <span className="min-w-0">
                    <span className="block truncate font-bold text-slate-950">
                      {region.region_name}
                    </span>
                    <span className="mt-1 block truncate text-sm text-slate-500">
                      {region.province_name} · {region.region_code}
                    </span>
                  </span>
                </span>
                <span className="flex shrink-0 flex-col items-end gap-2">
                  <span className="text-2xl font-black tabular-nums text-slate-950">
                    {numberFormat.format(region.ikk_score)}
                  </span>
                  <TierBadge tier={region.vulnerability_tier} />
                </span>
              </button>
            ))}
            {!isLoading && regions.length === 0 && (
              <p className="rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">
                Tidak ada wilayah yang cocok dengan filter ini.
              </p>
            )}
            {result && result.regions.total > 0 && (
              <div className="flex items-center justify-between px-1 pt-2 text-sm text-slate-600">
                <span>
                  Halaman {currentPage} dari {lastPage} · {integerFormat.format(result.regions.total)}{" "}
                  wilayah
                </span>
                <span className="flex gap-2">
                  <button
                    className="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold disabled:cursor-not-allowed disabled:opacity-40"
                    disabled={currentPage <= 1 || isLoading}
                    onClick={() => updatePage(currentPage - 1)}
                    type="button"
                  >
                    Sebelumnya
                  </button>
                  <button
                    className="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold disabled:cursor-not-allowed disabled:opacity-40"
                    disabled={currentPage >= lastPage || isLoading}
                    onClick={() => updatePage(currentPage + 1)}
                    type="button"
                  >
                    Berikutnya
                  </button>
                </span>
              </div>
            )}
          </section>

          <aside
            aria-label="Rincian wilayah terpilih"
            className="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
          >
            {detailLoading && (
              <p className="text-sm text-slate-500" role="status">
                Memuat rincian wilayah...
              </p>
            )}
            {detailError && (
              <p className="text-sm text-slate-700" role="alert">
                Rincian gagal dimuat: {detailError}
              </p>
            )}
            {detail && (
              <>
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <p className="text-xs font-bold uppercase tracking-wide text-slate-500">
                      Profil wilayah · {detail.region.period_year}
                    </p>
                    <h2 className="mt-1 text-xl font-black text-slate-950">
                      {detail.region.region_name}
                    </h2>
                    <p className="mt-1 text-sm text-slate-600">
                      {detail.region.province_name} · Kode BPS {detail.region.region_code}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-3xl font-black tabular-nums text-slate-950">
                      {numberFormat.format(detail.region.ikk_score)}
                    </p>
                    <TierBadge tier={detail.region.vulnerability_tier} />
                  </div>
                </div>

                <dl className="mt-5 divide-y divide-slate-100 border-y border-slate-100">
                  {detailIndicators.map(({ key, label, unit }) => {
                    const value = detail.region[key];
                    return (
                      <div className="flex items-center justify-between gap-3 py-3" key={key}>
                        <dt className="text-sm text-slate-600">
                          {label}
                          {label === "TPT" && (
                            <Link
                              className="ml-2 text-xs font-semibold text-slate-700 underline underline-offset-2 hover:text-slate-950"
                              href="/glosarium#tpt"
                            >
                              Apa itu?
                            </Link>
                          )}
                          <span className="ml-1 text-xs text-slate-400">
                            (sub-skor {numberFormat.format(detail.sub_scores[key])})
                          </span>
                        </dt>
                        <dd className="text-sm font-bold tabular-nums text-slate-950">
                          {numberFormat.format(value)}
                          {unit}
                        </dd>
                      </div>
                    );
                  })}
                  <div className="flex items-center justify-between gap-3 py-3">
                    <dt className="text-sm text-slate-600">Kejadian bencana</dt>
                    <dd className="text-sm font-bold text-slate-950">
                      {integerFormat.format(detail.region.disaster_events_count)}
                    </dd>
                  </div>
                  <div className="flex items-center justify-between gap-3 py-3">
                    <dt className="text-sm text-slate-600">Penduduk terdampak</dt>
                    <dd className="text-sm font-bold text-slate-950">
                      {integerFormat.format(detail.region.affected_population)} jiwa
                    </dd>
                  </div>
                </dl>

                <section className="mt-5 space-y-3" aria-label="Sitasi sumber">
                  <h3 className="text-sm font-bold text-slate-900">Sumber data</h3>
                  <p className="text-xs leading-5 text-slate-600">
                    <strong className="text-slate-800">BPS:</strong> {detail.sources.bps}
                  </p>
                  <p className="text-xs leading-5 text-slate-600">
                    <strong className="text-slate-800">BNPB:</strong> {detail.sources.bnpb}
                  </p>
                </section>

                <section className="mt-5 space-y-3 border-t border-slate-100 pt-4">
                  <h3 className="text-sm font-bold text-slate-900">Kerja terkait wilayah</h3>
                  <p className="text-xs text-slate-500">
                    Kontrak dan agenda aktif yang ditandai di kabupaten/kota atau provinsi ini.
                  </p>
                  <div className="grid grid-cols-2 gap-2">
                    <Link
                      className="rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-semibold text-slate-800 hover:bg-slate-50"
                      href="/kontrak-faksi"
                    >
                      Kontrak Faksi ({detail.internal_work.faction_contracts.length})
                    </Link>
                    <Link
                      className="rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-semibold text-slate-800 hover:bg-slate-50"
                      href="/events"
                    >
                      Gerak Warga ({detail.internal_work.events.length})
                    </Link>
                  </div>
                  {detail.internal_work.faction_contracts.length > 0 && (
                    <ul className="space-y-2">
                      {detail.internal_work.faction_contracts.map((contract) => (
                        <li
                          className="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700"
                          key={contract.id}
                        >
                          <span className="font-semibold">{contract.title}</span>
                          <span className="ml-2 text-xs text-slate-500">
                            {contract.politician_name}
                          </span>
                        </li>
                      ))}
                    </ul>
                  )}
                  {detail.internal_work.events.length > 0 && (
                    <ul className="space-y-2">
                      {detail.internal_work.events.map((event) => (
                        <li
                          className="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700"
                          key={event.id}
                        >
                          <span className="font-semibold">{event.title}</span>
                          <span className="ml-2 text-xs text-slate-500">
                            {event.region_code ?? event.province_name}
                          </span>
                        </li>
                      ))}
                    </ul>
                  )}
                  {detail.internal_work.faction_contracts.length === 0 &&
                    detail.internal_work.events.length === 0 && (
                      <p className="text-xs text-slate-500">
                        Belum ada kontrak atau agenda yang ditautkan pada wilayah ini.
                      </p>
                    )}
                </section>
              </>
            )}
            {!detail && !detailLoading && !detailError && (
              <p className="text-sm text-slate-500">Pilih wilayah untuk melihat rincian.</p>
            )}
          </aside>
        </div>
      </section>

      <p className="mt-6 text-xs leading-5 text-slate-500">
        Skor IKK adalah indeks komposit untuk perbandingan, bukan pengganti statistik resmi
        masing-masing sumber. Baseline offline merupakan estimasi kurasi yang perlu dicocokkan
        dengan tabel sumber; periksa sitasi dan tahun rilis sebelum menggunakan angka untuk
        keputusan program.
      </p>
    </main>
  );
}
