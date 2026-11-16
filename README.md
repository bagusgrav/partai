# PARTAI (akuhadir.my.id)

> **Infrastruktur digital untuk partai politik yang mendidik, melayani, dan dapat diawasi oleh warga.**

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Next.js](https://img.shields.io/badge/Next.js-16-000000?logo=next.js&logoColor=white)](https://nextjs.org/)
[![SQLite](https://img.shields.io/badge/Database-SQLite%20WAL-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Tentang Proyek

PARTAI adalah proyek civic-tech open-source yang membangun **infrastruktur digital untuk mewujudkan delapan fungsi ideal partai politik**: pendidikan dan sosialisasi politik, komunikasi politik, artikulasi dan agregasi kepentingan, partisipasi politik, rekrutmen politik, pengelolaan konflik, serta perumusan dan pengawasan kebijakan.

Platform ini dirancang untuk menjawab persoalan yang kerap dibahas dalam riset tata kelola partai—seperti korupsi, politik transaksional, dominasi oligarki, kolusi, serta minimnya transparansi—dengan menyediakan proses yang dapat ditelusuri dan data yang terbuka. Tujuannya adalah memperkuat akuntabilitas, bukan menggantikan peran organisasi dan deliberasi warga.

## Fitur Utama

| Fase | Fitur | Ringkasan |
| --- | --- | --- |
| 🟢 | **Pendidikan Kewargaan (SM-2)** | Materi kewargaan, latihan soal, dan pengulangan terjadwal untuk mendukung pembelajaran berkelanjutan. |
| 🟠 | **Aksi Lapangan (Anti-Kolusi QR)** | Kehadiran aksi berbasis QR dinamis, geofence, dan atestasi silang antarwilayah. |
| 🔵 | **Open Data & Jemput Hak (Bansos Tracker)** | Data sosial dan ekonomi publik serta alat bantu pengecekan hak dan bantuan sosial. |
| 🟣 | **Tata Kelola (Kontrak Faksi & Recall)** | Kontrak faksi yang dapat dipantau dan mekanisme partisipasi serta recall anggota. |
| 🏆 | **Sistem Reputasi** | Poin dan level warga berdasarkan kuis benar, esai dengan nilai peer-review minimal 70/100, kehadiran tervalidasi, dan atestasi klaim. |

## Arsitektur Teknis

| Lapisan | Teknologi |
| --- | --- |
| Frontend | Next.js App Router, React, dan Tailwind CSS — siap untuk deployment di Vercel |
| Backend | Laravel API — dapat dijalankan pada VPS |
| Database | SQLite dengan WAL dan konfigurasi ringan; tidak memerlukan server database terpisah |
| Autentikasi | SSO terpusat melalui ekosistem Aku Hadir dan API token Sanctum |

> Versi badge mengikuti dependency proyek saat ini. Backend pada `api/composer.json` menggunakan Laravel 13 dan frontend pada `web/package.json` menggunakan Next.js 16.

## Pengembangan Lokal

### Prasyarat

- PHP 8.3 atau lebih baru
- Composer
- Node.js 20 atau lebih baru
- npm

### 1. Jalankan API Laravel

```powershell
cd api
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Force database\database.sqlite
php artisan migrate --seed
php artisan serve
```

API lokal tersedia di `http://127.0.0.1:8000/api`.

### 2. Jalankan frontend Next.js

Buka terminal baru dari root repository:

```powershell
cd web
npm install
```

Buat `web/.env.local` bila API tidak menggunakan URL default:

```dotenv
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api
```

Lalu jalankan:

```powershell
npm run dev
```

Frontend tersedia di `http://localhost:3000`. Untuk memeriksa test API dan build frontend:

```powershell
cd api
php artisan test

cd ..\web
npm run build
```

## Kontribusi

Kontribusi melalui issue dan pull request sangat kami sambut. Sebelum mengirim perubahan:

1. Jelaskan konteks dan dampak perubahan pada issue atau deskripsi pull request.
2. Jalankan test API dan build frontend yang relevan.
3. Pertahankan invariant proyek:
   - Jangan mengubah skema inti `users`, `events`, atau `items` secara destruktif; gunakan migrasi baru yang kompatibel.
   - Gunakan UUID untuk tabel baru.
   - Gunakan `fetchWithRetry` untuk setiap proses fetching di frontend.
   - Pertahankan idempotensi endpoint yang mengubah data melalui `request_id`.

## Lisensi

PARTAI dirilis di bawah [MIT License](LICENSE).
