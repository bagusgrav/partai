"use client";

import Link from "next/link";
import {
  useCallback,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import {
  CartesianGrid,
  Legend,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import {
  getEconomicIndicators,
  getEconomicRegions,
  getGovernmentCabinet,
  getGovernmentNews,
} from "@/lib/api";
import type {
  EconomicIndicatorObservation,
  EconomicIndicatorRegion,
  EconomicIndicatorType,
  GovernmentInsight,
} from "@/types";

type DashboardTab = "ekonomi" | "wawasan";
type TrendData = {
  period: string;
  value?: number;
  comparisonValue?: number;
};
type IndicatorTab = {
  type: EconomicIndicatorType;
  label: string;
  description: string;
};

const indicatorTabByType: Record<EconomicIndicatorType, IndicatorTab> = {
  inflasi: {
    type: "inflasi",
    label: "Inflasi",
    description: "Mengukur kenaikan harga barang dan jasa secara umum.",
  },
  kemiskinan: {
    type: "kemiskinan",
    label: "Kemiskinan",
    description: "Persentase penduduk yang berada di bawah garis kemiskinan.",
  },
  tpt: {
    type: "tpt",
    label: "Pengangguran",
    description: "Persentase angkatan kerja yang tidak memiliki pekerjaan.",
  },
  gini: {
    type: "gini",
    label: "Gini",
    description: "Mengukur ketimpangan distribusi pengeluaran penduduk.",
  },
};
const indicatorTabs = Object.values(indicatorTabByType);

const numberFormatter = new Intl.NumberFormat("id-ID", {
  maximumFractionDigits: 2,
});
const dateFormatter = new Intl.DateTimeFormat("id-ID", {
  dateStyle: "medium",
  timeStyle: "short",
  timeZone: "Asia/Jakarta",
});
const monthFormatter = new Intl.DateTimeFormat("id-ID", {
  month: "short",
  year: "numeric",
  timeZone: "UTC",
});

function formatValue(value: number): string {
  return numberFormatter.format(value);
}

function formatDate(value: string | null): string {
  if (!value) {
    return "Belum tersedia";
  }

  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? "Belum tersedia"
    : dateFormatter.format(date);
}

function formatPeriod(value: string): string {
  const [year, month] = value.split("-");
  if (!month || month === "00") {
    return year;
  }

  const date = new Date(`${year}-${month}-01T00:00:00Z`);
  return Number.isNaN(date.getTime()) ? value : monthFormatter.format(date);
}

function formatYear(value: string): string {
  return value.split("-")[0] ?? value;
}

function toTrendData(
  observations: Array<{
    period_year: number;
    period_month: number | null;
    value: number;
  }>,
): TrendData[] {
  return observations.map(({ period_year, period_month, value }) => ({
    period:
      period_month === null
        ? String(period_year)
        : `${period_year}-${String(period_month).padStart(2, "0")}`,
    value,
  }));
}

function mergeIndicatorData(
  regionalData: TrendData[],
  nationalData: TrendData[],
): TrendData[] {
  const merged = new Map<string, TrendData>();

  for (const point of nationalData) {
    merged.set(point.period, {
      period: point.period,
      comparisonValue: point.value,
    });
  }

  for (const point of regionalData) {
    const existing = merged.get(point.period);
    merged.set(point.period, {
      period: point.period,
      value: point.value,
      ...(existing?.comparisonValue === undefined
        ? {}
        : { comparisonValue: existing.comparisonValue }),
    });
  }

  return [...merged.values()].sort((first, second) =>
    first.period.localeCompare(second.period),
  );
}

function sourceLabel(source: string): string {
  try {
    return new URL(source).hostname.replace(/^www\./, "");
  } catch {
    return source;
  }
}

function TrendChart({
  title,
  description,
  unit,
  data,
  seriesLabel,
  comparisonLabel,
  yearOnly = false,
  loading = false,
  emptyMessage = "Data sedang diperbarui.",
}: {
  title: string;
  description: string;
  unit: string;
  data: TrendData[];
  seriesLabel?: string;
  comparisonLabel?: string;
  yearOnly?: boolean;
  loading?: boolean;
  emptyMessage?: string;
}) {
  return (
    <article className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6">
      <div className="mb-5">
        <h3 className="text-lg font-bold text-text-main">{title}</h3>
        <p className="mt-1 text-sm text-text-muted">{description}</p>
      </div>
      {data.length === 0 ? (
        <p className="flex h-64 items-center justify-center text-sm text-text-muted">
          {loading ? "Memuat data wilayah..." : emptyMessage}
        </p>
      ) : (
        <>
          <div className="h-64 w-full" aria-label={`Grafik ${title}`}>
            <ResponsiveContainer width="100%" height="100%">
              <LineChart data={data} margin={{ top: 8, right: 12, bottom: 4, left: 0 }}>
                <CartesianGrid stroke="#e2e8f0" strokeDasharray="4 4" />
                <XAxis
                  axisLine={false}
                  dataKey="period"
                  tickFormatter={yearOnly ? formatYear : formatPeriod}
                  tickLine={false}
                  tick={{ fill: "#64748b", fontSize: 12 }}
                />
                <YAxis
                  axisLine={false}
                  tickFormatter={formatValue}
                  tickLine={false}
                  tick={{ fill: "#64748b", fontSize: 12 }}
                  width={72}
                />
                <Tooltip
                  formatter={(value, name) => [
                    `${formatValue(Number(value))} ${unit}`,
                    String(name ?? title),
                  ]}
                  labelFormatter={(label) =>
                    (yearOnly ? formatYear : formatPeriod)(String(label))
                  }
                />
                {comparisonLabel && <Legend />}
                <Line
                  activeDot={{ r: 5 }}
                  dataKey="value"
                  dot={{ r: 3 }}
                  name={seriesLabel ?? title}
                  stroke="#0f172a"
                  strokeWidth={3}
                  type="monotone"
                />
                {comparisonLabel && (
                  <Line
                    activeDot={{ r: 4 }}
                    dataKey="comparisonValue"
                    dot={false}
                    name={comparisonLabel}
                    stroke="#64748b"
                    strokeDasharray="6 4"
                    strokeWidth={2}
                    type="monotone"
                  />
                )}
              </LineChart>
            </ResponsiveContainer>
          </div>
          {data.length < 2 && (
            <p className="mt-3 text-xs text-text-muted">
              Tren akan terbentuk setelah tersedia pembaruan pada periode berikutnya.
            </p>
          )}
        </>
      )}
    </article>
  );
}

function MetricCard({
  title,
  indicator,
  loading,
}: {
  title: string;
  indicator: EconomicIndicatorObservation | undefined;
  loading: boolean;
}) {
  return (
    <article className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6">
      <p className="text-sm font-semibold text-text-muted">{title}</p>
      <p className="mt-3 text-2xl font-black text-text-main sm:text-3xl">
        {loading
          ? "Memuat..."
          : indicator
            ? `${formatValue(indicator.value)} ${indicator.unit}`
            : "Data sedang diperbarui"}
      </p>
      {indicator && (
        <>
          <p className="mt-2 text-xs text-text-muted">
            Sumber: {sourceLabel(indicator.source)}
          </p>
          <p className="mt-1 text-xs text-text-muted">
            Periode {formatPeriod(
              `${indicator.period_year}-${String(indicator.period_month ?? "00").padStart(2, "0")}`,
            )}
          </p>
        </>
      )}
    </article>
  );
}

function InsightLink({ item, children }: { item: GovernmentInsight; children: ReactNode }) {
  if (!item.source_url) {
    return <>{children}</>;
  }

  return (
    <a
      className="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
      href={item.source_url}
      rel="noopener noreferrer"
      target="_blank"
    >
      {children}
    </a>
  );
}

export default function EconomicDashboard() {
  const [activeTab, setActiveTab] = useState<DashboardTab>("ekonomi");
  const [indicators, setIndicators] = useState<
    Record<string, EconomicIndicatorObservation[]>
  >({});
  const [lastUpdated, setLastUpdated] = useState<string | null>(null);
  const [exchangeHistory, setExchangeHistory] = useState<TrendData[]>([]);
  const [selectedIndicator, setSelectedIndicator] =
    useState<EconomicIndicatorType>("inflasi");
  const [indicatorHistory, setIndicatorHistory] = useState<{
    regionCode: string;
    type: EconomicIndicatorType;
    data: TrendData[];
    error: string | null;
    loading: boolean;
  } | null>(null);
  const [regions, setRegions] = useState<EconomicIndicatorRegion[]>([]);
  const [selectedRegionCode, setSelectedRegionCode] = useState("9999");
  const [news, setNews] = useState<GovernmentInsight[]>([]);
  const [cabinet, setCabinet] = useState<GovernmentInsight[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadData = useCallback(async () => {
    return Promise.all([
      getEconomicIndicators(),
      getEconomicRegions(),
      getGovernmentNews(),
      getGovernmentCabinet(),
    ] as const);
  }, []);

  useEffect(() => {
    let isCurrent = true;

    async function refresh() {
      try {
        const [economicResponse, regionsResponse, newsResponse, cabinetResponse] =
          await loadData();
        if (!isCurrent) {
          return;
        }
        setIndicators(economicResponse.indicators);
        setLastUpdated(economicResponse.last_updated);
        setExchangeHistory(toTrendData(economicResponse.indicators.kurs_usd ?? []));
        setRegions(regionsResponse.regions);
        setNews(newsResponse.insights);
        setCabinet(cabinetResponse.insights);
        setError(null);
      } catch (requestError) {
        if (isCurrent) {
          setIndicators({});
          setExchangeHistory([]);
          setLastUpdated(null);
          setError(
            requestError instanceof Error
              ? requestError.message
              : "Data ekonomi dan wawasan gagal dimuat.",
          );
        }
      } finally {
        if (isCurrent) {
          setIsLoading(false);
        }
      }
    }

    void refresh();
    const intervalId = window.setInterval(() => {
      void refresh();
    }, 5 * 60 * 1000);

    return () => {
      isCurrent = false;
      window.clearInterval(intervalId);
    };
  }, [loadData]);

  useEffect(() => {
    let isCurrent = true;

    async function refreshIndicatorHistory() {
      setIndicatorHistory({
        regionCode: selectedRegionCode,
        type: selectedIndicator,
        data: [],
        error: null,
        loading: true,
      });

      try {
        const response = await getEconomicIndicators(
          selectedRegionCode,
          selectedIndicator,
        );
        if (isCurrent) {
          const regionalData = toTrendData(response.data);
          const nationalData = toTrendData(
            response.indicators[selectedIndicator] ?? [],
          );
          setIndicatorHistory({
            regionCode: selectedRegionCode,
            type: selectedIndicator,
            data:
              selectedRegionCode === "9999"
                ? regionalData
                : mergeIndicatorData(regionalData, nationalData),
            error: null,
            loading: false,
          });
        }
      } catch (requestError) {
        if (isCurrent) {
          setIndicatorHistory({
            regionCode: selectedRegionCode,
            type: selectedIndicator,
            data: [],
            error: requestError instanceof Error
              ? `Gagal memuat data indikator: ${requestError.message}`
              : "Data indikator untuk wilayah ini gagal dimuat.",
            loading: false,
          });
        }
      }
    }

    void refreshIndicatorHistory();

    return () => {
      isCurrent = false;
    };
  }, [selectedIndicator, selectedRegionCode]);

  const latestIndicator = (name: string) => {
    const observations = indicators[name] ?? [];
    return observations[observations.length - 1];
  };
  const selectedRegion = regions
    .flatMap((province) => [
      { ...province, provinceName: province.region_name },
      ...(province.children ?? []).map((region) => ({
        ...region,
        provinceName: province.region_name,
      })),
    ])
    .find((region) => region.region_code === selectedRegionCode);
  const selectedRegionName =
    selectedRegionCode === "9999"
      ? "Nasional (Indonesia)"
      : selectedRegion
        ? selectedRegion.region_code.endsWith("00")
          ? selectedRegion.region_name
          : `${selectedRegion.region_name}, ${selectedRegion.provinceName}`
        : "Wilayah terpilih";
  const currentIndicatorHistory =
    indicatorHistory?.regionCode === selectedRegionCode &&
    indicatorHistory.type === selectedIndicator
      ? indicatorHistory
      : null;
  const activeIndicatorTab = indicatorTabByType[selectedIndicator];
  const indicatorHistoryData = currentIndicatorHistory?.data ?? [];
  const isIndicatorLoading =
    currentIndicatorHistory === null || currentIndicatorHistory.loading;
  const indicatorError = currentIndicatorHistory?.error ?? null;

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-5 py-10 sm:px-8">
      <section className="mb-8">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-primary">
          Transparansi publik
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
          Ekonomi &amp; Wawasan Indonesia
        </h1>
        <p className="mt-3 max-w-3xl text-sm leading-6 text-text-muted">
          Data ini bersumber dari BPS. Digunakan untuk transparansi dan
          pengawasan publik. Tidak diperlukan akun untuk melihat data.
        </p>
        <p className="mt-3 max-w-3xl text-base leading-7 text-text-muted">
          Pantau indikator ekonomi, perkembangan berita, dan informasi kabinet
          dari sumber yang dapat ditelusuri.
        </p>
      </section>

      <div
        aria-label="Bagian dasbor"
        className="mb-6 flex w-fit gap-2 rounded-full border border-slate-200 bg-surface p-1"
        role="tablist"
      >
        {([
          ["ekonomi", "Ekonomi"],
          ["wawasan", "Wawasan"],
        ] as const).map(([tab, label]) => (
          <button
            aria-controls={`panel-${tab}`}
            aria-selected={activeTab === tab}
            className={`rounded-full px-5 py-2 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary ${
              activeTab === tab
                ? "bg-primary text-white"
                : "text-text-muted hover:bg-primary-light hover:text-primary-hover"
            }`}
            id={`tab-${tab}`}
            key={tab}
            onClick={() => setActiveTab(tab)}
            role="tab"
            type="button"
          >
            {label}
          </button>
        ))}
      </div>

      {error && (
        <div
          className="mb-6 rounded-2xl border border-slate-300 bg-slate-100 px-5 py-4 text-sm text-slate-800"
          role="alert"
        >
          Gagal memuat data: {error}
        </div>
      )}

      {activeTab === "ekonomi" && (
        <section
          aria-labelledby="tab-ekonomi"
          className="space-y-6"
          id="panel-ekonomi"
          role="tabpanel"
        >
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <MetricCard
              indicator={latestIndicator("inflasi")}
              loading={isLoading}
              title="Inflasi"
            />
            <MetricCard
              indicator={latestIndicator("kurs_usd")}
              loading={isLoading}
              title="Kurs USD/IDR"
            />
          </div>
          <Link
            className="inline-flex text-sm font-semibold text-primary-hover underline underline-offset-2 hover:text-primary"
            href="/glosarium#inflasi"
          >
            Apa itu inflasi?
          </Link>
          <p className="text-sm text-text-muted">
            Terakhir diperbarui: {formatDate(lastUpdated)}
          </p>
          <div className="max-w-md">
            <label
              className="mb-2 block text-sm font-semibold text-text-main"
              htmlFor="inflation-region"
            >
              Pilih provinsi atau kabupaten/kota
            </label>
            <select
              className="w-full rounded-xl border border-slate-300 bg-surface px-4 py-3 text-sm text-text-main shadow-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
              id="inflation-region"
              onChange={(event) => setSelectedRegionCode(event.currentTarget.value)}
              value={selectedRegionCode}
            >
              <option value="9999">Nasional (Indonesia)</option>
              {regions.map((province) => (
                <optgroup key={province.region_code} label={province.region_name}>
                  <option value={province.region_code}>
                    Semua wilayah di {province.region_name}
                  </option>
                  {(province.children ?? []).map((region) => (
                    <option key={region.region_code} value={region.region_code}>
                      {region.region_name}
                    </option>
                  ))}
                </optgroup>
              ))}
            </select>
          </div>
          <div
            aria-label="Pilih indikator ekonomi"
            className="flex w-fit flex-wrap gap-2 rounded-full border border-slate-200 bg-surface p-1"
            role="tablist"
          >
            {indicatorTabs.map(({ type, label }) => (
              <button
                aria-selected={selectedIndicator === type}
                className={`rounded-full px-4 py-2 text-sm font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary ${
                  selectedIndicator === type
                    ? "bg-primary text-white"
                    : "text-text-muted hover:bg-primary-light hover:text-primary-hover"
                }`}
                id={`indicator-tab-${type}`}
                key={type}
                onClick={() => setSelectedIndicator(type)}
                role="tab"
                type="button"
              >
                {label}
              </button>
            ))}
          </div>
          <p className="text-sm text-text-muted">
            {activeIndicatorTab.description}
          </p>
          <div className="grid gap-5 lg:grid-cols-2">
            <TrendChart
              data={exchangeHistory}
              description="Perubahan nilai rupiah terhadap dolar AS."
              title="Tren Kurs USD/IDR"
              unit="rupiah per USD"
            />
            <TrendChart
              data={indicatorHistoryData}
              comparisonLabel={
                selectedRegionCode === "9999"
                  ? undefined
                  : "Nasional (Indonesia)"
              }
              description={`Riwayat ${activeIndicatorTab.label.toLowerCase()} untuk ${selectedRegionName} dibandingkan dengan rata-rata nasional.`}
              emptyMessage={
                indicatorError ??
                "Data untuk indikator ini sedang dalam proses sinkronisasi."
              }
              loading={isIndicatorLoading}
              seriesLabel={selectedRegionName}
              title={`Tren ${activeIndicatorTab.label}`}
              unit="%"
              yearOnly={selectedIndicator !== "inflasi"}
            />
          </div>
          <p className="text-xs leading-5 text-text-muted">
            Layanan ini menggunakan API Badan Pusat Statistik (BPS).
          </p>
        </section>
      )}

      {activeTab === "wawasan" && (
        <section
          aria-labelledby="tab-wawasan"
          className="grid gap-6 lg:grid-cols-5"
          id="panel-wawasan"
          role="tabpanel"
        >
          <div className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6 lg:col-span-2">
            <h2 className="text-xl font-bold text-text-main">Kabinet</h2>
            <p className="mt-1 text-sm text-text-muted">
              Informasi kabinet dikelola manual dan ditampilkan sesuai data yang
              tersedia.
            </p>
            <ul className="mt-5 space-y-3">
              {cabinet.map((item) => (
                <li
                  className="rounded-xl border border-slate-100 bg-slate-50 p-4"
                  key={item.id}
                >
                  <InsightLink item={item}>
                    <h3 className="font-bold text-text-main">{item.title}</h3>
                    {item.source_url && (
                      <span className="mt-1 block text-xs text-primary">
                        Buka sumber
                      </span>
                    )}
                  </InsightLink>
                  <p className="mt-2 text-sm leading-6 text-text-muted">
                    {item.content}
                  </p>
                </li>
              ))}
              {!isLoading && cabinet.length === 0 && (
                <li className="rounded-xl bg-slate-50 px-4 py-6 text-sm text-text-muted">
                  Belum ada informasi kabinet. Data dapat ditambahkan secara manual.
                </li>
              )}
            </ul>
          </div>

          <div className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6 lg:col-span-3">
            <h2 className="text-xl font-bold text-text-main">Berita Terkini</h2>
            <p className="mt-1 text-sm text-text-muted">
              Berita dari RSS ANTARA dengan tautan ke sumber asli.
            </p>
            <ul className="mt-5 space-y-4">
              {news.map((item) => (
                <li
                  className="border-b border-slate-100 pb-4 last:border-0 last:pb-0"
                  key={item.id}
                >
                  <InsightLink item={item}>
                    <h3 className="font-bold leading-6 text-text-main hover:text-primary">
                      {item.title}
                    </h3>
                  </InsightLink>
                  <p className="mt-1 text-xs text-text-muted">
                    {formatDate(item.published_at)} ·{" "}
                    {item.source_url ? sourceLabel(item.source_url) : "ANTARA"}
                  </p>
                  {item.content && (
                    <p className="mt-2 line-clamp-3 text-sm leading-6 text-text-muted">
                      {item.content}
                    </p>
                  )}
                </li>
              ))}
              {!isLoading && news.length === 0 && (
                <li className="rounded-xl bg-slate-50 px-4 py-6 text-sm text-text-muted">
                  Belum ada berita. Feed diperbarui otomatis setiap jam.
                </li>
              )}
            </ul>
          </div>
        </section>
      )}
    </main>
  );
}
