#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

APP_KEY_VALUE=""
if [ -f .env ]; then
  APP_KEY_VALUE=$(grep '^APP_KEY=' .env | head -n 1 | cut -d= -f2- || true)
fi

if [ -z "$APP_KEY_VALUE" ]; then
  php artisan key:generate --no-interaction
fi

if [ -f .env ] && grep -q '^DB_CONNECTION=sqlite' .env; then
  if [ ! -f database/database.sqlite ]; then
    mkdir -p database
    touch database/database.sqlite
  fi
fi

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
  echo "Waiting for MySQL to be reachable..."
  i=0
  until php -r 'exit(@fsockopen(getenv("DB_HOST") ?: "db", (int)(getenv("DB_PORT") ?: 3306)) ? 0 : 1);'; do
    i=$((i+1))
    if [ "$i" -ge 60 ]; then
      echo "MySQL not reachable after 60 attempts, continuing anyway."
      break
    fi
    sleep 2
  done
fi

i=0
until php artisan migrate --force; do
  i=$((i+1))
  if [ "$i" -ge 10 ]; then
    echo "Migrations failed after 10 attempts, continuing anyway."
    break
  fi
  sleep 3
done

chmod -R ug+rwx storage bootstrap/cache || true

exec "$@"
