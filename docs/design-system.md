# Standar Desain UI/UX

Antarmuka menggunakan prinsip Shadcn UI (Radix UI + Tailwind CSS) dan sistem
grid 8 poin. Gunakan komponen semantik serta utilitas Tailwind yang konsisten.

## Tipografi

- Gunakan Inter dengan fallback `ui-sans-serif` / sans-serif.
- Skala teks: `text-sm` (14px), `text-base` (16px), `text-lg` (18px),
  `text-xl` (20px), dan `text-3xl` (30px).

## Spacing

Semua padding dan margin harus kelipatan 4px atau 8px agar selaras dengan
sistem grid 8 poin. Contoh: `p-2`, `p-4`, `p-6`, `p-8`, `gap-4`, dan `gap-6`.

## Radius

- Gunakan `rounded-md` (6px) untuk tombol dan input.
- Gunakan `rounded-2xl` (16px) untuk kartu besar.

## Warna

Gunakan design token yang tersedia, bukan warna ad hoc:

- Primary: `primary` (hitam/abu-abu gelap)
- Teks utama: `text-main`
- Permukaan: `surface`
- Latar: `background`

Warna antarmuka dibatasi pada putih, hitam, dan abu-abu netral (Slate/Zinc).
Gunakan token `primary` untuk aksen gelap dan hindari warna identitas sampai
ditetapkan melalui voting anggota.

## Aksesibilitas

Semua tombol interaktif wajib menyediakan state `hover:`,
`focus-visible:ring-2`, dan `disabled:opacity-50`. Pastikan kontrol tetap
dapat dioperasikan dengan keyboard serta memiliki kontras yang memadai.
