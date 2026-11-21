#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs

if [ -z "$APP_KEY" ]; then
    [ -f storage/app_key ] || php artisan key:generate --show --no-ansi > storage/app_key
    export APP_KEY="$(cat storage/app_key)"
fi

php artisan migrate --force

[ -f storage/oauth-private.key ] || php artisan passport:keys --no-interaction
if [ ! -f storage/.personal_client ]; then
    php artisan passport:client --personal --name=Personal --provider=users --no-interaction
    touch storage/.personal_client
fi

if [ "$SEED" = "true" ] && [ ! -f storage/.seeded ]; then
    php artisan db:seed --force
    touch storage/.seeded
fi

chown -R www-data:www-data storage bootstrap/cache
exec "$@"
