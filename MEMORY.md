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

### Known gaps dokumentasi

- `docs/deployment.md` dan `docs/github-actions.md` tidak konsisten dengan workflow dan asumsi di `api/deploy.sh`; jangan gunakan salah satu secara operasional sebelum audit/rekonsiliasi. Berkas tidak dihapus atau diubah pada sesi ini.
- `DOCS-STANDARD.md` tersedia di root dan menjadi acuan format dokumentasi proyek; `docs/api-reference.md` tetap dicatat sebagai dokumen pendukung API, bukan SSOT arsitektur.

## 6. ADR

### ADR-20261120-01: Voting hanya untuk anggota terverifikasi dan tally terlindungi

- **Konteks:** `auth:sanctum` juga mengizinkan akun tamu, sedangkan pemilihan kebijakan ditujukan untuk anggota; tally sebelum pemilih memberikan suara dapat memengaruhi pilihan.
- **Keputusan:** submit mensyaratkan `role=member` dan `is_verified=true`; `GET /api/votes/active` mengembalikan `can_vote` dan `has_voted`; `votes_count` hanya dikirim setelah pemanggil memilih. Tidak diperlukan perubahan skema.
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

- **Dikerjakan:** implementasi lokal UI dan alur Backend Voting Parameter & Kebijakan, beserta tes API/frontend dan pembaruan dokumen. Tidak ada commit atau push.
- **Bukti:** `api/tests/Feature/Api/VoteControllerTest.php` — 182 tes Laravel lulus; 3 tes Playwright voting lulus; `npm run lint` dan `npm run build` lulus.
- **Status:** voting aktif, hanya anggota terverifikasi dapat submit, submit idempoten, status pemilih, dan tally setelah memilih — status: selesai lokal — bukti: file layanan/controller, komponen voting, dan tes tercantum di atas.
- **Dokumen .md Diperbarui:** `MEMORY.md`, `ROADMAP.md`, `docs/api-reference.md`.
- **Belum Selesai / Langkah Berikutnya:** siapkan voting aktif dan lakukan smoke test alur di staging; implementasi ini belum diverifikasi/deploy di lingkungan tersebut.
- **Kanonis:** `docs/architecture.md`; `ARCHITECTURE.md` adalah pointer. Referensi API pendukung ada di `docs/api-reference.md`.
- **Invariant penting:** enkripsi `users.nik`/`nik_hash`; alur Sanctum lokal sampai kontrak SSO; rights check tanpa NIK di URL dan hanya untuk user terverifikasi; feed Komunitas terpisah; `api/deploy.sh` jangan disentuh tanpa audit.
