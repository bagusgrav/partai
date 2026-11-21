"use client";

import { useEffect, useState, type FormEvent } from "react";
import {
  getCurrentUser,
  getIdentityProposals,
  submitIdentityProposal,
  type IdentityProposal,
} from "@/lib/api";
import type { RegisteredUser } from "@/types";

const DISCLAIMER =
  "Usulan yang masuk akan dikurasi oleh Panitia untuk mencegah ujaran kebencian atau plagiarisme sebelum masuk ke tahap Voting.";

export default function IdentityProposalPage() {
  const [user, setUser] = useState<RegisteredUser | null>(null);
  const [authLoading, setAuthLoading] = useState(true);
  const [authError, setAuthError] = useState<string | null>(null);
  const [proposals, setProposals] = useState<IdentityProposal[]>([]);
  const [proposalsLoading, setProposalsLoading] = useState(true);
  const [proposalsError, setProposalsError] = useState<string | null>(null);
  const [name, setName] = useState("");
  const [color, setColor] = useState("#1e3a8a");
  const [logoDescription, setLogoDescription] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [submitError, setSubmitError] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    void getCurrentUser()
      .then((currentUser) => {
        if (active) {
          setUser(currentUser);
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setAuthError(
            error instanceof Error
              ? error.message
              : "Sesi anggota tidak dapat dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setAuthLoading(false);
        }
      });

    void getIdentityProposals()
      .then((result) => {
        if (active) {
          setProposals(result.proposals);
        }
      })
      .catch((error: unknown) => {
        if (active) {
          setProposalsError(
            error instanceof Error
              ? error.message
              : "Daftar usulan identitas gagal dimuat.",
          );
        }
      })
      .finally(() => {
        if (active) {
          setProposalsLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  const canPropose = user?.is_nik_verified === true;

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setSubmitting(true);
    setNotice(null);
    setSubmitError(null);

    try {
      await submitIdentityProposal({
        proposed_name: name.trim(),
        proposed_color: color,
        proposed_logo_desc: logoDescription.trim(),
      });
      setName("");
      setLogoDescription("");
      setNotice("Usulan Anda terkirim dan menunggu kurasi Panitia.");
    } catch (error: unknown) {
      setSubmitError(
        error instanceof Error ? error.message : "Usulan gagal dikirim.",
      );
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <main className="min-h-screen bg-slate-50">
      <section className="bg-slate-950 text-white">
        <div className="mx-auto max-w-5xl px-6 py-14 sm:px-8">
          <p className="text-sm font-black uppercase tracking-[0.2em] text-slate-400">
            Tata Kelola Partai
          </p>
          <h1 className="mt-4 text-4xl font-black tracking-tight sm:text-5xl">
            Pengusulan &amp; Voting Identitas Partai
          </h1>
          <p className="mt-5 max-w-3xl text-lg leading-8 text-slate-300">
            Usulkan nama, warna utama, dan deskripsi logo sebagai bahan
            musyawarah identitas partai.
          </p>
          <p className="mt-5 max-w-3xl text-sm italic leading-7 text-slate-200">
            {DISCLAIMER}
          </p>
        </div>
      </section>

      <div className="mx-auto max-w-5xl space-y-12 px-6 py-10 sm:px-8">
        <section aria-labelledby="identity-proposal-heading">
          <h2
            className="text-2xl font-black text-slate-950"
            id="identity-proposal-heading"
          >
            Kirim Usulan Identitas
          </h2>
          {authLoading ? (
            <p className="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-slate-600" role="status">
              Memeriksa status keanggotaan...
            </p>
          ) : canPropose ? (
            <form
              className="mt-5 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
              onSubmit={handleSubmit}
            >
              <div>
                <label className="block text-sm font-bold text-slate-800" htmlFor="proposed-name">
                  Nama Partai
                </label>
                <input
                  autoComplete="off"
                  className="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-slate-950 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-700/20"
                  id="proposed-name"
                  maxLength={255}
                  onChange={(event) => setName(event.target.value)}
                  required
                  value={name}
                />
              </div>

              <div>
                <label className="block text-sm font-bold text-slate-800" htmlFor="proposed-color">
                  Warna Utama
                </label>
                <div className="mt-2 flex flex-wrap items-center gap-3">
                  <input
                    aria-label="Pilih warna utama"
                    className="size-12 cursor-pointer rounded border border-slate-300 bg-white p-1"
                    id="proposed-color"
                    onChange={(event) => setColor(event.target.value)}
                    type="color"
                    value={color}
                  />
                  <span className="font-mono text-sm uppercase text-slate-700">
                    {color}
                  </span>
                </div>
              </div>

              <div>
                <label className="block text-sm font-bold text-slate-800" htmlFor="proposed-logo-description">
                  Deskripsi Logo
                </label>
                <textarea
                  className="mt-2 min-h-32 w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-950 focus:border-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-700/20"
                  id="proposed-logo-description"
                  maxLength={10000}
                  onChange={(event) => setLogoDescription(event.target.value)}
                  required
                  value={logoDescription}
                />
              </div>

              {notice && (
                <p className="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-900" role="status">
                  {notice}
                </p>
              )}
              {submitError && (
                <p className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-900" role="alert">
                  {submitError}
                </p>
              )}

              <button
                className="min-h-11 rounded-lg bg-slate-950 px-5 font-bold text-white transition hover:bg-slate-800 disabled:cursor-wait disabled:opacity-60"
                disabled={submitting}
                type="submit"
              >
                {submitting ? "Mengirim..." : "Kirim Usulan"}
              </button>
            </form>
          ) : (
            <div className="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-950">
              <p className="font-semibold">
                Hanya anggota terverifikasi yang dapat mengusulkan identitas partai. Selesaikan verifikasi NIK Anda.
              </p>
              {authError && (
                <p className="mt-2 text-sm" role="alert">
                  Status akun tidak dapat dimuat: {authError}
                </p>
              )}
            </div>
          )}
        </section>

        <section aria-labelledby="approved-proposals-heading">
          <h2
            className="text-2xl font-black text-slate-950"
            id="approved-proposals-heading"
          >
            Usulan yang Disetujui
          </h2>
          {proposalsError && (
            <p className="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">
              {proposalsError}
            </p>
          )}
          {proposalsLoading ? (
            <p className="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-slate-600" role="status">
              Memuat usulan...
            </p>
          ) : proposals.length > 0 ? (
            <div className="mt-5 grid gap-4 md:grid-cols-2">
              {proposals.map((proposal) => (
                <article
                  className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                  key={proposal.id}
                >
                  <div className="flex items-start justify-between gap-4">
                    <h3 className="text-lg font-extrabold text-slate-950">
                      {proposal.proposed_name}
                    </h3>
                    <span
                      aria-label={`Warna utama ${proposal.proposed_color}`}
                      className="size-8 shrink-0 rounded-full border border-slate-300"
                      style={{ backgroundColor: proposal.proposed_color }}
                    />
                  </div>
                  <p className="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                    {proposal.proposed_logo_desc}
                  </p>
                  <p className="mt-4 text-xs text-slate-500">
                    Disetujui ·{" "}
                    {new Date(proposal.created_at).toLocaleDateString("id-ID")}
                  </p>
                </article>
              ))}
            </div>
          ) : (
            <p className="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-slate-600">
              Belum ada usulan yang disetujui.
            </p>
          )}
        </section>
      </div>
    </main>
  );
}
