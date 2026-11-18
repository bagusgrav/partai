#!/usr/bin/env bash
set -euo pipefail

echo "🚀 Memulai deployment Partai API..."

cd /var/www/thortech/partai
git fetch origin
git reset --hard origin/main

cd api
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
if python3 scripts/fetch_gini_stunting.py --check-config; then
    if ! python3 scripts/fetch_gini_stunting.py; then
        echo "Warning: BPS sync incomplete; continuing deployment."
    fi
    if ! php artisan welfare:sync-economic-indicators --year=2024; then
        echo "Warning: IKK sync incomplete; continuing deployment."
    fi
else
    echo "⚠ BPS_API_KEY is not configured; skipping BPS indicator fetch and IKK sync."
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo systemctl reload php8.4-fpm

echo "✅ Deployment selesai!"
