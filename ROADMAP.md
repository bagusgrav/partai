# ROADMAP PENGEMBANGAN APLIKASI AKUHADIR.MY.ID
**Visi:** Membangun partai politik ideal yang digerakkan oleh kapasitas intelektual, transparansi data, dan pengawasan publik—bukan kekuasaan elite atau dinasti.

Dokumen ini adalah panduan fase demi fase. "Lengkap dan Tuntas" berarti seluruh 8 fungsi partai ideal berjalan secara digital, terintegrasi, dan dapat diaudit oleh publik.

## 🟢 FASE 0: FONDASI & MESIN INTI (SELESAI)
*Status: MVP sudah di-push dan siap deploy ke Vercel/VPS.*
- [x] Arsitektur SSOT (4 Pilar Dokumen).
- [x] Database SQLite WAL (Zero-RAM) + Indeks Majemuk.
- [x] API Idempoten (`request_id`) & SM-2 Algorithm.
- [x] Pendaftaran anggota resmi dengan token Sanctum dan halaman profil.
- [x] UI Kuis Dasar + `fetchWithRetry`.
- [x] Panel Admin Filament (Kelola Soal & User).

## 🟡 FASE 1: PENDIDIKAN KEWARGAAN (MODUL BELAJAR INTERAKTIF)
*Tujuan: Memastikan setiap anggota memiliki kapasitas intelektual yang terukur.*
- [ ] **Kurikulum Penuh:** Bank soal 5 domain (Hak, Alasan Bernegara, Keuangan, Hukum, Politik) hingga Level 4 (Kritisi).
- [x] **Sistem Esai & Peer-Review:** Untuk soal Level 3 & 4, anggota menjawab esai pendek yang dinilai oleh panel (bukan pilihan ganda).
- [x] **Pembahasan Esai:** Kunci jawaban ideal ditampilkan setelah esai dikirim ke antrean penilaian; pembahasan tidak bocor pada daftar soal.
- [ ] **Mastery Score & Sertifikasi:** Sistem lencana (badge) digital bagi anggota yang lulus level tertentu.
- [x] **Daily Digest:** Notifikasi harian otomatis berisi 1 soal, 1 berita pengawasan, dan 1 data kesejahteraan.

## 🟠 FASE 2: AKSI LAPANGAN (MODUL "AKU HADIR")
*Tujuan: Menggerakkan anggota di lapangan dengan validasi anti-kolusi.*
- [x] **Atestasi Aksi (QR & Geofencing):** Koordinator membuat QR berputar 90 detik di lokasi aksi. Anggota memindai untuk klaim kehadiran.
- [x] **Validasi Silang Lintas Cluster:** Klaim kehadiran wajib divalidasi oleh 2 anggota dari cluster (wilayah) berbeda.
- [x] **Sistem Skor Reputasi (Fase 2):** Mastery Score per domain dipisahkan dari Participation Score kumulatif; kontribusi lapangan/non-fisik berbobot setara dan dicatat dalam ledger audit append-only.
- [ ] **Juri Warga (Sortisi):** Pemilihan acak anggota untuk mengaudit klaim kehadiran yang ditandai sistem (anti-kolusi).

## 🔵 FASE 3: PENGAWASAN DATA & KESEJAHTERAAN (OPEN DATA)
*Tujuan: Menjemput hak rakyat dan mengawasi kebocoran anggaran.*
- [x] **Fondasi Data Publik:** Skema data kesejahteraan, seeder simulasi BPS/BNPB, API ringkasan/wilayah, panel admin, dan dasbor publik provinsi.
- [x] **Pipeline Data BPS & BNPB:** Penarikan terjadwal dengan Laravel Scheduler, integrasi BPS WebAPI dan BNPB ArcGIS, serta fallback data mock saat API key belum dikonfigurasi.
- [x] **Dasbor Ekonomi & Wawasan:** Indikator ekonomi, kurs API publik, berita RSS ANTARA, API publik, dan pembaruan otomatis terjadwal.
- [ ] **Indeks Kerentanan Kesejahteraan (IKK):** Dasbor publik per kabupaten/kota yang menggabungkan data BPS & BNPB; perhitungan IKK dan data kabupaten/kota belum tersedia.
- [x] **Modul "Jemput Hak" (Rights Tracker):** Anggota bisa mengecek status bansos (PKH, BPNT, PIP) berdasarkan NIK.
- [ ] **Perisai Pendidikan:** Pelacak dana BOS per sekolah dan kanal pengaduan pungli anonim.
- [ ] **Pengawasan Birokrasi:** Dasbor "Rapor ASN" dan pelaporan penyalahgunaan fasilitas negara.

## 🟣 FASE 4: TATA KELOLA PARTAI (GOVERNANCE & RECALL)
*Tujuan: Mencegah dinasti, korupsi internal, dan membangun demokrasi partai.*
- [x] **Komunitas (eksperimen MVP, kode dan tes):** Microblog teks dengan feed cursor-based, visibilitas publik/anggota, pelaporan, moderasi, dan rate limit; tes backend terfokus lulus. Deployment, validasi operasional, dan keputusan perluasan tetap roadmap.
- [x] **Voting Parameter & Kebijakan:** UI daftar voting aktif, konfirmasi pilihan, satu suara idempoten untuk anggota terverifikasi, status partisipasi, dan hasil agregat setelah memilih; implementasi kode lokal, belum klaim deployment.
- [x] **Voting Security Fix:** Membatasi endpoint `/api/votes/submit` dan UI Voting hanya untuk anggota terverifikasi (Menolak Guest dengan 403). *Status: Terverifikasi (Manual + Tes Otomatis) per 20 Nov 2026.*
- [x] **Panel Audit (Sortisi):** Pemilihan acak hingga lima anggota terverifikasi dengan `overall_mastery_level >= 4`.
- [ ] **Sortisi Ber-seed Publik:** Publikasi seed dan snapshot kelayakan agar pengundian dapat direproduksi tanpa membuka data pribadi.
- [ ] **Panel Etik & Editorial:** Pemilihan acak anggota untuk Panel Etik dan Panel Editorial.
- [ ] **Kontrak Faksi Anggota Dewan:** Dasbor publik yang melacak janji kampanye anggota dewan.
- [x] **Mekanisme Recall:** Pengajuan recall berbukti, voting anggota, dan pencabutan role saat dukungan mencapai 2/3 anggota aktif.
