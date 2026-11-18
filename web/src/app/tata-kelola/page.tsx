"use client";

import Link from "next/link";
import { useEffect, useState, type FormEvent } from "react";
import { SSO_URL } from "@/lib/api/auth";
import {
  applyForOrganizationSeat,
  getMySeatEligibility,
  getOrganizationTree,
  type OrganizationSeat,
  type SeatEligibility,
  type OrganizationTreeResponse,
} from "@/lib/api";

type Filter = "all" | "open" | "filled";

function SeatNode({ seat }: { seat: OrganizationSeat }) {
  const open = seat.status === "open" || seat.status === "sortition_pool";
  return (
    <article className="relative rounded-xl border border-slate-700 bg-slate-950 p-4 text-slate-100">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <div>
          <p className="text-xs font-bold uppercase tracking-wider text-slate-400">{seat.seat_code}</p>
          <h3 className="mt-1 font-extrabold">{seat.title}</h3>
        </div>
        <span className={`rounded-full px-2.5 py-1 text-xs font-bold ${open ? "bg-white text-slate-950" : "bg-slate-700 text-white"}`}>
          {open ? "Siap Diisi" : "Terisi"}
        </span>
      </div>
      <p className="mt-3 text-sm leading-6 text-slate-300">{seat.answering_scope}</p>
      <p className="mt-3 text-xs text-slate-400">
        Mastery Level {seat.required_mastery_level}
        {seat.required_domain ? ` · Domain ${seat.required_domain}` : ""}
        {seat.required_min_participation > 0 ? ` · Partisipasi ${seat.required_min_participation}` : ""}
      </p>
      {seat.holder_display_name && (
        <p className="mt-2 text-sm font-semibold">
          Pemegang: {seat.holder_display_name}
        </p>
      )}
      {seat.term_ends_at && (
        <p className="text-xs text-slate-400">Sunset clause: {seat.term_ends_at}</p>
      )}
      {seat.children && seat.children.length > 0 && (
        <div className="mt-4 space-y-3 border-l border-slate-700 pl-4">
          {seat.children.map((child) => <SeatNode key={child.id} seat={child} />)}
        </div>
      )}
    </article>
  );
}

export default function GovernancePage() {
  const [treeData, setTreeData] = useState<OrganizationTreeResponse | null>(null);
  const [eligibility, setEligibility] = useState<SeatEligibility[]>([]);
  const [filter, setFilter] = useState<Filter>("all");
  const [error, setError] = useState<string | null>(null);
  const [statement, setStatement] = useState("");
  const [selectedSeat, setSelectedSeat] = useState("");
  const [notice, setNotice] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    void getOrganizationTree()
      .then(setTreeData)
      .catch((loadError: unknown) => setError(loadError instanceof Error ? loadError.message : "Data tata kelola gagal dimuat."));

    void getMySeatEligibility()
      .then((result) => setEligibility(result.seats))
      .catch(() => setEligibility([]));
  }, []);

  async function handleApply(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selectedSeat || statement.trim().length < 20) return;
    setSubmitting(true);
    setNotice(null);
    setError(null);
    try {
      await applyForOrganizationSeat(selectedSeat, statement.trim(), crypto.randomUUID());
      setNotice("Pengajuan Anda telah masuk ke kolam kandidat kursi.");
      setStatement("");
      const result = await getMySeatEligibility();
      setEligibility(result.seats);
    } catch (submitError: unknown) {
      setError(submitError instanceof Error ? submitError.message : "Pengajuan gagal dikirim.");
    } finally {
      setSubmitting(false);
    }
  }

  const visibleSeats = treeData?.seats.filter((seat) =>
    filter === "all"
      ? true
      : filter === "open"
        ? ["open", "sortition_pool", "term_expiring"].includes(seat.status)
        : seat.status === "filled",
  ) ?? [];

  return (
    <main className="min-h-screen bg-slate-50">
      <section className="bg-slate-950 text-white">
        <div className="mx-auto max-w-7xl px-6 py-16 sm:px-8">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Akuntabilitas terbuka</p>
          <h1 className="mt-4 text-4xl font-black tracking-tight sm:text-5xl">Tata Kelola Terstruktur</h1>
          <p className="mt-5 max-w-3xl text-lg leading-8 text-slate-300">
            Empat lapis pemisahan kekuasaan, pohon mandat, dan kursi terbuka yang dinilai berdasarkan kapasitas—bukan uang atau popularitas.
          </p>
          <Link className="mt-6 inline-flex font-bold underline underline-offset-4" href="/recall">Buka mekanisme Recall →</Link>
        </div>
      </section>

      <div className="mx-auto max-w-7xl px-6 py-10 sm:px-8">
        {error && <p className="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 font-semibold text-red-900" role="alert">{error}</p>}
        {notice && <p className="mb-6 rounded-xl border border-slate-300 bg-white p-4 font-semibold text-slate-800" role="status">{notice}</p>}

        <section>
          <p className="text-sm font-black uppercase tracking-[0.16em] text-slate-500">Siapa Menjawab Apa</p>
          <h2 className="mt-2 text-3xl font-black text-slate-950">Matriks 4 Lapis Kekuasaan</h2>
          <div className="mt-6 grid gap-4 md:grid-cols-2">
            {treeData?.responsibility_matrix.map((item) => (
              <article className="rounded-2xl border border-slate-200 bg-white p-6" key={item.layer}>
                <p className="text-xs font-black uppercase tracking-wide text-slate-500">{item.layer.replaceAll("_", " ")}</p>
                <h3 className="mt-2 text-xl font-extrabold text-slate-950">{item.title}</h3>
                <p className="mt-3 leading-7 text-slate-600">{item.answers}</p>
              </article>
            ))}
          </div>
        </section>

        <section className="mt-14">
          <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-black uppercase tracking-[0.16em] text-slate-500">Pohon struktur</p>
              <h2 className="mt-2 text-3xl font-black text-slate-950">Organigram Terbuka</h2>
              {treeData && (
                <p className="mt-2 text-sm text-slate-600">{treeData.summary.total_seats} kursi · {treeData.summary.filled_seats} terisi · {treeData.summary.open_seats} terbuka</p>
              )}
            </div>
            <div className="flex flex-wrap gap-2" aria-label="Filter kursi">
              {([
                ["all", "Semua Kursi"],
                ["open", "Kursi Terbuka Siap Diisi"],
                ["filled", "Kursi Terisi"],
              ] as const).map(([value, label]) => (
                <button
                  className={`min-h-10 rounded-lg border px-4 text-sm font-bold ${filter === value ? "border-slate-950 bg-slate-950 text-white" : "border-slate-300 bg-white text-slate-700"}`}
                  key={value}
                  onClick={() => setFilter(value)}
                  type="button"
                >
                  {label}
                </button>
              ))}
            </div>
          </div>

          {treeData ? (
            <div className="mt-6 grid gap-4 md:grid-cols-2">
              {visibleSeats.map((seat) => <SeatNode key={seat.id} seat={seat} />)}
            </div>
          ) : (
            <p className="mt-6 rounded-xl border border-slate-200 bg-white p-6 text-slate-600">Memuat pohon organisasi...</p>
          )}
        </section>

        <section className="mt-14 rounded-3xl border border-slate-300 bg-slate-950 p-6 text-white sm:p-8">
          <p className="text-sm font-black uppercase tracking-[0.16em] text-slate-400">Kelayakan pribadi</p>
          <h2 className="mt-2 text-3xl font-black">Siap Mengisi Kursi Mana?</h2>
          {eligibility.length === 0 ? (
            <div className="mt-5">
              <p className="text-slate-300">Masuk melalui SSO untuk menghitung Mastery Level dan Participation Score Anda.</p>
              <a className="mt-5 inline-flex min-h-11 items-center rounded-lg bg-white px-5 font-bold text-slate-950" href={SSO_URL}>Masuk melalui SSO</a>
            </div>
          ) : (
            <div className="mt-6 grid gap-4 lg:grid-cols-2">
              {eligibility.map((seat) => (
                <article className="rounded-xl border border-slate-700 bg-slate-900 p-5" key={seat.id}>
                  <div className="flex items-start justify-between gap-3">
                    <h3 className="font-extrabold">{seat.title}</h3>
                    <span className={`rounded-full px-2 py-1 text-xs font-bold ${seat.eligible ? "bg-white text-slate-950" : "bg-slate-700 text-slate-200"}`}>
                      {seat.eligible ? "Siap" : "Belum Siap"}
                    </span>
                  </div>
                  {seat.reasons.length > 0 && <ul className="mt-3 list-disc pl-5 text-sm leading-6 text-slate-300">{seat.reasons.map((reason) => <li key={reason}>{reason}</li>)}</ul>}
                  {seat.eligible && ["open", "sortition_pool"].includes(seat.status) && (
                    <button className="mt-4 min-h-10 rounded-lg bg-white px-4 text-sm font-bold text-slate-950" onClick={() => setSelectedSeat(seat.id)} type="button">
                      Pilih untuk pengajuan
                    </button>
                  )}
                </article>
              ))}
            </div>
          )}

          {selectedSeat && (
            <form className="mt-6 rounded-xl border border-slate-700 p-5" onSubmit={handleApply}>
              <label className="block text-sm font-bold" htmlFor="seat-statement">Pernyataan integritas & rencana kerja</label>
              <textarea
                className="mt-2 min-h-28 w-full rounded-lg border border-slate-600 bg-slate-950 px-4 py-3 text-white"
                id="seat-statement"
                minLength={20}
                onChange={(event) => setStatement(event.target.value)}
                required
                value={statement}
              />
              <button className="mt-4 min-h-11 rounded-lg bg-white px-5 font-bold text-slate-950 disabled:bg-slate-500" disabled={submitting} type="submit">
                {submitting ? "Mengirim..." : "Masuk Kolam Kandidat"}
              </button>
            </form>
          )}
        </section>
      </div>
    </main>
  );
}
