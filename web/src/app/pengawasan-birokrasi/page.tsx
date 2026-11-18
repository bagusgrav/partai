"use client";

import { FormEvent, useEffect, useState } from "react";
import {
  createBureaucracyReport,
  getBureaucracyReports,
  getBureaucracyScorecards,
  trackBureaucracyReport,
  type CreateReportResult,
  type PublicAgencyScorecard,
  type BureaucracyReportPublic,
  type ScorecardResponse,
  type TrackReportResult,
} from "@/lib/api/bureaucracy";

const tierLabel = {
  sangat_baik: "Sangat Baik",
  baik: "Baik",
  perlu_perbaikan: "Perlu Perbaikan",
  kritis: "Kritis",
};

export default function PengawasanBirokrasiPage() {
  const [tab, setTab] = useState<"rapor" | "lapor" | "lacak">("rapor");
  const [scoreData, setScoreData] = useState<ScorecardResponse | null>(null);
  const [scoreError, setScoreError] = useState("");
  const [category, setCategory] = useState("");
  const [tier, setTier] = useState("");
  const [search, setSearch] = useState("");
  const [reports, setReports] = useState<BureaucracyReportPublic[]>([]);
  const [reportError, setReportError] = useState("");
  const [created, setCreated] = useState<CreateReportResult | null>(null);
  const [tracked, setTracked] = useState<TrackReportResult | null>(null);
  const [submitError, setSubmitError] = useState("");
  const [trackingError, setTrackingError] = useState("");
  const [submitting, setSubmitting] = useState(false);

  async function loadScores() {
    setScoreError("");
    try {
      setScoreData(await getBureaucracyScorecards({
        category: category || undefined,
        tier: tier || undefined,
        search: search || undefined,
      }));
    } catch (error) {
      setScoreError(error instanceof Error ? error.message : "Rapor tidak dapat dimuat.");
    }
  }

  useEffect(() => {
    void getBureaucracyScorecards({})
      .then(setScoreData)
      .catch((error: unknown) => {
        setScoreError(error instanceof Error ? error.message : "Rapor tidak dapat dimuat.");
      });
    void getBureaucracyReports()
      .then((result) => setReports(result.reports.data))
      .catch((error: unknown) => {
        setReportError(error instanceof Error ? error.message : "Daftar laporan tidak dapat dimuat.");
      });
  }, []);

  async function submitReport(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setSubmitting(true);
    setSubmitError("");
    setCreated(null);
    const form = new FormData(event.currentTarget);
    try {
      const result = await createBureaucracyReport({
        agency_name: String(form.get("agency_name")),
        province_name: String(form.get("province_name") || "") || null,
        category: String(form.get("category")) as "maladministrasi" | "penyalahgunaan_fasilitas" | "pungli_layanan" | "penundaan_berlarut" | "diskriminasi_layanan",
        title: String(form.get("title")),
        chronology: String(form.get("chronology")),
        incident_date: String(form.get("incident_date")),
        asset_identifier: String(form.get("asset_identifier") || "") || null,
        evidence_url: String(form.get("evidence_url") || "") || null,
      });
      setCreated(result);
      event.currentTarget.reset();
    } catch (error) {
      setSubmitError(error instanceof Error ? error.message : "Laporan gagal dikirim.");
    } finally {
      setSubmitting(false);
    }
  }

  async function submitTrack(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setTrackingError("");
    setTracked(null);
    const form = new FormData(event.currentTarget);
    try {
      setTracked(await trackBureaucracyReport(
        String(form.get("ticket_code")),
        String(form.get("secret") || "") || undefined,
      ));
    } catch (error) {
      setTrackingError(error instanceof Error ? error.message : "Tiket tidak dapat dilacak.");
    }
  }

  return (
    <main className="mx-auto min-h-screen max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <header className="mb-6 border-b border-slate-200 pb-5">
        <p className="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Transparansi Data</p>
        <h1 className="mt-2 text-3xl font-black text-slate-950">Pengawasan Birokrasi</h1>
        <p className="mt-2 max-w-3xl text-sm text-slate-600">
          Rapor kinerja instansi terbuka dan kanal pelaporan maladministrasi serta penyalahgunaan fasilitas negara secara anonim.
        </p>
      </header>

      <div className="mb-6 flex flex-wrap gap-2" role="tablist">
        {([
          ["rapor", "Rapor Instansi"],
          ["lapor", "Lapor Anonim"],
          ["lacak", "Lacak & Audit"],
        ] as const).map(([value, label]) => (
          <button
            key={value}
            type="button"
            role="tab"
            aria-selected={tab === value}
            onClick={() => setTab(value)}
            className={`rounded-md border px-4 py-2 text-sm font-bold ${tab === value ? "border-slate-950 bg-slate-950 text-white" : "border-slate-300 bg-white text-slate-700 hover:bg-slate-50"}`}
          >
            {label}
          </button>
        ))}
      </div>

      {tab === "rapor" && (
        <section>
          {scoreData && (
            <div className="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              {[
                ["Rata-rata Integritas", scoreData.summary.average_integrity_score?.toFixed(2) ?? "-"],
                ["Laporan Terverifikasi", scoreData.summary.verified_reports_count],
                ["Diteruskan Ombudsman", scoreData.summary.forwarded_to_ombudsman_count],
                ["Laporan Selesai", scoreData.summary.resolved_reports_count],
              ].map(([label, value]) => (
                <article key={String(label)} className="rounded-lg border border-slate-200 bg-white p-4">
                  <p className="text-xs font-bold uppercase tracking-wide text-slate-500">{label}</p>
                  <p className="mt-2 text-2xl font-black text-slate-950">{value}</p>
                </article>
              ))}
            </div>
          )}
          <div className="mb-5 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-4">
            <select aria-label="Kategori instansi" className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm" value={category} onChange={(event) => setCategory(event.target.value)}>
              <option value="">Semua kategori</option>
              <option value="kementerian_lembaga">Kementerian/Lembaga</option>
              <option value="pemerintah_provinsi">Provinsi</option>
              <option value="pemerintah_kab_kota">Kabupaten/Kota</option>
              <option value="pelayanan_publik">Pelayanan Publik</option>
            </select>
            <select aria-label="Tier kinerja" className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm" value={tier} onChange={(event) => setTier(event.target.value)}>
              <option value="">Semua tier</option>
              {Object.entries(tierLabel).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
            </select>
            <input aria-label="Cari instansi" className="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm md:col-span-2" placeholder="Cari nama atau kode instansi" value={search} onChange={(event) => setSearch(event.target.value)} />
          </div>
          <button type="button" onClick={() => void loadScores()} className="mb-5 rounded-md bg-slate-950 px-4 py-2 text-sm font-bold text-white">Terapkan Filter</button>
          {scoreError && <p className="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{scoreError}</p>}
          <div className="grid gap-3 md:grid-cols-2">
            {scoreData?.scorecards.data.map((item: PublicAgencyScorecard) => (
              <article key={item.id} className="rounded-lg border border-slate-200 bg-white p-5">
                <div className="flex items-start justify-between gap-3">
                  <div><p className="text-xs font-bold text-slate-500">{item.agency_code}</p><h2 className="mt-1 font-bold text-slate-950">{item.agency_name}</h2></div>
                  <span className="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{tierLabel[item.performance_tier]}</span>
                </div>
                <p className="mt-3 text-3xl font-black text-slate-950">{item.bureaucracy_integrity_score.toFixed(2)}</p>
                <dl className="mt-3 grid grid-cols-2 gap-2 text-sm text-slate-600">
                  <div><dt>Indeks RB</dt><dd className="font-bold text-slate-950">{item.rb_index_score}</dd></div>
                  <div><dt>SAKIP</dt><dd className="font-bold text-slate-950">{item.sakip_grade}</dd></div>
                  <div><dt>Sistem Merit BKN</dt><dd className="font-bold text-slate-950">{item.merit_system_score}</dd></div>
                  <div><dt>Kepatuhan Ombudsman</dt><dd className="font-bold text-slate-950">{item.ombudsman_compliance_score}</dd></div>
                </dl>
                <p className="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500">{item.source_citation}</p>
              </article>
            ))}
          </div>
        </section>
      )}

      {tab === "lapor" && (
        <section className="grid gap-6 lg:grid-cols-[2fr_1fr]">
          <form onSubmit={submitReport} className="grid gap-4 rounded-lg border border-slate-200 bg-white p-5">
            <h2 className="text-lg font-bold text-slate-950">Kirim Laporan Anonim</h2>
            <label className="text-sm font-semibold text-slate-700">Instansi terlapor
              <input name="agency_name" required className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
            </label>
            <div className="grid gap-4 sm:grid-cols-2">
              <label className="text-sm font-semibold text-slate-700">Kategori
                <select name="category" required className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                  <option value="maladministrasi">Maladministrasi</option>
                  <option value="penyalahgunaan_fasilitas">Penyalahgunaan fasilitas negara</option>
                  <option value="pungli_layanan">Pungli layanan</option>
                  <option value="penundaan_berlarut">Penundaan berlarut</option>
                  <option value="diskriminasi_layanan">Diskriminasi layanan</option>
                </select>
              </label>
              <label className="text-sm font-semibold text-slate-700">Tanggal kejadian
                <input name="incident_date" type="date" required className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
              </label>
            </div>
            <label className="text-sm font-semibold text-slate-700">Provinsi
              <input name="province_name" className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
            </label>
            <label className="text-sm font-semibold text-slate-700">Judul
              <input name="title" required maxLength={180} className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
            </label>
            <label className="text-sm font-semibold text-slate-700">Kronologi faktual (minimal 30 karakter)
              <textarea name="chronology" required minLength={30} rows={5} className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
            </label>
            <div className="grid gap-4 sm:grid-cols-2">
              <label className="text-sm font-semibold text-slate-700">Identitas aset kendaraan/negara
                <input name="asset_identifier" className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" placeholder="Mis. jenis aset atau nomor aset" />
              </label>
              <label className="text-sm font-semibold text-slate-700">URL bukti
                <input name="evidence_url" type="url" className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
              </label>
            </div>
            {submitError && <p className="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{submitError}</p>}
            <button disabled={submitting} type="submit" className="rounded-md bg-slate-950 px-4 py-3 text-sm font-bold text-white disabled:opacity-50">{submitting ? "Mengirim..." : "Kirim Laporan"}</button>
          </form>
          <aside className="rounded-lg border border-amber-300 bg-amber-50 p-5">
            <h2 className="font-bold text-slate-950">Lindungi identitas Anda</h2>
            <p className="mt-2 text-sm text-slate-700">Jangan cantumkan NIK, alamat, nomor identitas pribadi, atau identitas lain yang dapat mengungkap pelapor.</p>
            {created && (
              <div className="mt-5 rounded-md border border-slate-300 bg-white p-4">
                <p className="text-xs font-bold uppercase text-slate-500">Kode Tiket</p>
                <p className="mt-1 font-mono text-lg font-black">{created.ticket_code}</p>
                {created.reporter_secret && <>
                  <p className="mt-4 text-xs font-bold uppercase text-red-700">Kunci Rahasia Pelapor</p>
                  <p className="mt-1 break-all font-mono text-sm font-bold">{created.reporter_secret}</p>
                  <p className="mt-3 text-xs text-red-700">Simpan sekarang. Kunci ini hanya ditampilkan sekali.</p>
                </>}
              </div>
            )}
          </aside>
        </section>
      )}

      {tab === "lacak" && (
        <section className="grid gap-6 lg:grid-cols-[1fr_2fr]">
          <form onSubmit={submitTrack} className="h-fit rounded-lg border border-slate-200 bg-white p-5">
            <h2 className="font-bold text-slate-950">Lacak Tiket Anonim</h2>
            <label className="mt-4 block text-sm font-semibold text-slate-700">Kode tiket
              <input name="ticket_code" required className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
            </label>
            <label className="mt-3 block text-sm font-semibold text-slate-700">Kunci rahasia (opsional)
              <input name="secret" type="password" className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" />
            </label>
            <button type="submit" className="mt-4 rounded-md bg-slate-950 px-4 py-2 text-sm font-bold text-white">Periksa Status</button>
            {trackingError && <p className="mt-3 text-sm text-red-700">{trackingError}</p>}
            {tracked && (
              <dl className="mt-5 space-y-3 text-sm">
                <div><dt className="text-slate-500">Status</dt><dd className="font-bold">{tracked.verification_status}</dd></div>
                <div><dt className="text-slate-500">Instansi</dt><dd className="font-bold">{tracked.agency_name}</dd></div>
                {tracked.chronology && <div><dt className="text-slate-500">Kronologi</dt><dd>{tracked.chronology}</dd></div>}
                {tracked.public_resolution_note && <div><dt className="text-slate-500">Catatan tindak lanjut</dt><dd>{tracked.public_resolution_note}</dd></div>}
              </dl>
            )}
          </form>
          <div>
            <h2 className="mb-3 text-lg font-bold text-slate-950">Log Laporan Terverifikasi</h2>
            {reportError && <p className="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{reportError}</p>}
            <div className="grid gap-3">
              {reports.map((report) => (
                <article key={report.id} className="rounded-lg border border-slate-200 bg-white p-4">
                  <div className="flex justify-between gap-3"><p className="font-mono text-xs font-bold text-slate-500">{report.ticket_code}</p><span className="text-xs font-bold text-slate-600">{report.verification_status}</span></div>
                  <h3 className="mt-2 font-bold text-slate-950">{report.title}</h3>
                  <p className="mt-1 text-sm text-slate-600">{report.agency_name}{report.province_name ? ` · ${report.province_name}` : ""}</p>
                  {report.public_resolution_note && <p className="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-700">{report.public_resolution_note}</p>}
                </article>
              ))}
            </div>
          </div>
        </section>
      )}
    </main>
  );
}
