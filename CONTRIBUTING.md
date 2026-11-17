# Panduan Kontribusi

Terima kasih sudah membantu mengembangkan PARTAI. Kontribusi sebaiknya kecil, teruji, dapat ditinjau, dan menjaga privasi warga serta kontrak arsitektur yang ada. Diskusikan perubahan besar melalui issue sebelum mulai mengerjakannya.

## Alur kerja

1. Periksa [ROADMAP.md](ROADMAP.md), issue terbuka, dan dokumentasi terkait.
2. Buat branch dari `main` dengan nama yang menjelaskan perubahan:
   - `feat/quiz-review`
   - `fix/attendance-retry`
   - `docs/api-contract`
   - `chore/update-dependencies`
3. Implementasikan perubahan sekecil mungkin dan tambahkan tes untuk perilaku baru atau bug yang diperbaiki.
4. Jalankan pemeriksaan yang sesuai:

   ```powershell
   cd api
   php artisan test
   cd ..\web
   npm run lint
   npm run build
   ```

5. Kirim pull request ke `main`. Jelaskan masalah, perubahan, keputusan desain, pengujian yang dijalankan, risiko migrasi/data, dan tangkapan layar untuk perubahan UI.

Jangan menyertakan `.env`, kredensial, token, database produksi, atau data pribadi dalam commit, log, fixture, maupun tangkapan layar.

## Format commit

Gunakan [Conventional Commits](https://www.conventionalcommits.org/):

```text
<type>(<scope>): <ringkasan imperatif>
```

Contoh:

```text
feat(quiz): add review explanation
fix(api): reject duplicate attendance claim
docs(architecture): clarify tunnel boundary
```

Jenis umum: `feat`, `fix`, `docs`, `refactor`, `test`, `build`, dan `chore`. Gunakan scope opsional yang spesifik; satu commit sebaiknya mewakili satu perubahan logis.

## Invariant wajib

Invariant ini adalah batas arsitektur; perubahan yang menyentuhnya harus menjelaskan migrasi dan mendapatkan persetujuan maintainer sebelum diterapkan.

1. **Laravel modular monolith:** backend adalah satu aplikasi Laravel. Jangan memecah fitur menjadi layanan jaringan tersendiri tanpa keputusan arsitektur.
2. **Single Door Policy:** akses publik ke API melalui Cloudflare Tunnel `protocol: http2`; origin aplikasi hanya menerima koneksi lokal dari Tunnel/proxy. Jangan membuka port backend langsung ke internet.
3. **SSO terpusat:** autentikasi pengguna memakai `id.akuhadir.my.id`. Jangan menambahkan form atau sistem login lokal baru di Next.js. Halaman pendaftaran anggota yang sudah ada bukan alasan untuk memperluas mekanisme login lokal; dokumentasikan dan rencanakan penyelarasan jalur token yang lama.
4. **Idempotensi:** operasi tulis baru yang dapat dicoba ulang harus menerima UUID `request_id`, menyimpan hasil unik, mengembalikan hasil aman untuk retry, dan menolak penggunaan ulang ID dengan payload berbeda (`409`). Jangan menganggap seluruh endpoint tulis yang sudah ada telah memenuhi aturan; lihat daftar pengecualian di [referensi API](docs/api-reference.md).
5. **Retry frontend:** semua akses jaringan API dari frontend menggunakan `fetchWithRetry` di `web/src/lib/api/client.ts` dan memeriksa respons gagal secara eksplisit. Jangan retry operasi tulis dengan ID baru setelah timeout; ulangi memakai `request_id` yang sama.
6. **SQLite operasional:** produksi memakai SQLite WAL di `/var/data/partai/database.sqlite`, di luar direktori kode. Pertahankan `synchronous=NORMAL`, `busy_timeout=5000`, dan foreign keys. Jangan memindahkan database produksi ke `/var/www/`.
7. **Zero-RAM:** perlakukan ini sebagai sasaran footprint rendah, bukan klaim bahwa SQLite memakai nol memori. Pertahankan penggunaan memori yang terukur, hindari infrastruktur cache/database terpisah tanpa persetujuan, dan periksa dampak resource sebelum menambah komponen.
8. **Skema kompatibel:** hindari migrasi destruktif untuk data inti. Buat migrasi maju yang mempertahankan data, dan gunakan UUID pada identitas tabel baru kecuali terdapat alasan teknis yang disetujui.
9. **Privasi:** NIK dan informasi warga tidak boleh dicetak ke log, dimasukkan ke data contoh publik, atau dikirim ke pihak ketiga tanpa dasar dan persetujuan yang sesuai.
10. **Dokumentasi kontrak:** setiap perubahan rute, payload, autentikasi, atau perilaku retry harus memperbarui [docs/api-reference.md](docs/api-reference.md) dan dokumen terkait.

## Catatan implementasi saat ini

Sebagian API legacy belum memenuhi semua invariant, termasuk beberapa operasi tulis tanpa `request_id` dan rute `/api/auth/guest` serta `/api/auth/register`. Dokumentasi mencatat perilaku aktual agar integrator tidak salah mengira; PR baru tidak boleh menambah pengecualian tanpa keputusan dan rencana transisi yang terdokumentasi.

## Pelaporan isu

Issue publik cocok untuk bug dan usulan fitur non-sensitif. Jangan mengunggah rincian eksploit, rahasia, data pribadi, atau kredensial; sampaikan dugaan kerentanan secara privat kepada maintainer.
