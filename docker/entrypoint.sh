#!/bin/sh
set -e

if [ -z "${APP_KEY:-}" ]; then
  echo "ERROR: APP_KEY belum diset. Generate dengan: php artisan key:generate --show"
  exit 1
fi

mkdir -p \
  bootstrap/cache \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs

chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "Menjalankan migrasi database..."
  php artisan migrate --force
fi

php artisan config:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
