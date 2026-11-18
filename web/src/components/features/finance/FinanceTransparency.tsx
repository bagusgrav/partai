"use client";

import { FormEvent, useEffect, useState } from "react";
import {
  getFinanceLedger,
  getFinanceSummary,
  getMyDues,
  redirectToSso,
  submitDonation,
  submitMyDues,
  type FinanceLedgerEntry,
  type FinanceSummary,
  type MyDues,
} from "@/lib/api";

const money = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  maximumFractionDigits: 0,
});

const channelLabels: Record<string, string> = {
  member_dues: "Iuran Anggota",
  individual_donation: "Sumbangan Individu",
  corporate_donation: "Sumbangan Badan Usaha",
  political_education_expense: "Pendidikan Politik",
  legal_aid_expense: "Bantuan Hukum",
  operational_expense: "Operasional",
  audit_reversal: "Koreksi Pembalik",
};

export default function FinanceTransparency() {
  const [summary, setSummary] = useState<FinanceSummary | null>(null);
  const [ledger, setLedger] = useState<FinanceLedgerEntry[]>([]);
  const [dues, setDues] = useState<MyDues | null>(null);
  const [tab, setTab] = useState<"ledger" | "dues" | "donation">("ledger");
  const [category, setCategory] = useState("Semua");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    let current = true;
    Promise.all([getFinanceSummary(), getFinanceLedger({ per_page: "30" })])
      .then(([nextSummary, nextLedger]) => {
        if (!current) return;
        setSummary(nextSummary);
        setLedger(nextLedger.ledger.data);
      })
      .catch((reason: unknown) => {
        if (current) setError(reason instanceof Error ? reason.message : "Data keuangan gagal dimuat.");
      });

    getMyDues().then((next) => {
      if (current) setDues(next);
    }).catch(() => undefined);

    return () => { current = false; };
  }, []);

  async function loadDues() {
    try {
      setDues(await getMyDues());
    } catch {
      redirectToSso();
    }
  }

  async function submitDues(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");
    setMessage("");
    const form = new FormData(event.currentTarget);
    try {
      await submitMyDues({
        amount_idr: Number(form.get("amount")),
        transaction_date: String(form.get("date")),
        evidence_reference: String(form.get("reference")),
        legal_declaration_accepted: true,
      });
      setMessage("Konfirmasi iuran berhasil dikirim dan menunggu audit.");
      await loadDues();
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : "Iuran gagal dikirim.");
    }
  }

  async function submitPublicDonation(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setError("");
    setMessage("");
    const form = new FormData(event.currentTarget);
    try {
      await submitDonation({
        donor_type: String(form.get("donor_type")) as "individual" | "corporate",
        contributor_name: String(form.get("name")),
        identity_number: String(form.get("identity")),
        amount_idr: Number(form.get("amount")),
        transaction_date: String(form.get("date")),
        purpose_or_program: String(form.get("purpose") || "Dukungan Operasional & Pendidikan Politik"),
        evidence_reference: String(form.get("reference")),
        legal_declaration_accepted: true,
      });
      setMessage("Konfirmasi sumbangan diterima dengan status menunggu audit.");
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : "Sumbangan gagal dikirim.");
    }
  }

  const filteredLedger = ledger.filter((entry) => {
    if (category === "Semua") return true;
    if (category === "Iuran Anggota") return entry.channel === "member_dues";
    if (category === "Sumbangan Individu") return entry.channel === "individual_donation";
    if (category === "Sumbangan Badan Usaha") return entry.channel === "corporate_donation";
    return entry.entry_type === "outflow" || entry.entry_type === "reversal";
  });

  return (
    <section className="mx-auto w-full max-w-7xl px-5 pb-10 sm:px-8">
      <div className="mb-6 border-t border-slate-200 pt-8">
        <p className="text-sm font-bold uppercase tracking-[0.16em] text-slate-600">Keuangan partai</p>
        <h2 className="mt-2 text-2xl font-black text-text-main">Transparansi Keuangan</h2>
      </div>

      {error && <div className="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{error}</div>}
      {message && <div className="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{message}</div>}

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {[
          ["Pemasukan terverifikasi", summary?.verified_inflow_idr],
          ["Pengeluaran terverifikasi", summary?.verified_outflow_idr],
          ["Saldo kas bersih", summary?.net_cash_balance_idr],
          ["Rasio pendidikan politik", summary ? `${(summary.political_education_ratio * 100).toFixed(2)}%` : null],
        ].map(([label, value]) => (
          <article key={String(label)} className="rounded-2xl border border-slate-200 bg-surface p-5 shadow-sm">
            <p className="text-sm font-semibold text-text-muted">{label}</p>
            <p className="mt-3 text-2xl font-black text-text-main">
              {typeof value === "number" ? money.format(value) : value ?? "Memuat..."}
            </p>
          </article>
        ))}
      </div>

      {summary && (
        <div className="mt-4 flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm">
          <span className={`rounded-full px-3 py-1 font-bold ${summary.chain_integrity.valid ? "bg-emerald-100 text-emerald-800" : "bg-red-100 text-red-800"}`}>
            SHA-256 {summary.chain_integrity.valid ? "terverifikasi" : "rusak"}
          </span>
          <span className="rounded-full bg-slate-200 px-3 py-1 font-bold text-slate-700">
            Pendidikan politik: target {(summary.political_education_target_ratio * 100).toFixed(0)}%
          </span>
          <span className="rounded-full bg-slate-200 px-3 py-1 text-slate-700">
            {summary.chain_integrity.total_entries} entri terantai
          </span>
        </div>
      )}

      <div className="mt-6 flex flex-wrap gap-2" role="tablist">
        {([["ledger", "Buku Besar"], ["dues", "Iuran Anggota"], ["donation", "Sumbangan Publik"]] as const).map(([key, label]) => (
          <button key={key} type="button" onClick={() => setTab(key)} className={`rounded-full px-4 py-2 text-sm font-bold ${tab === key ? "bg-slate-900 text-white" : "border border-slate-300 bg-white text-slate-700"}`}>
            {label}
          </button>
        ))}
      </div>

      {tab === "ledger" && (
        <div className="mt-4 rounded-2xl border border-slate-200 bg-surface shadow-sm">
          <div className="flex flex-wrap gap-2 border-b border-slate-200 p-4">
            {["Semua", "Iuran Anggota", "Sumbangan Individu", "Sumbangan Badan Usaha", "Pengeluaran Program"].map((item) => (
              <button key={item} type="button" onClick={() => setCategory(item)} className={`rounded-full px-3 py-1.5 text-xs font-bold ${category === item ? "bg-slate-900 text-white" : "bg-slate-100 text-slate-700"}`}>{item}</button>
            ))}
          </div>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[800px] text-left text-sm">
              <thead className="bg-slate-50 text-xs uppercase text-text-muted"><tr><th className="px-5 py-3">Urutan</th><th className="px-5 py-3">Tanggal</th><th className="px-5 py-3">Kategori</th><th className="px-5 py-3">Penyumbang / Tujuan</th><th className="px-5 py-3">Nominal</th><th className="px-5 py-3">Hash</th></tr></thead>
              <tbody className="divide-y divide-slate-100">
                {filteredLedger.map((entry) => (
                  <tr key={entry.id}>
                    <td className="px-5 py-4 font-mono text-xs">{entry.sequence}</td>
                    <td className="px-5 py-4">{entry.transaction_date}</td>
                    <td className="px-5 py-4">{channelLabels[entry.channel] ?? entry.channel}</td>
                    <td className="px-5 py-4"><p className="font-semibold">{entry.contributor_display_name}</p><p className="mt-1 text-xs text-text-muted">{entry.purpose_or_program}</p></td>
                    <td className={`px-5 py-4 font-bold ${entry.entry_type === "outflow" || entry.entry_type === "reversal" ? "text-red-700" : "text-emerald-700"}`}>{entry.entry_type === "outflow" ? "-" : ""}{money.format(entry.amount_idr)}</td>
                    <td className="px-5 py-4 font-mono text-xs" title={entry.hash}>{entry.hash.slice(0, 14)}…</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {tab === "dues" && (
        <div className="mt-4 grid gap-5 lg:grid-cols-2">
          {dues ? (
            <>
              <div className="rounded-2xl border border-slate-200 bg-surface p-5">
                <h3 className="font-bold">Kuota iuran {dues.year}</h3>
                <p className="mt-3 text-2xl font-black">{money.format(dues.annual_accumulated_idr)}</p>
                <p className="mt-1 text-sm text-text-muted">Sisa {money.format(dues.annual_remaining_idr)} dari plafon {money.format(dues.annual_limit_idr)}</p>
              </div>
              <form onSubmit={submitDues} className="rounded-2xl border border-slate-200 bg-surface p-5">
                <h3 className="font-bold">Konfirmasi setor iuran</h3>
                <div className="mt-4 grid gap-3">
                  <input name="amount" type="number" min="10000" required placeholder="Nominal Rupiah" className="rounded-lg border border-slate-300 px-3 py-2" />
                  <input name="date" type="date" required className="rounded-lg border border-slate-300 px-3 py-2" />
                  <input name="reference" required placeholder="Nominal / referensi transfer" className="rounded-lg border border-slate-300 px-3 py-2" />
                  <button className="rounded-lg bg-slate-900 px-4 py-2.5 font-bold text-white" type="submit">Kirim Konfirmasi</button>
                </div>
              </form>
            </>
          ) : (
            <div className="rounded-2xl border border-slate-200 bg-surface p-6 lg:col-span-2">
              <h3 className="text-lg font-bold">Riwayat iuran pribadi memerlukan masuk SSO</h3>
              <p className="mt-2 text-sm text-text-muted">Pembayaran iuran tidak menambah skor reputasi maupun hak suara.</p>
              <button type="button" onClick={redirectToSso} className="mt-4 rounded-lg bg-slate-900 px-4 py-2.5 font-bold text-white">Masuk melalui SSO</button>
            </div>
          )}
        </div>
      )}

      {tab === "donation" && (
        <form onSubmit={submitPublicDonation} className="mt-4 grid gap-3 rounded-2xl border border-slate-200 bg-surface p-5 sm:grid-cols-2">
          <div className="sm:col-span-2 rounded-lg bg-slate-100 p-3 text-sm text-slate-700">
            Batas tahunan UU Parpol: individu Rp 1 Miliar; badan usaha Rp 7,5 Miliar. Identitas akan di-hash dan dimasking.
          </div>
          <select name="donor_type" required className="rounded-lg border border-slate-300 px-3 py-2"><option value="individual">Individu</option><option value="corporate">Badan Usaha</option></select>
          <input name="name" required placeholder="Nama / nama badan usaha" className="rounded-lg border border-slate-300 px-3 py-2" />
          <input name="identity" required placeholder="NIK 16 digit atau NPWP 15/16 digit" className="rounded-lg border border-slate-300 px-3 py-2" />
          <input name="amount" type="number" min="10000" required placeholder="Nominal Rupiah" className="rounded-lg border border-slate-300 px-3 py-2" />
          <input name="date" type="date" required className="rounded-lg border border-slate-300 px-3 py-2" />
          <input name="reference" required placeholder="Nomor referensi transfer" className="rounded-lg border border-slate-300 px-3 py-2" />
          <input name="purpose" placeholder="Tujuan atau program" className="rounded-lg border border-slate-300 px-3 py-2 sm:col-span-2" />
          <label className="sm:col-span-2 flex gap-2 text-sm"><input type="checkbox" required /> Saya menyatakan dana sah dan bukan dari BUMN/BUMD, lembaga negara, asing, maupun tindak pidana.</label>
          <button type="submit" className="rounded-lg bg-slate-900 px-4 py-2.5 font-bold text-white sm:col-span-2">Kirim Konfirmasi Sumbangan</button>
        </form>
      )}
    </section>
  );
}
