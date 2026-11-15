# TECHNICAL ARCHITECTURE: PARTAI
- Next.js (App Router) untuk Frontend.
- Laravel 11 API untuk Backend.
- SQLite dengan PRAGMA (WAL, NORMAL, busy_timeout=5000, foreign_keys=ON) di AppServiceProvider.
- Idempotensi via request_id di endpoint POST.
