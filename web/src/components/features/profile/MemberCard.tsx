"use client";

import Image from "next/image";
import { useRef, useState } from "react";
import { QRCodeSVG } from "qrcode.react";
import type { RegisteredUser } from "@/types";
import { usePartySettings } from "@/components/PartySettingsProvider";

function getErrorMessage(error: unknown): string {
  return error instanceof Error ? error.message : "Kartu gagal diunduh.";
}

export default function MemberCard({ user }: { user: RegisteredUser }) {
  const { settings, isLoading: isBrandingLoading } = usePartySettings();
  const cardRef = useRef<HTMLDivElement>(null);
  const [isExporting, setIsExporting] = useState(false);
  const [downloadError, setDownloadError] = useState<string | null>(null);
  const verificationUrl = `https://akuhadir.my.id/verifikasi/${encodeURIComponent(user.member_number ?? "")}`;

  async function renderCard(): Promise<string> {
    if (!cardRef.current) {
      throw new Error("Kartu anggota belum siap untuk diunduh.");
    }

    const { toPng } = await import("html-to-image");
    return toPng(cardRef.current, { cacheBust: true, pixelRatio: 2 });
  }

  async function downloadPng(): Promise<void> {
    setIsExporting(true);
    setDownloadError(null);

    try {
      const image = await renderCard();
      const link = document.createElement("a");
      link.download = `kta-${user.member_number}.png`;
      link.href = image;
      link.click();
    } catch (error) {
      setDownloadError(getErrorMessage(error));
    } finally {
      setIsExporting(false);
    }
  }

  async function downloadPdf(): Promise<void> {
    setIsExporting(true);
    setDownloadError(null);

    try {
      const image = await renderCard();
      const { jsPDF } = await import("jspdf");
      const pdf = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });
      const width = 277;
      const height = width / (640 / 360);
      pdf.addImage(image, "PNG", 10, 10, width, height);
      pdf.save(`kta-${user.member_number}.pdf`);
    } catch (error) {
      setDownloadError(getErrorMessage(error));
    } finally {
      setIsExporting(false);
    }
  }

  if (!user.member_number) {
    return (
      <div
        className="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900"
        role="status"
      >
        Kartu anggota digital tersedia setelah NIK Anda diverifikasi oleh admin.
      </div>
    );
  }

  return (
    <section className="mt-8" aria-labelledby="member-card-title">
      <h2 className="text-xl font-extrabold text-text-main" id="member-card-title">
        Kartu Tanda Anggota
      </h2>
      <div
        ref={cardRef}
        className="mt-4 aspect-[16/9] w-full overflow-hidden rounded-3xl shadow-xl"
        style={{
          backgroundColor: settings.primaryColor,
          color: settings.secondaryColor,
        }}
      >
        <div className="flex h-full flex-col justify-between p-5 sm:p-8">
          <header className="flex items-center gap-3">
            {settings.logoPath ? (
              <Image
                src={settings.logoPath}
                alt={`Logo ${settings.partyName}`}
                width={56}
                height={56}
                unoptimized
                crossOrigin="anonymous"
                className="h-12 w-12 rounded-full bg-white object-contain p-1 sm:h-14 sm:w-14"
              />
            ) : (
              <div
                aria-hidden="true"
                className="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-lg font-black sm:h-14 sm:w-14"
              >
                {settings.partyName.slice(0, 1)}
              </div>
            )}
            <div className="min-w-0">
              <p className="truncate text-sm font-black uppercase tracking-[0.12em] sm:text-base">
                {settings.partyName}
              </p>
              <p className="mt-1 truncate text-xs opacity-80 sm:text-sm">
                {settings.tagline}
              </p>
            </div>
            <span className="ml-auto hidden text-xs font-bold uppercase tracking-widest opacity-75 sm:block">
              KTA DIGITAL
            </span>
          </header>

          <div className="flex min-h-0 items-end justify-between gap-3 sm:gap-6">
            <div className="min-w-0 space-y-3">
              <div>
                <p className="text-[10px] font-semibold uppercase tracking-widest opacity-75 sm:text-xs">
                  Nama Anggota
                </p>
                <p className="mt-1 truncate text-lg font-black sm:text-2xl">
                  {user.name}
                </p>
              </div>
              <div>
                <p className="text-[10px] font-semibold uppercase tracking-widest opacity-75 sm:text-xs">
                  Nomor Anggota
                </p>
                <p className="mt-1 font-mono text-sm font-bold sm:text-base">
                  {user.member_number}
                </p>
              </div>
              <p className="truncate text-xs opacity-85 sm:text-sm">
                Wilayah: {user.cluster_name ?? user.cluster_id ?? "Belum ditentukan"}
              </p>
            </div>

            <div className="shrink-0 rounded-xl bg-white p-2 sm:p-3">
              <QRCodeSVG
                aria-label="QR untuk verifikasi anggota"
                value={verificationUrl}
                size={108}
                level="M"
              />
            </div>
          </div>
        </div>
      </div>

      <div className="mt-4 flex flex-wrap gap-3">
        <button
          className="rounded-full bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-60"
          disabled={isExporting || isBrandingLoading}
          onClick={downloadPng}
          type="button"
        >
          {isBrandingLoading ? "Memuat branding..." : "Unduh PNG"}
        </button>
        <button
          className="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-bold text-text-main transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
          disabled={isExporting || isBrandingLoading}
          onClick={downloadPdf}
          type="button"
        >
          {isBrandingLoading ? "Memuat branding..." : "Unduh PDF"}
        </button>
      </div>
      {downloadError ? (
        <p className="mt-3 text-sm font-semibold text-red-700" role="alert">
          {downloadError}
        </p>
      ) : null}
    </section>
  );
}
