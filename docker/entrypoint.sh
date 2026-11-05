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

if [ "${RUN_SEED:-false}" = "true" ]; then
  echo "Menjalankan seeder database..."
  php artisan db:seed --force
fi

php artisan config:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

a2dismod mpm_event mpm_worker >/dev/null 2>&1 || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod mpm_prefork >/dev/null 2>&1 || true

PORT="${PORT:-8080}"
sed -i '/^Listen /d' /etc/apache2/ports.conf
echo "Listen ${PORT}" >> /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
grep -q '^ServerName ' /etc/apache2/apache2.conf || echo 'ServerName localhost' >> /etc/apache2/apache2.conf

apache2ctl -t

exec apache2-foreground
