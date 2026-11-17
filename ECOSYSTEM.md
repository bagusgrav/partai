# Kontrak Ekosistem Thortech — PARTAI

Dokumen ini menetapkan aturan integrasi PARTAI dengan aplikasi Thortech/Aku Hadir sekarang maupun di masa depan. Ia adalah kontrak arsitektur; jangan mengasumsikan endpoint atau alur yang belum terimplementasi sebagai layanan produksi.

## Identitas dan batas kepercayaan

- **Single Door Policy:** akses eksternal ke API PARTAI hanya melalui `https://api.akuhadir.my.id` yang diteruskan Cloudflare Tunnel `protocol: http2` ke origin loopback. Jangan menghubungi atau mengekspos alamat IP/port VPS secara langsung.
- **SSO pusat:** `https://id.akuhadir.my.id` adalah penyedia identitas utama. Aplikasi konsumen mengarahkan pengguna ke penyedia tersebut, memvalidasi hasil autentikasi melalui mekanisme server-ke-server/token yang disepakati, lalu mengirim kredensial berumur terbatas ke API. Jangan mengirim password SSO ke PARTAI dan jangan membuat sistem login lokal baru.
- **Status implementasi:** backend memakai autentikasi bearer Laravel Sanctum. Rute yang saat ini terlihat di `api/routes/api.php` menyediakan `/api/auth/guest` dan `/api/auth/register`; tidak terlihat callback atau exchange token SSO. Karena itu, belum ada dasar untuk menganggap pertukaran SSO end-to-end sudah tersedia. Setiap integrasi SSO harus menyepakati issuer, audience, signature/JWKS atau introspection, expiry, logout/revocation, dan pemetaan identitas sebelum rute dibangun.
- **Otorisasi:** identitas terautentikasi tidak otomatis memiliki wewenang untuk setiap fungsi. API memeriksa autentikasi, role, dan kepemilikan data secara terpisah.

## Transport dan aplikasi

| Area | Kontrak |
| --- | --- |
| Origin API | `https://api.akuhadir.my.id/api` |
| Frontend | `https://akuhadir.my.id` (Next.js pada Vercel) |
| Tunnel | Cloudflare Tunnel, `protocol: http2`, API diteruskan ke origin loopback |
| Backend | Satu Laravel modular monolith; satu kontrak REST/JSON |
| Database | SQLite WAL persisten pada `/var/data/partai/database.sqlite` |
| Admin | Panel Laravel pada `/admin`; akses dibatasi oleh role aplikasi |

Permintaan menggunakan HTTPS dan JSON (`Accept: application/json`, serta `Content-Type: application/json` untuk body JSON). Perubahan path atau bentuk payload harus diperbarui di [docs/api-reference.md](docs/api-reference.md) sebelum integrator mengadopsinya.

## Kontrak API antar-aplikasi

1. Gunakan path dan skema yang terdokumentasi di [docs/api-reference.md](docs/api-reference.md). Jangan mengandalkan field yang tidak tercantum atau detail internal tabel.
2. Endpoint publik hanya untuk data yang memang boleh dipublikasikan. Endpoint privat memerlukan bearer token resmi dan pemeriksaan otorisasi.
3. Panggilan tulis baru membawa `request_id` UUID sebagai idempotency key. Aplikasi pemanggil menyimpan ID yang sama selama retry operasi yang sama; payload berbeda tidak boleh memakai ID itu lagi.
4. Respons error tidak boleh disamarkan sebagai sukses. Konsumen perlu menangani paling tidak `401` (belum terautentikasi), `403` (tidak berwenang), `404` (tidak ditemukan), `409` (konflik/idempotency mismatch), `422` (validasi), dan `429` (rate limit).
5. Semua konsumen frontend memakai helper retry resmi (`fetchWithRetry`). Retry otomatis saat gangguan jaringan bukan izin untuk menggandakan mutasi.
6. API yang mengirim NIK atau data warga harus memiliki tujuan yang sah, autentikasi dan otorisasi yang tepat, pembatasan laju, minimisasi data, serta kebijakan cache/log yang ketat.
7. Jangan menaruh token, secret, NIK, atau data sensitif dalam query string, log, URL analitik, contoh publik, atau repository.

### Kesenjangan kompatibilitas yang diketahui

Endpoint mutasi kontrak dan penandaan notifikasi telah menerima UUID `request_id` dan menangani replay sesuai kontrak di [referensi API](docs/api-reference.md). Rute guest/register merupakan jalur autentikasi yang tersedia saat ini, bukan alur SSO yang telah dibuktikan. Integrator harus mengikuti perilaku aktual di referensi API.

## Data dan reliabilitas

- SQLite memakai WAL, `synchronous=NORMAL`, `busy_timeout=5000`, dan foreign keys.
- **Zero-RAM** berarti sasaran footprint rendah, bukan database tanpa konsumsi memori. Jangan menambah Redis, broker, atau database lain tanpa persetujuan arsitektur dan rencana operasional.
- Perubahan data inti memerlukan migrasi kompatibel dan prosedur backup/restore. Jangan menyalin hanya file SQLite saat WAL aktif; ikuti prosedur backup pada [docs/deployment.md](docs/deployment.md).
- Integrasi pihak ketiga menggunakan timeouts, respons error yang jelas, retry terbatas, dan idempotensi sesuai sifat operasinya.

## Evolusi kontrak

Perubahan breaking harus dibahas dengan maintainer dan konsumen terkait. Jika tidak dapat mempertahankan kompatibilitas, rencanakan versi API baru, masa transisi, serta jadwal penghentian versi lama; jangan mengganti arti field secara diam-diam. Setiap perubahan implementasi memperbarui referensi API, blueprint arsitektur, dan panduan kontribusi yang berkaitan.
