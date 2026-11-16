"use client";

import { useEffect, useMemo, useState } from "react";
import {
  factionContractStatuses,
  getFactionContracts,
  type FactionContract,
  type FactionContractStatus,
} from "@/lib/api/contracts";

const statusLabels: Record<FactionContractStatus, string> = {
  BELUM_DIMULAI: "Belum dimulai",
  SEDANG_BERJALAN: "Sedang berjalan",
  TERPENUHI: "Terpenuhi",
  GAGAL: "Gagal",
};

const statusStyles: Record<FactionContractStatus, string> = {
  BELUM_DIMULAI: "border-slate-300 bg-slate-100 text-slate-800",
  SEDANG_BERJALAN: "border-amber-300 bg-amber-50 text-amber-900",
  TERPENUHI: "border-emerald-300 bg-emerald-50 text-emerald-900",
  GAGAL: "border-red-300 bg-red-50 text-red-900",
};

const dateFormatter = new Intl.DateTimeFormat("id-ID", {
  day: "numeric",
  month: "long",
  year: "numeric",
  timeZone: "UTC",
});

function formatDeadline(deadline: string | null): string {
  if (!deadline) {
    return "Tanpa tenggat";
  }

  const date = new Date(`${deadline}T00:00:00Z`);
  return Number.isNaN(date.getTime())
    ? "Tenggat tidak valid"
    : dateFormatter.format(date);
}

function ContractCard({ contract }: { contract: FactionContract }) {
  const isFailed = contract.status === "GAGAL";
  const isFulfilled = contract.status === "TERPENUHI";

  return (
    <article
      className={`rounded-2xl border-l-4 bg-white p-5 shadow-sm sm:p-6 ${
        isFailed
          ? "border-l-red-600"
          : isFulfilled
            ? "border-l-emerald-600"
            : "border-l-slate-400"
      }`}
    >
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p className="text-sm font-bold uppercase tracking-wide text-slate-500">
            {contract.politician_name}
          </p>
          <h2 className="mt-2 text-xl font-black leading-snug text-slate-950">
            {contract.title}
          </h2>
        </div>
        <span
          className={`rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-wide ${statusStyles[contract.status]}`}
        >
          {statusLabels[contract.status]}
        </span>
      </div>
      <p className="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700">
        {contract.description}
      </p>
      <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
        <p className="text-sm text-slate-600">
          Tenggat: <span className="font-semibold text-slate-900">{formatDeadline(contract.deadline)}</span>
        </p>
        {contract.evidence_url && (
          <a
            className={`inline-flex min-h-11 items-center justify-center rounded-lg px-4 py-2 text-sm font-bold text-white transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 ${
              isFailed
                ? "bg-red-700 hover:bg-red-800"
                : "bg-slate-900 hover:bg-slate-700"
            }`}
            href={contract.evidence_url}
            rel="noopener noreferrer"
            target="_blank"
          >
            Lihat Bukti
          </a>
        )}
      </div>
    </article>
  );
}

export default function ContractsDashboard() {
  const [contracts, setContracts] = useState<FactionContract[]>([]);
  const [selectedStatus, setSelectedStatus] = useState<
    FactionContractStatus | "SEMUA"
  >("SEMUA");
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let isCurrent = true;

    async function loadContracts() {
      try {
        const result = await getFactionContracts();
        if (isCurrent) {
          setContracts(result);
          setError(null);
        }
      } catch (loadError) {
        if (isCurrent) {
          setError(
            loadError instanceof Error
              ? loadError.message
              : "Data kontrak belum dapat dimuat. Silakan coba kembali.",
          );
        }
      } finally {
        if (isCurrent) {
          setIsLoading(false);
        }
      }
    }

    void loadContracts();
    return () => {
      isCurrent = false;
    };
  }, []);

  const filteredContracts = useMemo(
    () =>
      selectedStatus === "SEMUA"
        ? contracts
        : contracts.filter((contract) => contract.status === selectedStatus),
    [contracts, selectedStatus],
  );
  const fulfilledCount = contracts.filter(
    (contract) => contract.status === "TERPENUHI",
  ).length;
  const failedCount = contracts.filter(
    (contract) => contract.status === "GAGAL",
  ).length;

  return (
    <main className="flex-1 bg-slate-50">
      <section className="bg-slate-950 text-white">
        <div className="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-amber-300">
            Akuntabilitas publik
          </p>
          <div className="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div className="max-w-3xl">
              <h1 className="text-4xl font-black leading-tight sm:text-5xl">
                Kontrak Faksi
              </h1>
              <p className="mt-5 text-lg leading-8 text-slate-300">
                Janji kampanye bukan cek kosong. Pantau status, tenggat, dan
                bukti kerja politisi secara terbuka.
              </p>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="rounded-xl border border-emerald-400/40 bg-emerald-400/10 p-4">
                <p className="text-xs font-bold uppercase tracking-wide text-emerald-200">
                  Terpenuhi
                </p>
                <p className="mt-1 text-3xl font-black text-white">
                  {fulfilledCount}
                </p>
              </div>
              <div className="rounded-xl border border-red-400/40 bg-red-400/10 p-4">
                <p className="text-xs font-bold uppercase tracking-wide text-red-200">
                  Gagal
                </p>
                <p className="mt-1 text-3xl font-black text-white">
                  {failedCount}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section
        aria-labelledby="contract-list-heading"
        className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
      >
        <div className="flex flex-wrap items-end justify-between gap-4">
          <div>
            <p className="text-sm font-bold uppercase tracking-wide text-slate-500">
              Catatan publik
            </p>
            <h2
              className="mt-1 text-2xl font-black text-slate-950"
              id="contract-list-heading"
            >
              Rekam jejak janji
            </h2>
          </div>
          <label className="flex items-center gap-2 text-sm font-semibold text-slate-700">
            Filter status
            <select
              className="min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900"
              onChange={(event) =>
                setSelectedStatus(
                  event.target.value as FactionContractStatus | "SEMUA",
                )
              }
              value={selectedStatus}
            >
              <option value="SEMUA">Semua status</option>
              {factionContractStatuses.map((status) => (
                <option key={status} value={status}>
                  {statusLabels[status]}
                </option>
              ))}
            </select>
          </label>
        </div>

        {isLoading ? (
          <p className="mt-8 rounded-xl border border-slate-200 bg-white p-6 text-slate-600">
            Memuat kontrak publik...
          </p>
        ) : error ? (
          <p
            className="mt-8 rounded-xl border border-red-200 bg-red-50 p-6 font-semibold text-red-900"
            role="alert"
          >
            {error}
          </p>
        ) : filteredContracts.length === 0 ? (
          <p className="mt-8 rounded-xl border border-slate-200 bg-white p-6 text-slate-600">
            Belum ada janji untuk status ini.
          </p>
        ) : (
          <div className="mt-6 grid gap-4 lg:grid-cols-2">
            {filteredContracts.map((contract) => (
              <ContractCard contract={contract} key={contract.id} />
            ))}
          </div>
        )}
      </section>
    </main>
  );
}
