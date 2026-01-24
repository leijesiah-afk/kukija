#!/bin/sh
set -e

PORT_VALUE="${PORT:-10000}"

sed -i "s/Listen 80/Listen ${PORT_VALUE}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT_VALUE}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

php artisan storage:link || true
php artisan migrate --force || true

exec "$@"
