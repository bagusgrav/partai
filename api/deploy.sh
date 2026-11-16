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
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo systemctl reload php8.4-fpm

echo "✅ Deployment selesai!"
