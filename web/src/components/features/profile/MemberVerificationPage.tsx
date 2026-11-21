"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import { usePartySettings } from "@/components/PartySettingsProvider";
import { verifyMember, type VerifiedMember } from "@/lib/api";

export default function MemberVerificationPage({
  memberNumber,
}: {
  memberNumber: string;
}) {
  const { settings } = usePartySettings();
  const [member, setMember] = useState<VerifiedMember | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let active = true;

    verifyMember(memberNumber)
      .then((result) => {
        if (active) {
          setMember(result);
        }
      })
      .catch((loadError: unknown) => {
        if (active) {
          setError(
            loadError instanceof Error
              ? loadError.message
              : "Verifikasi anggota gagal dimuat.",
          );
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
  }, [memberNumber]);

  return (
    <main className="flex flex-1 items-center justify-center px-5 py-12 sm:px-8">
      <section className="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
        <header
          className="flex items-center gap-4 px-6 py-5 text-white sm:px-8"
          style={{ backgroundColor: settings.primaryColor }}
        >
          {settings.logoPath ? (
            <Image
              src={settings.logoPath}
              alt={`Logo ${settings.partyName}`}
              width={48}
              height={48}
              unoptimized
              crossOrigin="anonymous"
              className="h-12 w-12 rounded-full bg-white object-contain p-1"
            />
          ) : null}
          <div className="min-w-0">
            <p className="truncate font-black uppercase tracking-wider">
              {settings.partyName}
            </p>
            <p className="mt-1 truncate text-sm text-white/80">{settings.tagline}</p>
          </div>
        </header>

        <div className="p-6 sm:p-8">
          {isLoading ? (
            <p className="py-8 text-center font-semibold text-slate-600">
              Memeriksa keanggotaan...
            </p>
          ) : error ? (
            <div
              className="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-center text-amber-900"
              role="alert"
            >
              Verifikasi belum dapat dimuat: {error}
            </div>
          ) : member ? (
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-950">
              <p className="text-sm font-black uppercase tracking-widest text-emerald-700">
                Status Keanggotaan
              </p>
              <h1 className="mt-2 text-2xl font-black sm:text-3xl">
                Anggota Terverifikasi
              </h1>
              <dl className="mt-6 space-y-4">
                <div>
                  <dt className="text-sm font-semibold text-emerald-800">Nama</dt>
                  <dd className="mt-1 text-lg font-bold">{member.name}</dd>
                </div>
                <div>
                  <dt className="text-sm font-semibold text-emerald-800">
                    Nomor Anggota
                  </dt>
                  <dd className="mt-1 font-mono font-bold">{member.member_number}</dd>
                </div>
                <div>
                  <dt className="text-sm font-semibold text-emerald-800">Wilayah</dt>
                  <dd className="mt-1 font-bold">
                    {member.region_name ?? "Belum ditentukan"}
                  </dd>
                </div>
              </dl>
            </div>
          ) : (
            <div
              className="rounded-2xl border border-red-200 bg-red-50 p-6 text-center text-red-950"
              role="status"
            >
              <h1 className="text-2xl font-black">Kartu Tidak Ditemukan</h1>
              <p className="mt-2 text-sm text-red-800">
                Nomor anggota tidak valid atau keanggotaannya belum terverifikasi.
              </p>
            </div>
          )}
        </div>
      </section>
    </main>
  );
}
