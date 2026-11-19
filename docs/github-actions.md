# Menyiapkan GitHub Actions untuk deployment API

1. Buka repository GitHub, lalu pilih **Settings → Secrets and variables →
   Actions**.
2. Tambahkan tiga **Repository secrets**:
   - `SSH_HOST`: hostname atau alamat IP VPS deployment.
   - `SSH_USER`: `deploy`
   - `SSH_KEY`: private SSH key khusus deployment.

Gunakan key deployment khusus yang dibuat pada komputer tepercaya, bukan
private key root VPS. Pasang pasangan public key di
`~deploy/.ssh/authorized_keys` pada VPS, lalu simpan isi private key sebagai
`SSH_KEY`. Jangan commit private key ke repository atau mencetaknya ke log.

Pastikan checkout repository ada di `/var/www/thortech/partai`, branch aktifnya
`main`, dan pengguna `deploy` dapat menarik branch tersebut. Pengguna ini juga
memerlukan PHP 8.4 CLI, Composer, `curl`, izin tulis yang sudah dikonfigurasi
untuk direktori runtime Laravel, serta izin sudo minimum tanpa prompt password
untuk `systemctl reload php8.4-fpm`.

Workflow `.github/workflows/deploy-api.yml` berjalan pada push ke branch
`main` jika ada perubahan di `api/**` atau file workflow. Workflow tersambung
ke VPS menggunakan tiga secrets di atas, berpindah ke `/var/www/thortech/partai`,
dan menjalankan `bash api/deploy.sh`. Skrip mensyaratkan branch `main` dan
working tree bersih, mengambil pembaruan dengan `git pull --ff-only origin
main`, lalu memasang dependency produksi, menjalankan migrasi/cache Laravel,
reload PHP-FPM 8.4, dan memeriksa endpoint lokal
`http://127.0.0.1:8000/up`. Rilis berhenti jika working tree berubah atau
health check gagal; skrip tidak menghapus perubahan lokal maupun file
untracked/ignored. Prosedur rollback manual dan kehati-hatian migrasi dijelaskan
di [deployment.md](./deployment.md).
