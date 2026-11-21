import type { Metadata } from "next";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Draf AD/ART | PARTAI",
  description:
    "Draf terbuka Anggaran Dasar dan Anggaran Rumah Tangga PARTAI untuk dimusyawarahkan dan divoting oleh anggota.",
};

const contents = [
  { id: "pasal-1", title: "Pasal 1 — Nama dan Kedudukan" },
  { id: "pasal-2", title: "Pasal 2 — Asas dan Prinsip" },
  { id: "pasal-3", title: "Pasal 3 — Tujuan" },
  { id: "pasal-4", title: "Pasal 4 — Keanggotaan" },
  { id: "pasal-5", title: "Pasal 5 — Struktur dan Kepengurusan" },
  { id: "pasal-6", title: "Pasal 6 — Hak dan Kewajiban Anggota" },
  { id: "pasal-7", title: "Pasal 7 — Keuangan Partai" },
  { id: "pasal-8", title: "Pasal 8 — Penyelesaian Konflik" },
  { id: "pasal-9", title: "Pasal 9 — Pembubaran Partai" },
];

export default function AdArtPage() {
  return (
    <main className="min-h-screen bg-slate-50 text-slate-900">
      <section className="border-b border-slate-200 bg-white">
        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
          <div
            aria-labelledby="draft-status-title"
            className="rounded-xl border-2 border-amber-500 bg-amber-50 p-5 text-amber-950 sm:p-6"
            role="note"
          >
            <p
              className="text-sm font-black uppercase tracking-[0.12em]"
              id="draft-status-title"
            >
              ⚠️ Status: Draf (Belum Berlaku)
            </p>
            <p className="mt-2 text-sm leading-6 sm:text-base">
              Dokumen ini belum sah dan tidak mengikat anggota. Draf ini akan
              dimusyawarahkan dan divoting sebelum disahkan.
            </p>
            <ol className="mt-4 grid gap-2 text-sm leading-6 sm:grid-cols-3">
              <li>
                <strong>1. Musyawarah Terbuka:</strong> Penjaringan aspirasi dan
                pembahasan oleh anggota.
              </li>
              <li>
                <strong>2. Voting Elektronik:</strong> Pemungutan suara (satu
                anggota, satu suara) melalui platform PARTAI.
              </li>
              <li>
                <strong>3. Penetapan:</strong> Baru dinyatakan sah jika memenuhi
                kuorum dan disetujui mayoritas anggota.
              </li>
            </ol>
          </div>

          <p className="mt-8 text-sm font-bold uppercase tracking-[0.16em] text-slate-500">
            Dokumen untuk dibahas bersama
          </p>
          <h1 className="mt-3 max-w-4xl font-serif text-3xl font-bold leading-tight tracking-tight sm:text-4xl">
            Anggaran Dasar dan Anggaran Rumah Tangga (AD/ART) PARTAI
          </h1>
          <p className="mt-4 max-w-3xl font-serif text-lg leading-8 text-slate-600">
            Draf terbuka yang disusun berdasarkan semangat{" "}
            <em>Constitution as Code</em>. Setiap anggota dapat membaca,
            membahas, dan menyampaikan masukan sebelum proses pemungutan suara.
          </p>
        </div>
      </section>

      <div className="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 sm:py-10 lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-12 lg:px-8">
        <nav
          aria-label="Daftar isi AD/ART"
          className="h-fit rounded-xl border border-slate-200 bg-white p-5 lg:sticky lg:top-24"
        >
          <h2 className="text-sm font-black uppercase tracking-[0.12em] text-slate-700">
            Daftar Isi
          </h2>
          <p className="mt-4 text-xs font-bold uppercase tracking-wider text-slate-500">
            Bagian I — Anggaran Dasar
          </p>
          <ol className="mt-2 space-y-1">
            {contents.slice(0, 5).map((item) => (
              <li key={item.id}>
                <a
                  className="block rounded-md px-2 py-2 text-sm leading-5 text-slate-700 underline decoration-transparent underline-offset-2 transition hover:bg-slate-50 hover:text-slate-950 hover:decoration-current focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800"
                  href={`#${item.id}`}
                >
                  {item.title}
                </a>
              </li>
            ))}
          </ol>
          <p className="mt-4 text-xs font-bold uppercase tracking-wider text-slate-500">
            Bagian II — Anggaran Rumah Tangga
          </p>
          <ol className="mt-2 space-y-1">
            {contents.slice(5).map((item) => (
              <li key={item.id}>
                <a
                  className="block rounded-md px-2 py-2 text-sm leading-5 text-slate-700 underline decoration-transparent underline-offset-2 transition hover:bg-slate-50 hover:text-slate-950 hover:decoration-current focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800"
                  href={`#${item.id}`}
                >
                  {item.title}
                </a>
              </li>
            ))}
          </ol>
        </nav>

        <article className="min-w-0 rounded-xl border border-slate-200 bg-white px-5 py-7 shadow-sm sm:px-8 sm:py-10">
          <section aria-labelledby="bagian-ad">
            <h2
              className="border-b border-slate-200 pb-4 font-serif text-2xl font-bold sm:text-3xl"
              id="bagian-ad"
            >
              Bagian I: Anggaran Dasar (AD)
            </h2>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-1">
              <h3 className="font-serif text-xl font-bold">Pasal 1: Nama dan Kedudukan</h3>
              <p className="mt-3 font-serif leading-8 text-slate-700">
                Partai ini bernama <strong>PARTAI</strong> (singkatan dari
                Partisipasi Rakyat untuk Transparansi dan Akuntabilitas
                Indonesia), berkedudukan di Indonesia dan berbasis pada
                platform digital{" "}
                <a
                  className="font-semibold text-blue-800 underline underline-offset-2"
                  href="https://akuhadir.my.id"
                >
                  akuhadir.my.id
                </a>
                .
              </p>
            </section>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-2">
              <h3 className="font-serif text-xl font-bold">Pasal 2: Asas dan Prinsip</h3>
              <ol className="mt-3 list-decimal space-y-2 pl-6 font-serif leading-8 text-slate-700">
                <li>
                  PARTAI berasaskan Pancasila dan Undang-Undang Dasar Negara
                  Republik Indonesia Tahun 1945.
                </li>
                <li>
                  PARTAI menjunjung tinggi prinsip <strong>Zero Leverage</strong>{" "}
                  (satu anggota, satu suara, tidak ada pembelian pengaruh).
                </li>
                <li>
                  PARTAI menggunakan pendekatan{" "}
                  <em>Constitution as Code</em> dan transparansi data publik
                  (Open Data).
                </li>
              </ol>
            </section>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-3">
              <h3 className="font-serif text-xl font-bold">Pasal 3: Tujuan</h3>
              <ol className="mt-3 list-decimal space-y-2 pl-6 font-serif leading-8 text-slate-700">
                <li>
                  Membangun kapasitas intelektual warga negara melalui
                  pendidikan kewargaan (Algoritma SM-2).
                </li>
                <li>
                  Mewujudkan tata kelola partai yang bebas dari oligarki,
                  dinasti politik, dan korupsi.
                </li>
                <li>
                  Mengawal kebijakan publik dan anggaran negara agar tepat
                  sasaran.
                </li>
              </ol>
            </section>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-4">
              <h3 className="font-serif text-xl font-bold">Pasal 4: Keanggotaan</h3>
              <ol className="mt-3 list-decimal space-y-2 pl-6 font-serif leading-8 text-slate-700">
                <li>
                  Anggota PARTAI adalah Warga Negara Indonesia yang telah
                  terverifikasi NIK-nya.
                </li>
                <li>
                  Keanggotaan bersifat sukarela dan tidak memandang latar
                  belakang suku, agama, ras, dan golongan.
                </li>
              </ol>
            </section>

            <section className="scroll-mt-28 py-6" id="pasal-5">
              <h3 className="font-serif text-xl font-bold">
                Pasal 5: Struktur dan Kepengurusan
              </h3>
              <p className="mt-3 font-serif leading-8 text-slate-700">
                Struktur kepengurusan PARTAI bersifat dinamis dan berbasis
                digital, mengedepankan <strong>Sortisi (Pemilihan Acak)</strong>{" "}
                untuk panel audit dan juri warga guna mencegah konsentrasi
                kekuasaan.
              </p>
            </section>
          </section>

          <section aria-labelledby="bagian-art" className="mt-8">
            <h2
              className="border-b border-slate-200 pb-4 font-serif text-2xl font-bold sm:text-3xl"
              id="bagian-art"
            >
              Bagian II: Anggaran Rumah Tangga (ART)
            </h2>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-6">
              <h3 className="font-serif text-xl font-bold">
                Pasal 6: Hak dan Kewajiban Anggota
              </h3>
              <ol className="mt-3 list-decimal space-y-2 pl-6 font-serif leading-8 text-slate-700">
                <li>
                  <strong>Hak:</strong> Mendapatkan akses pendidikan
                  kewargaan, memberikan suara dalam voting, dan mengajukan{" "}
                  <em>recall</em> (pemberhentian) terhadap pengurus yang
                  menyimpang.
                </li>
                <li>
                  <strong>Kewajiban:</strong> Menjaga etika digital,
                  berpartisipasi aktif dalam kegiatan, dan mematuhi hasil
                  keputusan musyawarah.
                </li>
              </ol>
            </section>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-7">
              <h3 className="font-serif text-xl font-bold">Pasal 7: Keuangan Partai</h3>
              <ol className="mt-3 list-decimal space-y-2 pl-6 font-serif leading-8 text-slate-700">
                <li>
                  Sumber keuangan berasal dari iuran anggota sukarela dan
                  donasi yang sah secara hukum.
                </li>
                <li>
                  Seluruh arus kas dan penggunaan dana wajib dipublikasikan
                  secara <em>real-time</em> di dasbor transparansi{" "}
                  <a
                    className="font-semibold text-blue-800 underline underline-offset-2"
                    href="https://akuhadir.my.id"
                  >
                    akuhadir.my.id
                  </a>
                  .
                </li>
              </ol>
            </section>

            <section className="scroll-mt-28 border-b border-slate-200 py-6" id="pasal-8">
              <h3 className="font-serif text-xl font-bold">
                Pasal 8: Penyelesaian Konflik
              </h3>
              <p className="mt-3 font-serif leading-8 text-slate-700">
                Perselisihan internal diselesaikan melalui mekanisme{" "}
                <em>musyawarah</em> atau, jika gagal, melalui{" "}
                <strong>Panel Etik</strong> yang dipilih secara acak (Sortisi).
              </p>
            </section>

            <section className="scroll-mt-28 py-6" id="pasal-9">
              <h3 className="font-serif text-xl font-bold">
                Pasal 9: Pembubaran Partai
              </h3>
              <p className="mt-3 font-serif leading-8 text-slate-700">
                Pembubaran partai hanya dapat dilakukan melalui voting nasional
                anggota yang memenuhi kuorum sesuai dengan ketentuan peraturan
                perundang-undangan yang berlaku.
              </p>
            </section>
          </section>

          <section className="mt-8 rounded-xl bg-slate-100 p-5 sm:p-6">
            <h2 className="font-serif text-xl font-bold">
              Sampaikan Masukan
            </h2>
            <p className="mt-2 font-serif leading-7 text-slate-700">
              Bantu menyempurnakan draf melalui diskusi terbuka bersama warga
              dan anggota.
            </p>
            <Link
              className="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-slate-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-800 focus-visible:ring-offset-2"
              href="/komunitas"
            >
              Beri Masukan terhadap Draf
            </Link>
          </section>
        </article>
      </div>
    </main>
  );
}
