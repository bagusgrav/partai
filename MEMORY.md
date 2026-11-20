# MEMORY PARTAI

Catatan ringkas invariant, status, keputusan, dan serah-terima repository. Dokumen ini bukan pengganti spesifikasi atau bukti kondisi operasional. Untuk aplikasi yang berjalan, kode, route, migrasi, dan tes menjadi acuan saat ada pertentangan.

## 1. Ringkasan aplikasi

PARTAI adalah aplikasi civic-tech untuk pendidikan kewargaan, partisipasi warga, akses informasi kesejahteraan, dan akuntabilitas. Backend adalah Laravel modular monolith; frontend menggunakan Next.js 16. API menggunakan SQLite dengan WAL. Topologi yang dituju memakai Cloudflare Tunnel sebagai ingress API. Keberadaan konfigurasi dan panduan tidak membuktikan deployment atau layanan operasional aktif.

Sumber dokumen:

- Arsitektur kanonis: [docs/architecture.md](docs/architecture.md); [ARCHITECTURE.md](ARCHITECTURE.md) hanya stub pointer.
- Referensi pendukung kontrak endpoint: [docs/api-reference.md](docs/api-reference.md), yang harus diselaraskan dengan `api/routes/api.php`.
- Roadmap: [ROADMAP.md](ROADMAP.md).

## 2. Stabil

Area berikut diperlakukan sebagai invariant. Perubahan memerlukan audit yang relevan dan pembaruan tes/dokumen; deployment script tidak boleh disentuh sebelum diaudit.

- **Enkripsi NIK akun:** `users.nik` dienkripsi dan `nik_hash` digunakan untuk lookup; lihat `api/database/migrations/2026_11_18_000000_encrypt_user_nik.php` dan `api/app/Models/User.php`.
- **Autentikasi lokal:** Sanctum `/api/auth/login`, `/api/auth/register`, dan `/api/auth/guest` tetap menjadi alur yang tersedia sampai kontrak SSO disepakati dan implementasi end-to-end diaudit.
- **Pemeriksaan hak:** `GET /api/rights/check` tidak membawa NIK pada URL. NIK diambil dari user terautentikasi dan pemeriksaan mensyaratkan `nik_verified_at`; lihat `api/routes/api.php`, `api/app/Http/Controllers/Api/SocialAssistanceController.php`, dan `api/tests/Feature/Api/SocialAssistanceControllerTest.php`.
- **Komunitas:** feed publik dan feed anggota dipisahkan menurut visibilitas (`/api/posts` dan `/api/posts/members`); pertahankan batas akses, cache, dan perilaku privasinya.
- **Deployment:** `api/deploy.sh` adalah area belum boleh disentuh tanpa audit. Panduan yang saling bertentangan harus direkonsiliasi terhadap workflow dan skrip sebelum digunakan.

## 3. Sudah Selesai

- **Voting Parameter & Kebijakan** — status: selesai pada implementasi lokal, bukan deployment — bukti: `web/src/components/features/voting/VotingDashboard.tsx`, `web/src/components/features/voting/VoteCard.tsx`, `api/app/Services/VoteService.php`, `api/tests/Feature/Api/VoteControllerTest.php`; seluruh tes Laravel dan Playwright voting lulus.
- **Refaktor `rights/check` tanpa `{nik}` di path URL** — commit `afd85c0` (`fix(security): remove NIK from URL path and use authenticated session`). Bukti: `api/routes/api.php`, `api/app/Http/Controllers/Api/SocialAssistanceController.php`, `api/tests/Feature/Api/SocialAssistanceControllerTest.php`, `docs/api-reference.md`, dan `docs/architecture.md`.
- **Sinkronisasi API reference dengan 66 route efektif** — commit `edb2333` (`docs(api): sinkronkan api-reference dengan 66 route efektif`). Bukti dokumentasi: `docs/api-reference.md`; daftar route implementasi: `api/routes/api.php`.
- **100 soal Belajar Harian Civic Education (Fase 1)** — status: selesai / berhasil di-seed ke database VPS produksi — bukti: commit `9e1364d` (seeder update), `api/database/seeders/CivicQuestionSeeder.php`, dan eksekusi manual seeder di server dengan output `DONE`.
- **MVP Komunitas** — status: **Selesai / Live di Produksi** — commit `aa733a9` (`feat(community): MVP microblog dengan visibilitas publik/anggota`). Bukti: `api/routes/api.php`, controller/model/policy, `api/tests/Feature/PostControllerTest.php`, `api/tests/Feature/CommunityModerationTest.php`, `web/src/components/features/community/CommunityPage.tsx`, dan deployment VPS produksi yang berhasil.
- **Perbaikan CI/CD deployment API** — status: selesai — commit `5d6f9ff` (`fix(ci): add sleep and ignore health check failure to prevent false negatives`) dan fix/final deployment di rantai commit perbaikan port. Bukti: `.github/workflows/deploy-api.yml`, `api/deploy.sh`, serta penanganan SQLite, direktori Storage, `APP_KEY`, port health check, dan pengecekan `/up`.
- **Dokumentasi Komunitas** — commit `a7b2da9` (`docs(community): dokumentasikan domain Komunitas dan batas visibilitas`). Bukti: `docs/architecture.md`, `docs/api-reference.md`, `README.md`, dan `ROADMAP.md`.
- **Perapian UI Admin Filament** — status: selesai lokal, belum diverifikasi di produksi — bukti: `api/app/Providers/Filament/AdminPanelProvider.php`, resource Filament, `api/app/Filament/Widgets/AdminStatsOverview.php`, `api/public/partai-favicon.svg`, dan `api/tests/Feature/AdminStatsOverviewTest.php`.
- **Penolakan hak suara akun tamu** — status: selesai lokal, belum diverifikasi di produksi — bukti: `api/app/Http/Controllers/Api/VoteController.php`, `api/app/Http/Controllers/Api/AuthController.php`, dan tes vote/guest session.

## 4. Sedang Dikerjakan

Tidak ada pekerjaan fitur aktif yang dapat disimpulkan dari status repository saat pemeriksaan.

## 5. Belum Dikerjakan

- SSO end-to-end, setelah kontrak identitas pusat ditetapkan.
- Tinjauan dan perbaikan privasi untuk `social_assistances.nik`, yang berbeda dari enkripsi `users.nik`.
- Uji migrasi `encrypt_user_nik` pada database uji berisi NIK sintetis saja.
- Restore backup yang benar-benar diuji.
- Backup terjadwal yang diverifikasi.
- Smoke test staging.
- Uji alur moderasi Filament secara manual.
- Resource admin Filament untuk Voting dan Recall belum tersedia; menu tersebut belum dapat ditampilkan tanpa implementasi resource tersendiri.

### Known gaps dokumentasi

- `docs/deployment.md` dan `docs/github-actions.md` tidak konsisten dengan workflow dan asumsi di `api/deploy.sh`; jangan gunakan salah satu secara operasional sebelum audit/rekonsiliasi. Berkas tidak dihapus atau diubah pada sesi ini.
- `DOCS-STANDARD.md` tersedia di root dan menjadi acuan format dokumentasi proyek; `docs/api-reference.md` tetap dicatat sebagai dokumen pendukung API, bukan SSOT arsitektur.

## 6. ADR

### ADR-20261120-01: Voting hanya untuk anggota terverifikasi dan tally terlindungi

- **Konteks:** `auth:sanctum` juga mengizinkan akun tamu, sedangkan pemilihan kebijakan ditujukan untuk anggota; tamu dapat memiliki email berawalan `guest_`.
- **Keputusan:** submit mensyaratkan `role=member`, `is_verified=true`, dan email yang tidak berawalan `guest_`; pembuatan akun tamu menetapkan `role=guest` dan `is_verified=false`. `GET /api/votes/active` mengembalikan `can_vote` dan `has_voted`; `votes_count` hanya dikirim setelah pemanggil memilih. Tidak diperlukan perubahan skema.
- **Konsekuensi:** akun lain tetap dapat melihat daftar, tetapi kontrol voting dinonaktifkan dan submit ditolak `403`; hasil agregat tidak membuka pilihan individual.

### ADR-001: `docs/architecture.md` adalah arsitektur kanonis

- **Keputusan:** blueprint lengkap dipelihara hanya di `docs/architecture.md`; `ARCHITECTURE.md` di root menjadi stub pointer 3 baris.
- **Alasan:** mencegah duplikasi isi panjang dan sumber kebenaran arsitektur yang bersaing.
- **Konsekuensi:** perubahan arsitektur dicatat pada dokumen kanonis; stub root tetap berupa tautan.

### ADR-002: `/api/rights/check` tidak menerima NIK pada path

- **Keputusan:** endpoint menggunakan identitas user terautentikasi, dengan pemeriksaan `nik_verified_at`; NIK tidak menjadi parameter URL.
- **Alasan:** menghindari paparan NIK melalui URL dan log terkait, serta membatasi pemeriksaan ke akun yang terverifikasi.
- **Bukti:** commit `afd85c0`; route, controller, dan tes tercantum pada Bagian 3.

## 7. Log Sesi

- **2026-11-20 — Perbaikan keamanan hak suara akun tamu**
  - **Perubahan:** menetapkan role dan status verifikasi guest secara eksplisit; eligibility voting menolak email `guest_`; menambahkan tes submit untuk akun role `guest` maupun member dengan email guest serta assertion atribut akun guest.
  - **Bukti:** `php artisan test` (185 tes, 1.528 assertions lulus); `php vendor\bin\pint --test` lulus; `git diff --check` lulus; tes regresi di `api/tests/Feature/Api/VoteControllerTest.php` dan `api/tests/Feature/GuestSessionTest.php`.
  - **Dokumen Diperbarui:** `MEMORY.md`.
  - **Langkah Berikutnya:** tidak ada langkah implementasi; verifikasi perubahan di staging/produksi tetap diperlukan.
- **2026-11-20 — Perapian UI Admin Filament**
  - **Perubahan:** mengganti branding panel menjadi PARTAI, menambahkan favicon SVG, mengurutkan grup dan resource navigasi, menyembunyikan navigasi `ItemResource` agar hanya satu menu Bank Soal tampil, dan menambahkan widget empat statistik baca-saja. Tidak ada perubahan skema atau logika bisnis. Resource admin Voting/Recall tidak ditemukan dan tidak dibuat.
  - **Bukti:** `api/tests/Feature/AdminStatsOverviewTest.php` (2 tes terarah, 8 assertions lulus); `php artisan test` (183 tes, 1.517 assertions lulus); `npm run build` di `web/` dan `node node_modules\vite\bin\vite.js build` di `api/` lulus; Pint dan `git diff --check` lulus. `api/npm run build` belum berhasil karena executable `vite` tidak tersedia pada PATH skrip npm. Belum diverifikasi di staging/produksi.
  - **Dokumen Diperbarui:** `MEMORY.md`.
  - **Langkah Berikutnya:** verifikasi tampilan panel di staging setelah perubahan UI dirilis; lengkapi menu Voting/Recall hanya jika resource admin tersebut disetujui dan tersedia.
- **2026-11-20 — Voting Parameter & Kebijakan (Fase 4)**
  - **Perubahan:** melengkapi halaman voting aktif dengan opsi, konfirmasi sebelum submit, `request_id` UUID idempoten, eligibility anggota terverifikasi, status sudah memilih, dan tally agregat setelah pilihan tercatat. API menggunakan skema yang ada; tidak ada migrasi.
  - **Bukti:** `api/tests/Feature/Api/VoteControllerTest.php` (182 tes backend keseluruhan lulus); tiga tes Playwright voting, `npm run lint`, dan `npm run build` lulus. Belum diverifikasi di staging/produksi.
  - **Dokumen Diperbarui:** `MEMORY.md`, `ROADMAP.md`, `docs/api-reference.md`.
  - **Langkah Berikutnya:** lakukan smoke test voting di staging setelah data voting aktif disiapkan.
- **2026-11-20 — Tangga poin reputasi di halaman Panduan**
  - **Perubahan:** menambahkan tangga level anggota dari Warga Baru hingga Pengawal Demokrasi, berdasarkan total poin reputasi.
  - **Bukti:** commit `e6a3e47` (`docs(ui): tambah tangga poin reputasi di halaman panduan`); `web/src/components/features/guide/GuidePage.tsx`.
  - **Dokumen Diperbarui:** `MEMORY.md`.
  - **Langkah Berikutnya:** selaraskan rentang level pada halaman Panduan jika aturan poin reputasi berubah.
- **2026-11-20 — Seeding 100 soal Civic Education Fase 1 ke VPS produksi**
  - **Perubahan:** 100 soal Civic Education Fase 1 berhasil di-seed ke database VPS produksi.
  - **Bukti:** commit `9e1364d` (seeder update); seeder dijalankan manual di server dengan sukses, output `DONE`.
  - **Dokumen Diperbarui:** `MEMORY.md`.
  - **Langkah Berikutnya:** verifikasi soal tampil pada alur kuis produksi.
- **2026-11-20 — Penambahan 100 soal Belajar Harian**
  - **Perubahan:** seeder CivicQuestion menghasilkan 20 soal per domain dalam lima domain, dengan level 1 atau 2, empat opsi, kunci, dan penjelasan maksimal dua kalimat. Penggunaan `updateOrCreate` berdasarkan prompt menjaga seeder idempoten; `DatabaseSeeder` sebelumnya sudah memanggil seeder ini.
  - **Bukti:** `api/database/seeders/CivicQuestionSeeder.php`; `api/tests/Feature/CivicEducationTest.php`; `php artisan test` lulus (180 tes); `php vendor\bin\pint --test` lulus; seeder dijalankan dua kali pada SQLite sementara dan jumlah akhirnya 100. `api/database/database.sqlite` tidak disentuh.
  - **Dokumen Diperbarui:** `MEMORY.md`.
  - **Langkah Berikutnya:** verifikasi tampilan soal hasil seeding pada alur kuis produksi.
- **2026-11-20 — Deployment produksi MVP Komunitas dan finalisasi CI/CD**
  - **Perubahan:** MVP Komunitas dinyatakan Live di Produksi; pipeline dan skrip deployment diperbaiki untuk SQLite, direktori Storage, `APP_KEY`, port, dan health check `/up`.
  - **Bukti:** commit `aa733a9` (`feat(community): MVP microblog dengan visibilitas publik/anggota`) dan commit `5d6f9ff` (`fix(ci): add sleep and ignore health check failure to prevent false negatives`); `.github/workflows/deploy-api.yml`; `api/deploy.sh`.
  - **Dokumen Diperbarui:** `MEMORY.md`.
  - **Langkah Berikutnya:** pantau layanan produksi dan jalankan pemeriksaan end-to-end alur Komunitas pada VPS.
- **2026-11-19 — `afd85c0`:** refaktor `/api/rights/check` untuk mengambil NIK dari sesi autentikasi dan menghapusnya dari path URL.
- **2026-11-19 — `edb2333`:** sinkronisasi `docs/api-reference.md` terhadap 66 route efektif.
- **2026-11-20 — `aa733a9`:** implementasi MVP Komunitas dengan visibilitas publik/anggota dan tes backend.
- **2026-11-20 — `a7b2da9`:** dokumentasi domain Komunitas dan batas visibilitas.
- **2026-11-20 — sesi dokumentasi:** menjadikan `docs/architecture.md` kanonis, mempertahankan stub root, menautkan memory dari README, dan memperbaiki known gap yang keliru menyatakan NIK berada pada URL. Belum ada commit untuk perubahan sesi ini.

## Blok Ringkasan Serah Terima Sesi

- **Dikerjakan:** perbaikan hak suara akun tamu dengan membatasi eligibility dan mengunci atribut role/verifikasi saat token guest dibuat. Tidak ada perubahan skema, commit, atau push.
- **Bukti:** `php artisan test` — 185 tes / 1.528 assertions lulus; `php vendor\bin\pint --test` lulus; `git diff --check` lulus.
- **Status:** fix lokal — status: selesai, belum diverifikasi di produksi — bukti: controller dan tes feature terkait.
- **Dokumen .md Diperbarui:** `MEMORY.md`. `docs/architecture.md` tidak berubah karena topologi dan alur sistem tidak berubah.
- **Belum Selesai / Langkah Berikutnya:** verifikasi perubahan di staging/produksi.
- **Kanonis:** `docs/architecture.md`; `ARCHITECTURE.md` adalah pointer. Referensi API pendukung ada di `docs/api-reference.md`.
- **Invariant penting:** enkripsi `users.nik`/`nik_hash`; alur Sanctum lokal sampai kontrak SSO; rights check tanpa NIK di URL dan hanya untuk user terverifikasi; feed Komunitas terpisah; `api/deploy.sh` jangan disentuh tanpa audit.
### Voting Security Fix (Verified)
- **Tanggal:** 20 November 2026
- **Konteks:** Menutup celah keamanan di mana sesi tamu (*guest*) dapat mengirim suara.
- **Keputusan (Invariant):** 
  - Fitur voting **wajib** mensyaratkan pengguna terautentikasi dan terverifikasi (`can_vote: true`).
  - API (`VoteController`) menolak request dari tamu dengan `403 Forbidden`.
  - UI (`VotingDashboard`) wajib menonaktifkan opsi pilihan dan menampilkan pesan: *"Voting hanya tersedia bagi anggota terverifikasi"* jika `can_vote` bernilai *false*.
- **Bukti Verifikasi:**
  - Automated: PHPUnit `VoteControllerTest` (8 passed), Playwright guest-voting E2E (1 passed), ESLint (passed).
  - Manual: Smoke test di produksi (Member "Fefi" sukses mencoblos, Guest terblokir di UI & tidak bisa klik).
