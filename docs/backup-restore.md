# Backup dan Restore SQLite Produksi

Database produksi berada di `/var/data/partai/database.sqlite` dan memakai
WAL. Jangan membuat backup dengan menyalin file database aktif: transaksi yang
sudah commit mungkin masih berada di file WAL. Skrip backup menggunakan
SQLite Online Backup (`.backup`), memeriksa integritas hasilnya, lalu
menyimpan backup di `/var/data/partai/backups/`.

## Membuat backup

Jalankan sebagai pengguna deployment pada VPS:

```bash
cd /var/www/thortech/partai/api
bash scripts/backup-db.sh
```

Pastikan `sqlite3` tersedia dan pengguna yang menjalankan skrip dapat membaca
database serta menulis ke `/var/data/partai/`. Skrip membuat direktori backup
bila belum ada dan menghapus file `database-*.sqlite` yang lebih lama dari
7 hari. Backup otomatis juga dibuat oleh `api/deploy.sh` tepat sebelum
migrasi. Backup lokal di VPS bukan salinan di luar host; untuk perlindungan
bencana pada VPS atau disk, salin backup yang sudah dibuat ke penyimpanan
terpisah yang aksesnya dibatasi.

## Memulihkan backup

Pilih backup yang ingin dipulihkan dan verifikasi sebelum mengganti database:

```bash
sqlite3 /var/data/partai/backups/database-YYYYMMDD-HHMMSS.sqlite \
  'PRAGMA integrity_check;'
```

Perintah harus mencetak `ok`. Sesuaikan nama file dengan backup yang dipilih.

1. Hentikan PHP-FPM agar request tidak menulis ke database:

   ```bash
   sudo systemctl stop php8.4-fpm
   ```

2. Hentikan atau jeda semua penulis database lain, termasuk worker antrean dan
   scheduler Laravel. Jika scheduler dijalankan melalui cron, jeda entri
   `schedule:run` selama proses pemulihan dan tunggu proses yang sedang berjalan
   selesai.
3. Siapkan file restore di direktori database dengan kepemilikan dan mode yang
   sesuai. Ganti nama backup pada perintah berikut:

   ```bash
   BACKUP=/var/data/partai/backups/database-YYYYMMDD-HHMMSS.sqlite
   RESTORE_TMP=/var/data/partai/database.sqlite.restore
   sudo install -o deploy -g www-data -m 0660 "$BACKUP" "$RESTORE_TMP"
   sudo sqlite3 "$RESTORE_TMP" 'PRAGMA integrity_check;'
   ```

   Pastikan pemeriksaan kedua juga mencetak `ok` sebelum melanjutkan.
4. **Pastikan PHP-FPM, worker, dan scheduler tetap berhenti.** File
   `database.sqlite-wal` dapat berisi transaksi committed yang belum
   di-checkpoint; jangan menghapus `-wal` atau `-shm` saat proses masih
   menggunakan database. Setelah semua penulis berhenti, ganti database dan
   hapus sidecar lama agar WAL lama tidak diterapkan ke database yang baru:

   ```bash
   sudo mv "$RESTORE_TMP" /var/data/partai/database.sqlite
   sudo rm -f /var/data/partai/database.sqlite-wal \
     /var/data/partai/database.sqlite-shm
   ```

5. Nyalakan kembali layanan dan scheduler yang dijeda:

   ```bash
   sudo systemctl start php8.4-fpm
   ```

   Lanjutkan scheduler/worker yang dihentikan. Periksa log Laravel dan
   verifikasi health check API, misalnya
   `curl --fail --show-error http://127.0.0.1:8000/up`.

Pemulihan mengembalikan data ke waktu backup dan dapat menghilangkan perubahan
setelah waktu tersebut. Jangan jalankan migrasi atau hapus sidecar secara
otomatis sebagai bagian dari restore tanpa persetujuan operator.
