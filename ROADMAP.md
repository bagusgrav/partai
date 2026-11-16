# ROADMAP PENGEMBANGAN APLIKASI AKUHADIR.MY.ID
**Visi:** Membangun partai politik ideal yang digerakkan oleh kapasitas intelektual, transparansi data, dan pengawasan publik—bukan kekuasaan elite atau dinasti.

Dokumen ini adalah panduan fase demi fase. "Lengkap dan Tuntas" berarti seluruh 8 fungsi partai ideal berjalan secara digital, terintegrasi, dan dapat diaudit oleh publik.

## 🟢 FASE 0: FONDASI & MESIN INTI (SELESAI)
*Status: MVP sudah di-push dan siap deploy ke Vercel/VPS.*
- [x] Arsitektur SSOT (4 Pilar Dokumen).
- [x] Database SQLite WAL (Zero-RAM) + Indeks Majemuk.
- [x] API Idempoten (`request_id`) & SM-2 Algorithm.
- [x] Pendaftaran anggota resmi dengan token Sanctum dan halaman profil.
- [x] UI Kuis Dasar (Duolingo-style) + `fetchWithRetry`.
- [x] Panel Admin Filament (Kelola Soal & User).

## 🟡 FASE 1: PENDIDIKAN KEWARGAAN (CIVIC DUOLINGO)
*Tujuan: Memastikan setiap anggota memiliki kapasitas intelektual yang terukur.*
- [ ] **Kurikulum Penuh:** Bank soal 5 domain (Hak, Alasan Bernegara, Keuangan, Hukum, Politik) hingga Level 4 (Kritisi).
- [x] **Sistem Esai & Peer-Review:** Untuk soal Level 3 & 4, anggota menjawab esai pendek yang dinilai oleh panel (bukan pilihan ganda).
- [ ] **Mastery Score & Sertifikasi:** Sistem lencana (badge) digital bagi anggota yang lulus level tertentu.
- [ ] **Daily Digest:** Notifikasi harian otomatis berisi 1 soal, 1 berita pengawasan, dan 1 data kesejahteraan.

## 🟠 FASE 2: AKSI LAPANGAN (MODUL "AKU HADIR")
*Tujuan: Menggerakkan anggota di lapangan dengan validasi anti-kolusi.*
- [x] **Atestasi Aksi (QR & Geofencing):** Koordinator membuat QR berputar 90 detik di lokasi aksi. Anggota memindai untuk klaim kehadiran.
- [x] **Validasi Silang Lintas Cluster:** Klaim kehadiran wajib divalidasi oleh 2 anggota dari cluster (wilayah) berbeda.
- [ ] **Sistem Skor Reputasi:** Pemberian poin untuk kehadiran fisik, riset, bantuan hukum, dan logistik.
- [ ] **Juri Warga (Sortisi):** Pemilihan acak anggota untuk mengaudit klaim kehadiran yang ditandai sistem (anti-kolusi).

## 🔵 FASE 3: PENGAWASAN DATA & KESEJAHTERAAN (OPEN DATA)
*Tujuan: Menjemput hak rakyat dan mengawasi kebocoran anggaran.*
- [x] **Fondasi Data Publik:** Skema data kesejahteraan, seeder simulasi BPS/BNPB, API ringkasan/wilayah, panel admin, dan dasbor publik provinsi.
- [ ] **Pipeline Data BPS & BNPB:** Scraper otomatis (via Laravel Scheduler) untuk data kemiskinan, stunting, dan kebencanaan; integrasi API resmi belum tersedia.
- [ ] **Indeks Kerentanan Kesejahteraan (IKK):** Dasbor publik per kabupaten/kota yang menggabungkan data BPS & BNPB; perhitungan IKK dan data kabupaten/kota belum tersedia.
- [ ] **Modul "Jemput Hak" (Rights Tracker):** Anggota bisa mengecek status bansos (PKH, BPNT, PIP) berdasarkan NIK.
- [ ] **Perisai Pendidikan:** Pelacak dana BOS per sekolah dan kanal pengaduan pungli anonim.
- [ ] **Pengawasan Birokrasi:** Dasbor "Rapor ASN" dan pelaporan penyalahgunaan fasilitas negara.

## 🟣 FASE 4: TATA KELOLA PARTAI (GOVERNANCE & RECALL)
*Tujuan: Mencegah dinasti, korupsi internal, dan membangun demokrasi partai.*
- [ ] **Voting Parameter & Kebijakan:** Anggota bisa memilih perubahan parameter atau agenda partai.
- [ ] **Sistem Panel (Sortisi):** Pemilihan acak untuk Panel Audit, Panel Etik, dan Panel Editorial.
- [ ] **Kontrak Faksi Anggota Dewan:** Dasbor publik yang melacak janji kampanye anggota dewan.
- [ ] **Mekanisme Recall:** Fitur pengajuan recall jika gagal memenuhi Kontrak Faksi atau melanggar etik.

## 🏁 DEFINISI "LENGKAP DAN TUNTAS"
Aplikasi dinyatakan Tuntas jika: Fungsional (Fase 1-4 berjalan), Skalabel (10.000 anggota aktif di VPS Zero-RAM), Aman & Auditable (Open Source, terbackup), dan Berdampak Nyata (digunakan anggota dewan untuk menginterpelasi anggaran korup).
