# PARTAI — Aturan Sistem dan Invariant

Dokumen ini merangkum batas teknis yang harus dipertahankan. Untuk prosedur kontribusi, lihat [CONTRIBUTING.md](CONTRIBUTING.md); untuk rincian sistem dan status kesenjangan, lihat [docs/architecture.md](docs/architecture.md) dan [ECOSYSTEM.md](ECOSYSTEM.md).

## Standar Thortech v2.0

- **Monolith:** aplikasi backend adalah Laravel modular monolith, bukan arsitektur microservices.
- **Single Door Policy:** semua akses publik ke origin API melalui Cloudflare Tunnel dengan `protocol: http2`; servis backend bind ke loopback dan tidak membuka port origin langsung ke internet.
- **SSO:** identitas utama terpusat di `https://id.akuhadir.my.id`. Jangan membuat form atau login lokal baru di frontend. Rute guest/register yang masih ada adalah jalur legacy/pendaftaran, bukan bukti bahwa pertukaran SSO end-to-end sudah tersedia.
- **Zero-RAM:** sasaran operasi ber-footprint rendah tanpa layanan database/cache tambahan yang tidak disetujui. Istilah Zero-RAM bukan berarti proses database memakai RAM nol; ukur serta batasi konsumsi aktual.
- **SQLite:** database produksi `/var/data/partai/database.sqlite`, dengan WAL, `synchronous=NORMAL`, `busy_timeout=5000`, dan foreign keys aktif. Database tidak boleh disimpan di `/var/www/`.
- **Idempotensi:** setiap operasi tulis baru yang aman diulang wajib menerima UUID `request_id`. Retry dengan ID dan payload yang sama tidak menggandakan efek; ID sama dengan payload berbeda harus ditolak. Beberapa rute lama masih menjadi pengecualian dan harus ditransisikan, bukan dianggap patuh.
- **Frontend API:** gunakan `fetchWithRetry` (dua retry, jeda 1.500 ms pada kegagalan jaringan `TypeError`) dan tangani status HTTP gagal secara eksplisit. Mutasi yang dicoba ulang harus mempertahankan `request_id`.
- **Skema:** lindungi data inti dari perubahan destruktif. Gunakan migrasi maju dan UUID untuk entitas baru, kecuali ada keputusan arsitektur terdokumentasi.
- **Roadmap:** fitur harus selaras dengan [ROADMAP.md](ROADMAP.md); fitur di luar roadmap memerlukan persetujuan maintainer.
- **Privasi:** rahasia dan data pribadi warga tidak masuk repository, log, issue publik, atau fixture yang dibagikan.

## Kesesuaian dan pengecualian terpantau

`docs/cloudflared-config.yml` menetapkan `protocol: http2`, panduan deployment menempatkan database di `/var/data/partai`, dan `AppServiceProvider` mengatur PRAGMA SQLite. Namun, rute autentikasi yang terlihat sekarang masih menyediakan guest/register; beberapa mutasi juga belum menerima `request_id`. Perubahan dokumentasi tidak mengubah keadaan runtime. Lihat daftar rute dan pengecualian terkini di [docs/api-reference.md](docs/api-reference.md).
