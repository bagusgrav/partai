# DOCS-STANDARD.md — Standar Disiplin Dokumentasi Kode & Transisi AI

Dokumen ini adalah aturan main mandatori di tingkat repositori untuk **AI Collaborator** (perencana/arsitek) dan **AI Coding Agent** (eksekutor kode).
Tujuannya memastikan setiap perpindahan alat bantu AI, sesi baru, atau pengembang manusia berlangsung mulus tanpa kehilangan konteks, tanpa lupa status pekerjaan, dan tanpa halusinasi teknis tentang:
1. **Apa aplikasi ini** (tujuan bisnis, arsitektur, dan batasannya).
2. **Apa yang sudah selesai** (beserta bukti nyata di kode/server).
3. **Apa yang belum selesai** (rencana, pekerjaan aktif, dan hambatan).
4. **Apa yang sudah stabil** (wilayah beku yang tidak boleh dirusak).

---

## 1. Prinsip Utama (Non-Negotiable)

1. **Tidak Ada Pekerjaan Tanpa Jejak `.md`:** Setiap perubahan fitur, perbaikan bug, keputusan arsitektur, atau temuan operasional **wajib** tercatat di berkas `.md` pada commit yang sama.
2. **Hormati SSOT Eksisting (Anti-Duplikasi):** Satu topik hanya boleh memiliki satu sumber kebenaran (*Single Source of Truth*). Sebelum membuat file baru, wajib cek konvensi nama file yang sudah ada di root repositori (misalnya `MEMORY.md` / `memori.md` dan `ARCHITECTURE.md` / `arsitektur.md`). Gunakan file yang sudah ada dan pertahankan bahasanya.
3. **Zero Hallucination & Klaim Berbukti:** Dilarang menyebut status selesai, jalur file, endpoint, nama service, atau konfigurasi tanpa memverifikasi keberadaannya langsung di repositori/sistem dan menyertakan buktinya.
4. **Lindungi Area Stabil:** Modul atau konfigurasi yang ditandai **Stabil** di dokumen memori adalah wilayah beku. Tidak boleh diubah tanpa alasan eksplisit, pengecekan test, dan pembaruan catatan keputusan.
5. **Dokumen Usang Lebih Berbahaya dari Tidak Ada:** Jika ditemukan catatan lama yang bertentangan dengan kondisi kode/infrastruktur saat ini, wajib diperbarui atau dihapus saat itu juga.

---

## 2. Peta Dokumen SSOT di Repositori

| Dokumen | Variasi Nama Eksisting | Peran Utama | Kapan Wajib Diperbarui |
|---|---|---|---|
| **Memori Hidup** | `MEMORY.md` / `memori.md` | Konteks aktif proyek: apa aplikasi ini, status fitur (`selesai`, `sedang-dikerjakan`, `belum`), area stabil, ADR/insiden, dan log sesi. | **Setiap akhir sesi kerja**, tanpa kecuali. |
| **Arsitektur Sistem** | `ARCHITECTURE.md` / `arsitektur.md` | Cetak biru teknis: stack, topologi runtime, struktur folder, database, alur deploy CI/CD, dan batasan lingkungan. | Setiap ada perubahan arsitektur, stack, endpoint, atau prosedur deploy. |
| **Entry Point** | `README.md` | Gambaran ringkas aplikasi, cara menjalankan secara lokal, dan daftar modul utama. | Saat fitur besar dirilis atau cara setup berubah. |
| **Rencana Kerja** *(opsional)* | `ROADMAP.md` | Daftar backlog jangka menengah/panjang jika proyek kompleks. (Jika tidak ada, cukup kelola di bagian `Berikutnya` pada `MEMORY.md`). | Saat status item rencana berubah. |

> **Aturan Konsolidasi:** Jika di root repositori ditemukan berkas catatan tercecer yang bertentangan (misal `deploy.md` atau `notes.md` lama), serap informasi pentingnya ke dokumen SSOT di atas lalu hapus file redundan tersebut.

---

## 3. Struktur Wajib `MEMORY.md` (atau `memori.md`)

Agar AI Collaborator dan AI Coding Agent langsung memahami kondisi proyek dalam sekali baca, dokumen memori wajib memuat struktur ringkas berikut (boleh menyesuaikan gaya heading yang sudah ada di repo selama seluruh elemen ini tercakup):

```markdown
# Memori Proyek — <Nama Aplikasi>

## 1. Apa Aplikasi Ini
<1 paragraf singkat: fungsi aplikasi, target pengguna, stack utama, dan posisi bisnisnya>

## 2. Stabil (Wilayah Beku — Jangan Diubah Tanpa Alasan Kuat)
- <fitur/modul/konfigurasi> — sejak <tanggal/commit> — bukti: <path/test/service>

## 3. Sudah Selesai
- <fitur/tugas> — status: selesai — bukti: <path/commit/URL>

## 4. Sedang Dikerjakan
- <fitur/tugas> — status: sedang-dikerjakan — catatan: <progres terakhir & sisa langkah>

## 5. Belum Dikerjakan / Berikutnya
- <fitur/tugas> — status: direncanakan | diblokir (sebutkan penghalangnya)

## 6. Keputusan Arsitektur & Insiden (ADR)
### ADR-YYYYMMDD-XX: <Judul Keputusan>
- **Konteks:** <masalah yang dihadapi>
- **Keputusan:** <solusi yang dipilih & larangan terkait>
- **Konsekuensi:** <dampak terhadap sistem & guardrail bagi AI berikutnya>

## 7. Log Sesi Terbaru (Maksimal 10 Entri Teratas)
### YYYY-MM-DD — <Ringkasan Singkat Sesi>
- **Perubahan:** <apa yang diubah>
- **Bukti:** <path file / hash commit / hasil test>
- **Dokumen Diperbarui:** <daftar file .md>
- **Langkah Berikutnya:** <1 langkah konkret untuk sesi selanjutnya>
```

---

## 4. Aturan Klaim & Verifikasi (Anti-Halusinasi)

AI Coding Agent dilarang menulis status tanpa bukti. Setiap pernyataan status harus mengikuti format:

```text
<klaim> — status: <selesai | sedang-dikerjakan | direncanakan | diblokir | belum-terverifikasi> — bukti: <path/commit/output-test>
```

* **`ada-di-kode` / `selesai` (lokal):** Wajib menyertakan path file atau commit hash yang sudah diperiksa.
* **`sudah-deploy` (produksi):** Hanya boleh diklaim jika ada bukti verifikasi runtime (output health check HTTP, status `systemd`, atau log deploy berhasil)—**bukan** asumsi semata karena `git push` selesai.
* **Tanpa bukti:** Wajib ditulis `belum-terverifikasi`.

---

## 5. Protokol Kerja AI Coding Agent

### Fase A: Sebelum Menulis Kode (Wajib)
1. Baca `DOCS-STANDARD.md`, dokumen memori (`MEMORY.md`/`memori.md`), dan dokumen arsitektur (`ARCHITECTURE.md`/`arsitektur.md`).
2. Periksa struktur folder dan status Git aktual (`git status`, `git remote -v`) sebelum berasumsi tentang path atau konfigurasi.
3. Sampaikan **Kontrak Pemahaman (3–5 baris)** kepada pengguna / AI Collaborator sebelum eksekusi:
   * Aplikasi apa & stack-nya.
   * Kondisi terkini (apa yang sudah selesai & apa yang belum).
   * Tugas yang akan dikerjakan sekarang.
   * Area **Stabil** yang tidak akan disentuh.

### Fase B: Saat Mengerjakan Tugas
1. **Tugas Kecil (*Bugfix* / *Typo* / Penyesuaian Minor):** Kerjakan kode & verifikasi, lalu catat ringkasannya di `Log Sesi` pada dokumen memori.
2. **Tugas Fitur / Perubahan Arsitektur / Menyentuh Area Stabil:**
   * Jangan merusak atau menghapus perilaku eksisting yang sudah stabil (gunakan pola aditif/kondisional jika berbagi basecode).
   * Jalankan test/typecheck yang relevan sebelum menutup pekerjaan.
   * Perbarui dokumen arsitektur jika ada perubahan alur, skema, port, service, atau deployment.
   * Tambahkan entri ADR di dokumen memori jika mengambil keputusan teknis penting.

### Fase C: Menutup Sesi (Serah Terima ke Agen Berikutnya)
Sebelum menyatakan tugas selesai, AI Coding Agent wajib:
1. Memperbarui bagian **Sudah Selesai**, **Sedang Dikerjakan**, **Belum Dikerjakan**, dan menambah entri terbaru di **Log Sesi** pada dokumen memori (`MEMORY.md`/`memori.md`).
2. Memperbarui dokumen arsitektur (`ARCHITECTURE.md`/`arsitektur.md`) jika struktur/topologi berubah.
3. Melakukan `git diff --check` dan memastikan dokumen `.md` ter-commit rapi bersama perubahan kode.
4. Menulis **Blok Serah Terima Akhir**:

```markdown
## Ringkasan Serah Terima Sesi
- **Dikerjakan:** <ringkasan perubahan>
- **Bukti:** <path file / hasil test / commit>
- **Status:** <klaim — status — bukti>
- **Dokumen .md Diperbarui:** <daftar file .md>
- **Belum Selesai / Langkah Berikutnya:** <tugas spesifik berikutnya agar agen selanjutnya langsung lanjut>
```

---

## 6. Aturan Atribusi

* Semua commit, PR, changelog, dan dokumentasi publik diatribusikan sepenuhnya kepada pengembang manusia.
* AI Collaborator maupun AI Coding Agent **dilarang** mencantumkan dirinya sebagai `Co-authored-by`, contributor, atau author di metadata Git maupun dokumen publik.

---

## 7. Definition of Done (DoD)

Sebuah sesi kerja AI Coding Agent **belum dianggap selesai** sampai seluruh poin ini terpenuhi:
- [ ] Perubahan kode selesai dan lolos verifikasi (test / typecheck / cek runtime).
- [ ] Area **Stabil** tetap utuh atau diperbarui dengan alasan & test yang sah.
- [ ] Dokumen memori (`MEMORY.md` / `memori.md`) diperbarui sehingga agen berikutnya tahu persis apa yang sudah dan belum dikerjakan.
- [ ] Dokumen arsitektur (`ARCHITECTURE.md` / `arsitektur.md`) selaras dengan kondisi kode terbaru.
- [ ] Blok **Ringkasan Serah Terima Sesi** ditulis di akhir respons.