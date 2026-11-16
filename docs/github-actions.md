# Menyiapkan GitHub Actions untuk deployment API

1. Buka repository GitHub, lalu pilih **Settings → Secrets and variables →
   Actions**.
2. Tambahkan tiga **Repository secrets**:
   - `SSH_HOST`: `103.27.206.250`
   - `SSH_USER`: `deploy`
   - `SSH_KEY`: private SSH key untuk pengguna deployment.

Gunakan key deployment khusus yang dibuat pada komputer tepercaya, bukan
private key root VPS. Pasang pasangan public key di
`~deploy/.ssh/authorized_keys` pada VPS, lalu simpan isi private key sebagai
`SSH_KEY`. Jangan commit private key ke repository atau mencetaknya ke log.

Pastikan pengguna `deploy` dapat menarik repository di `/var/www/partai` dan
memiliki izin tulis yang sudah dikonfigurasi untuk direktori runtime Laravel.
Berikan hanya izin sudo minimum yang diperlukan untuk menjalankan
`systemctl reload php8.3-fpm`. Setelah ketiga secrets tersedia, workflow
`.github/workflows/deploy-api.yml` akan memanggil `api/deploy.sh` ketika ada
perubahan pada `api/**` atau file workflow tersebut di branch `main`.
