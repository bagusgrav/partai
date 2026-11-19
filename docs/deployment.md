# Panduan Deployment Produksi

Panduan ini menyiapkan Laravel API di VPS Debian 12, Cloudflare Tunnel, dan
Next.js di Vercel. Ganti nilai placeholder (`<...>`) sebelum menjalankan
perintah. Perintah di bawah adalah panduan manual dan belum dijalankan.

## 1. Siapkan VPS Debian 12

1. Perbarui paket. Debian 12 bawaan dapat menyediakan PHP lebih lama dari
   persyaratan aplikasi (`^8.3`); bila PHP 8.4 tidak tersedia, tambahkan
   repositori PHP yang Anda percayai mengikuti instruksi dan verifikasi kunci
   repositorinya sebelum memasang paket. Lalu pasang Nginx, Git, SQLite,
   PHP 8.4 CLI dan FPM, serta ekstensi PHP yang dibutuhkan Laravel:

   ```bash
   sudo apt update
   sudo apt install -y nginx git sqlite3 unzip \
     php8.4-cli php8.4-fpm php8.4-sqlite3 php8.4-mbstring \
     php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl curl
   ```

2. Pasang Composer mengikuti petunjuk instalasi resmi Composer, lalu pastikan
   `php -v` melaporkan PHP 8.4, serta `composer --version`, `nginx -v`, dan
   `sqlite3 --version` berhasil.
3. Pasang `cloudflared` dari repositori paket Cloudflare untuk Debian, lalu
   verifikasi dengan `cloudflared --version`. Jangan menaruh token atau berkas
   kredensial Tunnel di repository.
4. Buat pengguna deployment dan direktori kode aplikasi. Database tetap
   disimpan terpisah dari kode:

   ```bash
   sudo adduser --disabled-password --gecos "" deploy
   sudo usermod -aG www-data deploy
   sudo install -d -o deploy -g www-data -m 0755 /var/www/thortech/partai
   sudo -iu deploy
   git clone <REPOSITORY_URL> /var/www/thortech/partai
   cd /var/www/thortech/partai/api
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   chgrp www-data .env
   chmod 0640 .env
   ```

5. Isi `.env` produksi (minimal `APP_ENV=production`,
   `APP_DEBUG=false`, `APP_URL=https://api.akuhadir.my.id`, dan
   `DB_CONNECTION=sqlite`). Atur `DB_DATABASE` ke path absolut yang dijelaskan
   di bagian SQLite. Jangan commit `.env` atau `APP_KEY`.

### Nginx dan PHP-FPM

Konfigurasi Tunnel di bawah meneruskan HTTP/2 ke `127.0.0.1:8000`. Karena itu,
Nginx harus mendengarkan pada port lokal tersebut dan meneruskan PHP ke
PHP-FPM. Buat server block (misalnya
`/etc/nginx/sites-available/partai-api`) dengan isi berikut, sesuaikan
`root` bila repository diletakkan di lokasi lain:

```nginx
server {
    listen 127.0.0.1:8000;
    server_name api.akuhadir.my.id;
    root /var/www/thortech/partai/api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi dengan symlink ke `sites-enabled`, validasi dengan
`sudo nginx -t`, lalu reload Nginx. Pastikan port 8000 hanya bind ke loopback;
Tunnel menjadi satu-satunya jalur publik ke API. Pastikan pengguna `www-data`
memiliki akses baca ke source dan akses tulis ke `api/storage` serta
`api/bootstrap/cache`. Atur direktori runtime agar dapat ditulis oleh PHP-FPM
dan pengguna deployment:

```bash
sudo chown -R deploy:www-data /var/www/thortech/partai/api/storage \
  /var/www/thortech/partai/api/bootstrap/cache
sudo chmod -R g+rwX /var/www/thortech/partai/api/storage \
  /var/www/thortech/partai/api/bootstrap/cache
sudo find /var/www/thortech/partai/api/storage /var/www/thortech/partai/api/bootstrap/cache \
  -type d -exec chmod g+s {} +
```

## 2. Pindahkan database SQLite ke lokasi persisten

Database produksi wajib berada di `/var/data/partai/`, bukan di `/var/www/`.
Hentikan sementara proses yang menulis ke database sumber. Dari Windows
PowerShell pada mesin yang memiliki database sumber, buat backup SQLite yang
konsisten (SQLite Online Backup menyertakan perubahan yang masih berada di
WAL), lalu transfer backup ke direktori sementara VPS dengan SCP. Pastikan
SQLite CLI tersedia di mesin sumber:

```powershell
sqlite3 "<PATH_DATABASE_SUMBER>" ".backup partai-database.sqlite"
scp .\partai-database.sqlite deploy@<SSH_HOST>:/tmp/partai-database.sqlite
```

Di VPS, salin langsung ke lokasi data persisten dan atur permission:

```bash
sudo install -d -o deploy -g www-data -m 2770 /var/data/partai
sudo install -o deploy -g www-data -m 0660 \
  /tmp/partai-database.sqlite /var/data/partai/database.sqlite
rm /tmp/partai-database.sqlite
```

Jika database sumber tidak ada (instalasi baru), buat file kosong langsung
di direktori tersebut dengan pemilik dan permission yang sama:

```bash
sudo install -d -o deploy -g www-data -m 2770 /var/data/partai
sudo install -o deploy -g www-data -m 0660 /dev/null \
  /var/data/partai/database.sqlite
```

Jangan menyalin hanya file `.sqlite` dari database yang sedang aktif;
perubahan yang belum di-checkpoint mungkin masih berada di file `-wal`/`-shm`.

Atur `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/var/data/partai/database.sqlite
```

Pastikan direktori tetap writable oleh grup `www-data` agar SQLite dapat
membuat file WAL/SHM. `api/app/Providers/AppServiceProvider.php` mengaktifkan
`journal_mode=WAL`, `synchronous=NORMAL`, `busy_timeout=5000`, dan
`foreign_keys=ON`; pertahankan konfigurasi tersebut. Setelah `.env` benar,
jalankan migrasi melalui skrip deployment.

## 3. Konfigurasikan Nginx dan Cloudflare Tunnel

1. Buat Tunnel dari dashboard Cloudflare atau CLI `cloudflared tunnel login`
   lalu `cloudflared tunnel create <NAMA_TUNNEL>`. Simpan UUID Tunnel dan
   credentials JSON di VPS dengan akses terbatas untuk root.
2. Arahkan DNS hostname ke Tunnel:

   ```bash
   cloudflared tunnel route dns <TUNNEL_UUID> api.akuhadir.my.id
   ```

3. Salin [cloudflared-config.yml](./cloudflared-config.yml) ke
   `/etc/cloudflared/config.yml`. Ganti `<TUNNEL_UUID>` pada `tunnel` dan
   `credentials-file`, lalu simpan credentials di lokasi yang ditunjuk.
   Pertahankan `protocol: http2` dan service `http://127.0.0.1:8000`.
4. Pasang service systemd dengan `sudo cloudflared service install`, verifikasi
   konfigurasi, lalu aktifkan `sudo systemctl enable --now cloudflared`.
   Periksa `sudo systemctl status cloudflared` dan log service bila Tunnel
   belum tersambung.

## 4. Siapkan GitHub Actions Secrets

Di repository GitHub, buka **Settings → Secrets and variables → Actions** dan
buat repository secrets berikut:

- `SSH_HOST`: hostname atau alamat IP VPS yang menjalankan aplikasi.
- `SSH_USER`: `deploy`.
- `SSH_KEY`: private SSH key yang pasangannya sudah ada pada
  `~deploy/.ssh/authorized_keys` di VPS.

Gunakan key khusus deployment dengan akses terbatas. Jangan menaruh private key
di repository atau mencetak nilainya ke log. Workflow
[deploy-api.yml](../.github/workflows/deploy-api.yml) berjalan pada push ke
`main` yang mengubah `api/**` atau file workflow; workflow terhubung sebagai
`deploy` dan memanggil [deploy.sh](../api/deploy.sh) di
`/var/www/thortech/partai`. Akun tersebut harus dapat menjalankan Composer,
PHP 8.4 CLI, `curl`, menarik branch `main`, dan menjalankan
`sudo systemctl reload php8.4-fpm` tanpa prompt password. Berikan izin sudo
minimum yang diperlukan. Detail secrets juga tersedia di
[github-actions.md](./github-actions.md).

Untuk deployment manual, masuk ke VPS sebagai `deploy`, lalu jalankan
`cd /var/www/thortech/partai && bash api/deploy.sh`. Skrip memastikan branch
aktif adalah `main` dan working tree bersih, kemudian menjalankan
`git pull --ff-only origin main`, instalasi dependency produksi, migrasi paksa,
cache Laravel, reload PHP-FPM 8.4, dan health check
`http://127.0.0.1:8000/up`. Pull fast-forward-only tidak membuang commit lokal;
skrip membatalkan rilis bila mendeteksi perubahan lokal. Skrip tidak
membersihkan file untracked atau ignored, termasuk `.env`, database, dan
`storage/logs`. Siapkan konfigurasi `.env`, database, permission, dan akses
sudo sebelum menjalankan.

### Rollback manual bila deployment gagal

Sebelum rilis, catat commit aplikasi terakhir yang sehat. Skrip deployment
membuat dan memverifikasi backup SQLite secara otomatis sebelum migrasi.
Untuk menjalankan backup manual dan prosedur restore terperinci, lihat
[backup-restore.md](./backup-restore.md).
Jika health check gagal, periksa log workflow, PHP-FPM, Nginx, dan Laravel.
Jangan mengulang deploy sebelum penyebab kegagalan dipahami.

Untuk mengembalikan kode aplikasi, sebagai `deploy` pastikan tidak ada
perubahan lokal, lalu pindah sementara ke commit sehat yang dicatat:

```bash
cd /var/www/thortech/partai
if [[ -n "$(git status --porcelain)" ]]; then
  echo "Working tree tidak bersih; batalkan rollback dan amankan perubahan lokal." >&2
  exit 1
fi
git switch --detach <KNOWN_GOOD_COMMIT>
cd api
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.4-fpm
curl --fail --show-error http://127.0.0.1:8000/up
```

Jalankan rollback kode hanya jika status bersih; jangan memakai `reset --hard`
atau `git clean`. Setelah pemulihan sementara, kembali ke branch `main` dan
lakukan rollback rilis permanen melalui commit/revert yang ditinjau sebelum
deployment berikutnya. Rollback kode tidak membatalkan migrasi atau memulihkan
data. Jangan menjalankan `migrate:rollback` atau memulihkan backup database
secara otomatis: tinjau kompatibilitas migrasi dan dampak data, hentikan
penulisan bila perlu, dan restore hanya dari backup terverifikasi dengan
persetujuan operator.

## 5. Aktifkan Laravel Scheduler

Perintah terjadwal (termasuk pembaruan RSS berita setiap jam serta kurs dan
ringkasan ekonomi setiap hari) berjalan hanya jika Laravel Scheduler dipanggil.
Sebagai pengguna `deploy`, tambahkan satu entri cron:

```cron
* * * * * cd /var/www/thortech/partai/api && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Sesuaikan path PHP jika `command -v php` menunjukkan lokasi berbeda. Verifikasi
jadwal dengan `php artisan schedule:list` dan pastikan cron aktif melalui
`systemctl status cron`.

## 6. Deploy frontend ke Vercel

1. Import repository GitHub di Vercel dan pilih **Root Directory** `web`.
   Gunakan framework preset Next.js.
2. Di **Project Settings → Environment Variables**, tambahkan:

   ```text
   NEXT_PUBLIC_API_URL=https://api.akuhadir.my.id/api
   ```

   Terapkan ke environment yang akan dipakai (Production; tambahkan Preview
   bila preview memang harus mengakses API produksi).
3. Pastikan branch produksi sesuai branch yang digunakan untuk deployment,
   kemudian deploy/redeploy project.
4. Verifikasi aplikasi di `https://akuhadir.my.id` dan API melalui hostname
   `https://api.akuhadir.my.id`. CORS API membatasi origin ke domain produksi
   dan `http://localhost:3000`; konfigurasi header Vercel tidak menggantikan
   CORS Laravel.
