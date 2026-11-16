"use client";

import {
  useCallback,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import {
  CartesianGrid,
  Line,
  LineChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import {
  getEconomicHistory,
  getEconomicSummary,
  getGovernmentCabinet,
  getGovernmentNews,
} from "@/lib/api";
import type {
  EconomicIndicator,
  GovernmentInsight,
} from "@/types";

type DashboardTab = "ekonomi" | "wawasan";
type TrendData = { period: string; value: number };

const numberFormatter = new Intl.NumberFormat("id-ID", {
  maximumFractionDigits: 2,
});
const dateFormatter = new Intl.DateTimeFormat("id-ID", {
  dateStyle: "medium",
  timeStyle: "short",
  timeZone: "Asia/Jakarta",
});
const dayFormatter = new Intl.DateTimeFormat("id-ID", {
  day: "numeric",
  month: "short",
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

function formatDay(value: string): string {
  const date = new Date(`${value}T00:00:00Z`);
  return Number.isNaN(date.getTime()) ? value : dayFormatter.format(date);
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
}: {
  title: string;
  description: string;
  unit: string;
  data: TrendData[];
}) {
  return (
    <article className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm sm:p-6">
      <div className="mb-5">
        <h3 className="text-lg font-bold text-text-main">{title}</h3>
        <p className="mt-1 text-sm text-text-muted">{description}</p>
      </div>
      {data.length === 0 ? (
        <p className="flex h-64 items-center justify-center text-sm text-text-muted">
          Belum ada riwayat untuk ditampilkan.
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
                  tickFormatter={formatDay}
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
                  formatter={(value) => [`${formatValue(Number(value))} ${unit}`, title]}
                  labelFormatter={(label) => formatDay(String(label))}
                />
                <Line
                  activeDot={{ r: 5 }}
                  dataKey="value"
                  dot={{ r: 3 }}
                  name={title}
                  stroke="#0f172a"
                  strokeWidth={3}
                  type="monotone"
                />
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
  indicator: EconomicIndicator | undefined;
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
            : "Belum tersedia"}
      </p>
      {indicator && (
        <>
          <p className="mt-2 text-xs text-text-muted">
            Sumber: {sourceLabel(indicator.source)}
          </p>
          <p className="mt-1 text-xs text-text-muted">
            Periode {indicator.period}
            {indicator.trend ? ` · Tren ${indicator.trend}` : ""}
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
  const [indicators, setIndicators] = useState<EconomicIndicator[]>([]);
  const [lastUpdated, setLastUpdated] = useState<string | null>(null);
  const [exchangeHistory, setExchangeHistory] = useState<TrendData[]>([]);
  const [inflationHistory, setInflationHistory] = useState<TrendData[]>([]);
  const [news, setNews] = useState<GovernmentInsight[]>([]);
  const [cabinet, setCabinet] = useState<GovernmentInsight[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadData = useCallback(async () => {
    return Promise.all([
      getEconomicSummary(),
      getEconomicHistory("exchange_rate"),
      getEconomicHistory("inflation"),
      getGovernmentNews(),
      getGovernmentCabinet(),
    ] as const);
  }, []);

  useEffect(() => {
    let isCurrent = true;

    async function refresh() {
      try {
        const [
          summaryResponse,
          exchangeResponse,
          inflationResponse,
          newsResponse,
          cabinetResponse,
        ] = await loadData();
        if (!isCurrent) {
          return;
        }
        setIndicators(summaryResponse.indicators);
        setLastUpdated(summaryResponse.last_updated);
        setExchangeHistory(
          exchangeResponse.data.map(({ period, value }) => ({ period, value })),
        );
        setInflationHistory(
          inflationResponse.data.map(({ period, value }) => ({ period, value })),
        );
        setNews(newsResponse.insights);
        setCabinet(cabinetResponse.insights);
        setError(null);
      } catch (requestError) {
        if (isCurrent) {
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

  const indicatorsByType = new Map(
    indicators.map((indicator) => [indicator.indicator_type, indicator] as const),
  );

  return (
    <main className="mx-auto w-full max-w-7xl flex-1 px-5 py-10 sm:px-8">
      <section className="mb-8">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-primary">
          Transparansi publik
        </p>
        <h1 className="mt-2 text-3xl font-black tracking-tight text-text-main sm:text-4xl">
          Ekonomi &amp; Wawasan Indonesia
        </h1>
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
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <MetricCard
              indicator={indicatorsByType.get("debt")}
              loading={isLoading}
              title="Utang Pemerintah"
            />
            <MetricCard
              indicator={indicatorsByType.get("apbn")}
              loading={isLoading}
              title="APBN"
            />
            <MetricCard
              indicator={indicatorsByType.get("inflation")}
              loading={isLoading}
              title="Inflasi"
            />
            <MetricCard
              indicator={indicatorsByType.get("exchange_rate")}
              loading={isLoading}
              title="Kurs USD/IDR"
            />
          </div>
          <p className="text-sm text-text-muted">
            Terakhir diperbarui: {formatDate(lastUpdated)}
          </p>
          <div className="grid gap-5 lg:grid-cols-2">
            <TrendChart
              data={exchangeHistory}
              description="Perubahan nilai rupiah terhadap dolar AS."
              title="Tren Kurs USD/IDR"
              unit="rupiah per USD"
            />
            <TrendChart
              data={inflationHistory}
              description="Riwayat indikator inflasi yang tersimpan."
              title="Tren Inflasi"
              unit="persen"
            />
          </div>
          <p className="text-xs leading-5 text-text-muted">
            Ringkasan utang, APBN, dan inflasi saat ini merupakan simulasi, bukan
            data resmi. Kurs diambil dari API publik ExchangeRate-API. Data berita
            diperbarui otomatis melalui RSS ANTARA.
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
