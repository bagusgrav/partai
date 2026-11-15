# MEMORY & ARCHITECTURE INVARIANTS: PARTAI
**Status:** Mandatori & SSOT
- INVARIANT (Git): Dilarang trailer Co-authored-by.
- INVARIANT (Auth): Dilarang form login lokal di Next.js. Wajib via SSO id.akuhadir.my.id.
- INVARIANT (DB): SQLite WAL, synchronous=NORMAL, busy_timeout=5000.
- INVARIANT (DB): File di /var/data/partai/, dilarang di /var/www/.
- INVARIANT (API): Wajib idempoten menggunakan request_id (UUID).
- INVARIANT (Frontend): Wajib fetchWithRetry (2 retry, 1.5s).
