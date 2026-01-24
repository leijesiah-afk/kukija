#!/bin/sh
set -e

PORT_VALUE="${PORT:-10000}"

sed -i "s/Listen 80/Listen ${PORT_VALUE}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT_VALUE}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

mkdir -p \
  storage \
  storage/framework/cache \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/testing \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is missing. Generating a temporary key (set APP_KEY in Render env vars for production)."
  export APP_KEY="$(php artisan key:generate --show --no-interaction)"
fi

if [ -z "${DB_CONNECTION:-}" ]; then
  export DB_CONNECTION=sqlite
fi

if [ -z "${LOG_CHANNEL:-}" ]; then
  export LOG_CHANNEL=stderr
fi

if [ "${DB_CONNECTION}" = "sqlite" ]; then
  if [ ! -f database/database.sqlite ]; then
    mkdir -p database
    touch database/database.sqlite
    chown -R www-data:www-data database || true
  fi
fi

php artisan optimize:clear || true
php artisan package:discover --ansi || true

php artisan storage:link || true

if [ "${DB_CONNECTION}" = "mysql" ]; then
  echo "Checking MySQL connectivity..."
  php -r 'exit(@fsockopen(getenv("DB_HOST") ?: "", (int)(getenv("DB_PORT") ?: 3306)) ? 0 : 1);' \
    && php artisan migrate --force \
    || echo "Skipping migrations: MySQL not reachable or not configured."
else
  php artisan migrate --force || true
fi

if [ "${RUN_SEED:-}" = "true" ]; then
  php artisan db:seed --force || true
fi

exec "$@"
