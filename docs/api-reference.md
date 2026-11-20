# Referensi API PARTAI

Dokumen ini mencatat seluruh rute yang didefinisikan di `api/routes/api.php` dan parameter yang divalidasi atau dibaca controller. Semua path memakai prefix `/api` dan middleware grup Laravel `api`; base URL produksi `https://api.akuhadir.my.id`. Body dan respons menggunakan JSON kecuali dinyatakan lain. Label akses/throttle di bawah merujuk pada middleware route tambahan yang dinyatakan di route file; batas throttle adalah jumlah request per menit.

> **Penting:** ini adalah gambaran implementasi aktual, bukan jaminan bahwa seluruh standar target sudah terpenuhi. Rute autentikasi SSO belum tampak di daftar route; endpoint autentikasi yang tersedia saat ini meliputi guest, register, login, verifikasi email, logout, dan pemulihan kata sandi.

## Autentikasi dan format umum

- Rute bertanda **Bearer** membutuhkan `Authorization: Bearer <sanctum-token>`.
- Token dapat diperoleh dari `/api/auth/guest`, `/api/auth/register`, atau `/api/auth/login`. Tidak satu pun merupakan endpoint SSO.
- Rute **Publik** tidak memasang middleware autentikasi pada definisi API. Endpoint `/api/user` membutuhkan Bearer token.
- Untuk request JSON kirim `Accept: application/json` dan `Content-Type: application/json`.
- Operasi tulis menerima `request_id` UUID dan memiliki rate limit sesuai middleware yang tercantum; pengecualian adalah `POST /api/auth/logout`, yang hanya menghapus token saat ini. Perilaku retry berbeda antar-controller; jangan mengasumsikan semua operasi idempoten.
- Kesalahan validasi umumnya `422`, belum terautentikasi `401`, tidak berwenang `403`, tidak ditemukan `404`, konflik/state/idempotensi `409`, dan rate limit `429`. Detail error dapat bervariasi per controller.

## Sesi dan profil

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `POST /api/auth/guest` | Publik; throttle 300/menit | JSON: `request_id` (UUID) | `201 {"token":"..."}`. Mengulang ID mempertahankan akun tamu; endpoint mengeluarkan bearer token Sanctum. |
| `POST /api/auth/register` | Publik; throttle 10/menit | JSON: `request_id` (UUID), `name`, `email`, `password` (min. 8 karakter), `cluster_id`; `nik` opsional | `201 {"token":"..."}` untuk pendaftaran baru; `{"token":"..."}` untuk retry identik; `409 {"message":"..."}` bila ID yang sama dikirim dengan data berbeda. |
| `POST /api/auth/login` | Publik; throttle 10/menit | JSON: `email`, `password` | `{"token":"..."}` untuk akun non-tamu dengan kredensial valid; kredensial salah atau akun tamu menghasilkan `422`. |
| `GET /api/auth/verify-email/{id}/{hash}` | Publik; `signed`; throttle 10/menit | Path: `id` ID user, `hash` SHA-1 email; URL harus memiliki signature dan masa berlaku yang valid | `{"message":"Alamat email berhasil diverifikasi."}`; hash tidak cocok `403`, user tidak ditemukan `404`. |
| `POST /api/auth/forgot-password` | Publik; `IdempotentPasswordRequest`; `throttle:5,1` | JSON: `request_id` (UUID), `email` | Respons sukses generik yang sama untuk alamat terdaftar/tidak. Retry dengan ID dan payload sama mengembalikan respons tersimpan tanpa mengirim notifikasi kedua; payload berbeda menghasilkan `409`. |
| `POST /api/auth/reset-password` | Publik; `IdempotentPasswordRequest`; `throttle:5,1` | JSON: `request_id` (UUID), `token`, `email`, `password`, `password_confirmation` | `{"message":"Kata sandi berhasil diubah."}`; retry identik mengembalikan respons yang sama; ID sama dengan payload berbeda menghasilkan `409`. |
| `POST /api/auth/logout` | Bearer (`auth:sanctum`); tanpa throttle tambahan | — | Menghapus token akses saat ini dan mengembalikan pesan sukses. |
| `GET /api/user` | Bearer | — | `id`, `name`, `email`, `cluster_id`, `cluster_name`, `role`, `is_verified`, `reputation_points`, `reputation_level`. |
| `GET /api/reputation` | Bearer | — | `reputation_points`, `reputation_level`, dan hingga 10 `logs` terbaru. |
| `GET /api/reputation/me` | Bearer | Query opsional `page` (pagination) | Skor mastery lima domain, `overall_mastery_level`, `participation_score`, `eligible_roles`, dan hingga 50 entri ledger per halaman. |

Tidak ada route SSO atau route exchange SSO dalam route API saat ini. Login email/kata sandi tersedia melalui `/api/auth/login`.

## Data publik dan layanan “Jemput Hak”

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/welfare/summary` | Publik | — | Ringkasan sesuai `WelfareService`. |
| `GET /api/welfare/regions` | Publik | — | Daftar wilayah/data sesuai `WelfareService`. |
| `GET /api/welfare/region/{code}` | Publik | Path: `code` kode wilayah | Satu objek wilayah; `404 {"message":"Data wilayah tidak ditemukan."}` bila tidak ada. |
| `GET /api/economic/summary` | Publik | — | `{"indicators":[...],"last_updated":"..."}`; satu rekaman terbaru per tipe indikator. |
| `GET /api/economic/history/{type}` | Publik | Path: `type` jenis indikator | `{"type":"...","data":[...]}`; paling banyak 90 rekaman. |
| `GET /api/economy/indicators` | Publik | Query opsional: `type` (`inflasi`, `kemiskinan`, `tpt`, `gini`; default `inflasi`), `region_code` (empat digit; default `9999`) | Data indikator sesuai jenis dan wilayah serta rangkuman indikator nasional dan `last_updated`. |
| `GET /api/economy/regions` | Publik | — | `{"regions":[...]}` daftar provinsi dan wilayah anak yang tersedia pada observasi indikator ekonomi. |
| `GET /api/insights/news` | Publik | — | `{"insights":[...]}` berisi hingga 10 wawasan kategori berita terbaru. |
| `GET /api/insights/cabinet` | Publik | — | `{"insights":[...]}` kategori kabinet. |
| `GET /api/rights/check` | `auth:sanctum`; `throttle:5,1` | Tidak ada parameter NIK; NIK diambil dari user terautentikasi dan harus memiliki cap verifikasi admin (`nik_verified_at` tidak `NULL`) | `{"program":"PKH\|BPNT\|PIP","status":"DITERIMA\|TIDAK_DITERIMA\|PROSES","period":"..."}`. Respons memakai `Cache-Control: no-store, private`; NIK akun kosong atau belum memiliki cap verifikasi mendapat `403` `{"message":"NIK belum terverifikasi.","code":"nik_not_verified"}`; NIK akun dengan format salah mendapat `422`; NIK tidak terdaftar `404`. |
| `GET /api/contracts` | Publik | Query opsional: `status` salah satu `BELUM_DIMULAI`, `SEDANG_BERJALAN`, `TERPENUHI`, `GAGAL` | Array kontrak berisi antara lain `id`, `politician_name`, `title`, `description`, `status`, `evidence_url`, `deadline`. |
| `GET /api/faqs` | Publik | — | FAQ yang berstatus publik saja. |
| `GET /api/glossary` | Publik | Query opsional: `search` (maksimal 100 karakter; mencari istilah, definisi, dan kategori) | Daftar istilah glosarium berisi `id`, `term`, `definition`, dan `category`. |
| `POST /api/inquiries` | Publik; throttle 5/menit | JSON: `request_id` (UUID), `question` (maksimal 5.000 karakter); `name` dan `email` opsional | `201` untuk pertanyaan baru, `200` untuk retry identik, `409` bila ID sama dipakai dengan data berbeda; input tidak valid menghasilkan `422`. |

Data “Jemput Hak” mengikuti dataset yang tersedia dan tidak menggantikan keputusan resmi penyaluran bantuan sosial.

## Komunitas

| Method & endpoint | Akses dan middleware | Parameter | Respons dan catatan |
| --- | --- | --- | --- |
| `GET /api/posts` | Publik; `throttle:60,1` | Query opsional: `cursor`, `per_page` (1–50; default 20) | Feed cursor-based berisi post berstatus `published` dengan visibilitas `public` saja; cache singkat `public, max-age=30`. |
| `GET /api/posts/members` | Bearer; `auth:sanctum`, `throttle:60,1` | Query opsional: `cursor`, `per_page` (1–50; default 20) | Feed cursor-based berisi post berstatus `published` dengan visibilitas `members` saja; `Cache-Control: no-store, private`, `X-Robots-Tag: noindex, nofollow`. |
| `POST /api/posts` | Bearer non-guest; `auth:sanctum`, `throttle:10,1` | JSON wajib: `request_id` (string), `body` (1–500 karakter); `visibility` opsional (`public\|members`, default `members`) | `201` untuk post baru; guest ditolak `403`; ID dan payload yang sama replay `200 already_processed`; ID yang sama dengan pemilik atau payload berbeda menghasilkan `409`. |
| `PATCH /api/posts/{post}` | Bearer; `auth:sanctum`, `throttle:10,1`; pemilik post | Path `post` UUID; `body` dan/atau `visibility` (`public\|members`) | Memperbarui teks/visibilitas dan mengisi `edited_at`; hanya pemilik, selain itu `403`. |
| `DELETE /api/posts/{post}` | Bearer; `auth:sanctum`, `throttle:10,1`; pemilik atau role `super_admin\|pengurus` | Path `post` UUID | Soft delete dan status internal `deleted`; respons publik tidak memuat status internal. |
| `POST /api/posts/{post}/report` | Bearer; `auth:sanctum`, `throttle:5,1` | Path `post` UUID; JSON `reason` wajib (1–1.000 karakter) | Membuat laporan berstatus `open`; `201`. |

`PostResource` hanya menampilkan ID post, teks, visibilitas, waktu pembuatan/perubahan, dan nama penulis; tidak menampilkan ID user, `request_id`, status moderasi, atau log. Konten post anggota tidak disediakan melalui endpoint publik dan tidak masuk metadata/preview OG publik. Moderasi post dan pemeriksaan laporan dilakukan melalui Filament bagi role `super_admin` atau `pengurus`, dengan audit trail pada `post_moderation_logs`.

## Organisasi

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/organization/tree` | Publik; throttle 60/menit | Query opsional: `layer`, `status`, `territory_level`, `search` | Struktur pohon dan daftar kursi organisasi yang sesuai filter. |
| `GET /api/organization/eligibility` | Bearer (`auth:sanctum`) | — | Kursi organisasi beserta kelayakan dan syarat anggota yang sedang login. |
| `POST /api/organization/seats/{seat}/apply` | Bearer (`auth:sanctum`); throttle 10/menit | Path: `seat` ID kursi; JSON: `request_id` (UUID), `statement` (20–5.000 karakter) | `201` aplikasi baru atau `200` retry identik; `409` jika `request_id` dipakai untuk pernyataan berbeda. |

## Keuangan partai

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/finance/summary` | Publik; throttle 60/menit | — | `{"summary":...}` ringkasan keuangan publik. |
| `GET /api/finance/ledger` | Publik; throttle 60/menit | Query opsional: `entry_type`, `channel`, `donor_type`, `status`, `year`, `search`, `per_page` (default 20, maksimum 100), `page` | `{"ledger":...}` data ledger terpaginasikan; identitas ditampilkan dalam bentuk yang dimasking. |
| `POST /api/finance/donations` | Publik; throttle 10/menit | JSON wajib: `request_id` (UUID), `donor_type` (`individual\|corporate`), `contributor_name`, `identity_number`, `amount_idr` (min. 10.000), `transaction_date` (tidak di masa depan), `evidence_reference`, `legal_declaration_accepted` (harus accepted); `purpose_or_program` opsional (maks. 220 karakter). Identitas individu harus NIK 16 digit; korporasi NPWP/NIB 15–16 digit. | Mencatat donasi berstatus `pending_audit`; respons memuat `created` dan identitas tersamarkan. |
| `GET /api/finance/my-dues` | Bearer (`auth:sanctum`) | — | Iuran anggota tahun berjalan, akumulasi, sisa dan batas tahunan. |
| `POST /api/finance/my-dues` | Bearer (`auth:sanctum`); throttle 10/menit | JSON: `request_id` (UUID), `amount_idr` (min. 10.000), `transaction_date` (tidak di masa depan), `evidence_reference`, `legal_declaration_accepted` (harus accepted) | Mencatat iuran anggota berstatus `pending_audit`; respons menunjukkan apakah entri baru dibuat. |

## Pemantauan birokrasi

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/bureaucracy/scorecards` | Publik; throttle 60/menit | Query opsional: `category`, `tier` (`sangat_baik\|baik\|perlu_perbaikan\|kritis`), `province`, `year` (1900–2100), `search` (maks. 100 karakter), `sort` (`score_desc\|score_asc\|complaints_desc`), `page` | Ringkasan dan scorecard instansi terpaginasikan (20 per halaman). |
| `GET /api/bureaucracy/reports` | Publik; throttle 60/menit | Query opsional: `category` (`maladministrasi\|penyalahgunaan_fasilitas\|pungli_layanan\|penundaan_berlarut\|diskriminasi_layanan`), `province` (maks. 255 karakter), `status` (`verified\|forwarded_to_ombudsman\|resolved`), `page` | Laporan yang sudah diverifikasi/diteruskan/diselesaikan, terpaginasikan (20 per halaman). |
| `POST /api/bureaucracy/reports` | Publik; throttle 5/menit | JSON: `request_id` (UUID), `agency_name`, `category` (nilai seperti di atas), `title` (maks. 180 karakter), `chronology` (min. 30 karakter), `incident_date` (tidak di masa depan); opsional `agency_id` (UUID), `province_name`, `region_code`, `asset_identifier`, `evidence_url` (URL). | `201` memberi `ticket_code` dan `reporter_secret`; retry identik `already_processed`, ID sama dengan data berbeda `409`. |
| `GET /api/bureaucracy/reports/track/{ticketCode}` | Publik; throttle 15/menit | Path: `ticketCode`; query opsional: `secret` (`reporter_secret` dari pembuatan laporan) | Status tiket; kronologi ditampilkan untuk laporan publik yang terverifikasi atau jika secret pelapor cocok. Respons memakai `Cache-Control: no-store`. |

## Pendidikan dan reputasi

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/education/overview` | Publik; `throttle:60,1` | — | Kurikulum lima domain, distribusi soal terpublikasi menurut domain/level, `daily_digest` (sorotan konstitusional dan konteks kontrak/agendas), serta hingga tiga `preview_questions` tanpa kunci jawaban. |
| `GET /api/education/session` | Bearer anggota (`role=member`) | — | Maksimal lima soal: ulasan `next_review_at` yang jatuh tempo terlebih dahulu, lalu soal terpublikasi yang belum pernah dijawab sesuai tingkat penguasaan; mengembalikan `mastery_scores` dan `overall_mastery_level`. |
| `POST /api/education/answers` | `auth:sanctum`; `throttle:30,1` | JSON: `request_id` (UUID), `question_id` (UUID), `selected_option` (`A\|B\|C\|D`), `confidence` (`ragu\|yakin`, opsional) | `correct`, kunci dan penjelasan setelah menjawab, sitasi/sumber primer, `mastery_awarded`, `points_delta`, `next_review_at`, dan `updated_reputation`. Retry identik mengembalikan respons yang sama; ID sama dengan payload berbeda menghasilkan `409`. Tamu dapat berlatih tanpa menyimpan jadwal atau skor; poin hanya diberikan satu kali per soal kepada anggota. |
| `GET /api/review` | Bearer | Query opsional: `limit` integer 1–20 (default 5) | `{"items":[...]}` soal yang jatuh tempo; payload soal tidak memuat jawaban/pembahasan. |
| `POST /api/answer` | `auth:sanctum`; `throttle:30,1` | JSON: `item_id`, `selected_option` (integer mulai 0), `request_id` (UUID) | `{"status":"processed","data":{"next_review_at":"...","correct":true,"correct_option":0,"explanation":"..."}}`; retry identik berstatus `already_processed`. |
| `GET /api/essay/question` | Bearer | — | `{"item":{...}}` atau `{"item":null}`; kunci/pembahasan tidak dikirim di payload soal. |
| `POST /api/essay/submit` | `auth:sanctum`; `throttle:10,1` | JSON: `item_id`, `answer_text` (maks. 20.000 karakter), `request_id` (UUID) | `201 {"status":"processed","data":{"submission_id":123,"explanation":"..."}}`; retry identik berstatus `already_processed`. |
| `GET /api/essay/review` | Bearer | — | `{"submission":{...}}` esai pending milik anggota lain, atau `{"submission":null}`. |
| `POST /api/essay/review` | `auth:sanctum`; `throttle:20,1` | JSON: `submission_id`, `score` integer 0–100, `request_id` (UUID), `feedback` opsional maks. 10.000 karakter | `{"status":"processed","data":{"submission_id":123}}`; reviewer tidak boleh menilai esainya sendiri. Retry identik berstatus `already_processed`. |

Jawaban benar kuis dan esai yang memenuhi syarat tetap tercatat pada reputasi legacy (`/api/reputation`). Skor legacy tersebut tidak menjadi sumber atau pengali bagi `mastery_score` dan `participation_score` Fase 2.

## Reputasi Fase 2

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/reputation/leaderboard-stats` | Publik | — | `members_count`, distribusi jumlah anggota per `mastery level` 1–4, serta agregat poin `lapangan`, `non_fisik`, dan `total`; tidak menyertakan identitas atau skor individu. |
| `GET /api/reputation/me` | Bearer | Query opsional `page` | Profil skor anggota sendiri: lima `mastery_scores`, `overall_mastery_level`, `participation_score`, `eligible_roles`, dan pagination `ledger` berisi hingga 50 entri per halaman. |
| `POST /api/reputation/contributions` | `auth:sanctum`; `role:super_admin\|pengurus\|panelis`; `throttle:10,1` | JSON: `request_id` (UUID), `user_id` (UUID), `category` (`lapangan`, `riset`, `bantuan_hukum`, `logistik`, `editorial`), `source_type`, `source_id`, `reason` | Setiap kontribusi terverifikasi menambah satu poin dengan bobot setara. `201` untuk entri baru, `200` untuk retry identik, `409` bila ID sama dipakai untuk data berbeda. |

Mastery dicatat per domain (`hak_konstitusional`, `alasan_bernegara`, `keuangan_negara`, `hukum`, `politik_bernegara`), dibatasi 0–100, lalu dirata-ratakan untuk tingkat 1–4. Tingkat 4 dimulai dari rata-rata domain 80 dan menjadi syarat absolut bagi Panel Audit, Panel Etik, dan Bakal Caleg; hasil tidak menentukan hak suara. Participation adalah akumulasi ledger yang terpisah. Koreksi internal melalui Filament juga membuat entri ledger baru, bukan mengubah riwayat.

## Kegiatan dan kehadiran

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `POST /api/events` | `auth:sanctum`; `throttle:10,1` | JSON: `title`, `description`, `location_lat` (-90..90), `location_lng` (-180..180), `starts_at`, `ends_at` setelah waktu mulai, `request_id` (UUID), `radius_m` integer opsional (default 100), `region_code` (opsional, empat digit), `province_name` (opsional) | `201 {"status":"processed","event":{"id":"..."}}`; retry identik berstatus `already_processed`. |
| `GET /api/events/active` | Bearer | — | `{"events":[...]}` kegiatan aktif yang sedang berlangsung. |
| `GET /api/events/{id}/qr` | Bearer; hanya koordinator acara | Path: `id` UUID kegiatan | Token QR dinamis dalam respons JSON; kegiatan tidak ada `404`, bukan koordinator `403`, bukan aktif `409`. |
| `POST /api/attendance/claim` | `auth:sanctum`; `throttle:20,1` | JSON: `event_id`, token QR, `lat`, `lng`, `request_id` (UUID) | `201` berisi `status`, `claim_id`, `distance_m` (dan `radius_m` jika ditolak karena jarak). Klaim di luar radius berstatus `rejected`; retry identik aman. |
| `GET /api/attendance/pending` | Bearer | Query: `event_id` UUID | `{"claims":[...]}` klaim menunggu atestasi, selain milik pemanggil. |
| `POST /api/attendance/attest` | `auth:sanctum`; `throttle:20,1` | JSON: `claim_id` UUID, `request_id` UUID | `201 {"status":"processed","claim_status":"pending\|valid","attestations":N}`; retry identik aman. Validator tidak dapat memvalidasi klaim sendiri dan harus berasal dari cluster berbeda. |

## Notifikasi

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/notifications` | Bearer | — | `{"notifications":[...],"unread_count":N}`; paling banyak 50 notifikasi terbaru milik pemanggil. |
| `POST /api/notifications/{id}/read` | `auth:sanctum`; `throttle:30,1`; hanya pemilik notifikasi | Path: `id` ID notifikasi; JSON: `request_id` (UUID) | `{"status":"read","id":"...","read_at":"..."}`; pengulangan setelah dibaca menghasilkan `{"status":"already_processed",...}` tanpa mengubah `read_at`. |

## Voting, recall, dan sortisi

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/votes/active` | `auth:sanctum` (Bearer) | — | `{"can_vote":true,"votes":[{"id":1,"title":"...","description":"...","starts_at":"...","ends_at":"...","has_voted":false,"vote_options":[{"id":1,"option_text":"..."}]}]}`. `can_vote` hanya `true` untuk `role=member` dan `is_verified=true`. Jika pemanggil sudah memilih, `has_voted` bernilai `true` dan opsi memuat `votes_count`; tally tidak dikirim sebelum pemanggil memberikan suara. |
| `POST /api/votes/submit` | `auth:sanctum`; anggota terverifikasi; `throttle:10,1` | JSON: `vote_id` integer, `vote_option_id` integer milik voting itu, `request_id` UUID | `201 {"status":"processed","data":{"submission_id":123}}`; retry identik `already_processed`; akun selain anggota terverifikasi menerima `403`; satu suara per anggota per voting. |
| `POST /api/recall/request` | `auth:sanctum`; `throttle:5,1`; anggota terverifikasi | JSON: `target_user_id` UUID anggota terverifikasi, `reason` maks. 10.000 karakter, `request_id` UUID; `evidence_url` HTTP(S) opsional | `201 {"status":"processed","recall":{"id":"...","target_user_id":"...","reason":"...","evidence_url":"...","status":"voting"}}`; retry identik aman. |
| `GET /api/recall/active` | Bearer | — | `{"recalls":[...]}` recall yang sedang voting beserta `agree_count`, `reject_count`, `active_member_count`, `votes_required`, dan `has_voted`. |
| `POST /api/recall/vote` | `auth:sanctum`; `throttle:10,1`; anggota terverifikasi | JSON: `recall_request_id` UUID, `vote` boolean, `request_id` UUID | `201 {"status":"processed","data":{"vote_id":"...","recall_status":"voting\|approved","agree_count":N,"votes_required":N}}`; retry identik `already_processed`. |
| `GET /api/sortition/panels` | Bearer | — | `{"panels":[{"id":"...","name":"...","mastery":N}]}` anggota terverifikasi dengan `overall_mastery_level >= 4`; pengundian saat ini acak tanpa seed publik. |

## Kontrak Faksi

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/contracts` | Publik | Query opsional `status` | Array objek kontrak; dapat difilter menurut status. |
| `POST /api/contracts` | `auth:sanctum`; `role:super_admin\|pengurus`; `throttle:20,1` | JSON: `politician_name`, `title`, `description`, `request_id` (UUID); opsional `politician_id` UUID, `status`, `evidence_url`, `deadline` (`YYYY-MM-DD`) | `201` satu objek kontrak; retry identik menghasilkan HTTP `200 {"status":"already_processed","data":{...}}`. |
| `PATCH /api/contracts/{contract}` | `auth:sanctum`; `role:super_admin\|pengurus`; `throttle:20,1` | Path `contract` UUID; JSON: `request_id` (UUID) dan subset field kontrak | Objek kontrak yang diperbarui; retry identik menghasilkan HTTP `200 {"status":"already_processed","data":{...}}` tanpa menerapkan perubahan ulang. ID yang sama dengan operasi/payload berbeda menghasilkan `409`. |

Status yang sah: `BELUM_DIMULAI`, `SEDANG_BERJALAN`, `TERPENUHI`, dan `GAGAL`.

## Indeks Kerentanan Kesejahteraan (IKK)

| Method & endpoint | Akses | Parameter | Respons utama |
| --- | --- | --- | --- |
| `GET /api/welfare/ikk` | Publik; `throttle:60,1` | Query opsional `province`, `tier`, `year`, `search`, `sort` (`ikk_desc`, `ikk_asc`, `poverty_desc`, `disaster_desc`), `page` | Ringkasan nasional tahun terpilih (rata-rata IKK, distribusi tier, bobot/rentang normalisasi), daftar wilayah berhalaman, dan daftar provinsi. |
| `GET /api/welfare/ikk/{regionCode}` | Publik; `throttle:60,1` | Kode BPS kabupaten/kota empat digit | Profil indikator, lima sub-skor 0–100, sitasi BPS/BNPB, kontrak aktif dan agenda aktif yang ditandai pada kabupaten/kota atau provinsi tersebut. |
| `POST /api/welfare/ikk/ingest` | `auth:sanctum`; `role:super_admin\|pengurus`; `throttle:20,1` | JSON: `request_id` UUID dan `rows` berisi indikator lengkap beserta sitasi BPS/BNPB | `201 {"status":"processed","records_saved":N}`; retry identik menghasilkan `already_processed`; pemakaian ID untuk batch berbeda menghasilkan `409`. |

Metodologi komposit yang dipakai deterministik: kemiskinan 30% (acuan 0–35%), stunting 25% (0–45%), Gini 15% (0,250–0,550), TPT 15% (0–15%), dan risiko bencana BNPB 15% (0–100). Nilai normalisasi dibatasi pada 0–100. Tier: rendah `<30`, sedang `30–49,99`, tinggi `50–69,99`, kritis `>=70`. Data seeder offline adalah estimasi kurasi untuk CI dan fallback (bukan hasil tarik API langsung) dengan rujukan publikasi yang perlu dicocokkan ke tabel sumber sebelum dipakai mengambil keputusan. Command `welfare:sync-open-data` memakai baseline ini jika endpoint BPS/BNPB tidak dikonfigurasi atau tidak tersedia. Endpoint dapat diatur melalui `BPS_IKK_DATA_URL`, `BNPB_CKAN_DATA_URL`, serta `BNPB_CKAN_RESOURCE_ID`; keduanya harus mengembalikan objek JSON dengan `records`/`data` berupa baris terstruktur yang menggabungkan kode/tahun dan indikator terkait (BNPB CKAN dapat memakai `result.records`).

Penandaan opsional `region_code` dan `province_name` pada event serta kontrak faksi menentukan keterkaitannya pada profil IKK; kedua field dapat diisi melalui API mutasi maupun panel admin. Resource Filament **Indeks Kerentanan (IKK)** dapat dibaca/audit oleh `panelis`; create/edit dan aksi sinkronisasi data terbuka dibatasi ke `super_admin` dan `pengurus`. Resource data lama `WelfareDataResource` tetap terpisah.

## Dukungan idempotensi dan kepatuhan

Standar Thortech v2.0 mewajibkan UUID `request_id` untuk operasi tulis baru yang aman dicoba ulang. Dukungan yang terlihat saat ini:

| Endpoint | Kondisi yang tercatat |
| --- | --- |
| `POST /api/answer`, `/api/essay/submit`, `/api/essay/review` | Memvalidasi UUID `request_id` dan mengembalikan hasil retry identik. |
| `POST /api/events`, `/api/attendance/claim`, `/api/attendance/attest` | Memvalidasi UUID `request_id`; payload berbeda dengan ID yang sudah dipakai ditolak. |
| `POST /api/votes/submit`, `/api/recall/request`, `/api/recall/vote` | Memvalidasi UUID `request_id` dan melindungi pengulangan operasi. |
| `POST /api/auth/guest`, `/api/auth/register` | Menggunakan UUID `request_id` untuk membuat sesi/pendaftaran secara idempoten; keduanya bukan SSO. |
| `POST /api/auth/forgot-password`, `/api/auth/reset-password` | Memvalidasi UUID `request_id`, menyimpan hash payload dan respons untuk replay identik, serta menolak pemakaian ulang ID dengan payload/endpoint berbeda. |
| `POST /api/contracts`, `PATCH /api/contracts/{contract}` | Memvalidasi UUID `request_id`, menyimpan hasil mutasi untuk replay, dan menolak ID yang dipakai ulang dengan payload/operasi/kontrak berbeda. |
| `POST /api/education/answers` | Memvalidasi UUID `request_id`, menyimpan hash payload dan respons; replay identik tidak mengubah jadwal atau ledger, sedangkan payload berbeda ditolak. |
| `POST /api/notifications/{id}/read` | Memvalidasi UUID `request_id`; perubahan `read_at` atomik dan replay saat sudah dibaca mengembalikan `already_processed`. |
| `POST /api/inquiries`, `/api/reputation/contributions` | Memvalidasi UUID `request_id`; retry identik aman dan payload berbeda ditolak dengan `409`. |

Untuk operasi yang sudah mendukung idempotensi, gunakan ID yang sama hanya untuk payload dan pengguna yang sama. Jangan menganggap metode HTTP atau helper retry sendiri sudah membuat operasi idempoten.

## Referensi implementasi

Daftar route kanonis berada di `api/routes/api.php`; response dan validasi berada di controller masing-masing di `api/app/Http/Controllers/Api/`. Perubahan implementasi wajib memperbarui tabel ini agar dokumentasi tidak tertinggal dari kontrak.
