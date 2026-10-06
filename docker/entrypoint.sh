#!/bin/sh
# Safe, idempotent startup for the dev "app" container. Never runs migrate:fresh / db:wipe.
set -e

cd /var/www/html
RUN_AS="runuser -u www-data --"

if [ "$1" = "php-fpm" ]; then
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
             storage/logs storage/app/public bootstrap/cache vendor
    chown -R www-data:www-data storage bootstrap/cache vendor 2>/dev/null || true
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

    [ -f .env ] || { cp .env.example .env; chown www-data:www-data .env 2>/dev/null || true; echo "[entrypoint] created .env from .env.example"; }
    chmod 664 .env 2>/dev/null || true

    echo "[entrypoint] composer install"
    $RUN_AS composer install --no-interaction --prefer-dist

    if ! grep -q '^APP_KEY=base64:' .env; then
        echo "[entrypoint] generating APP_KEY"
        $RUN_AS php artisan key:generate --force
    fi

    echo "[entrypoint] migrate"
    $RUN_AS php artisan migrate --force

    # DatabaseSeeder creates non-unique rows (barcode, shifts), so only seed an empty database.
    USERS=$($RUN_AS php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n1 | tr -dc '0-9')
    if [ "${USERS:-0}" = "0" ]; then
        echo "[entrypoint] empty database -> db:seed"
        $RUN_AS php artisan db:seed --force
    else
        echo "[entrypoint] users exist ($USERS) -> skipping seed"
    fi

    [ -e public/storage ] || $RUN_AS php artisan storage:link
    $RUN_AS php artisan optimize:clear >/dev/null 2>&1 || true
fi

exec "$@"
