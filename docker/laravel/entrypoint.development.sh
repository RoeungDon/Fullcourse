#!/bin/bash
set -e

cd /var/www/html

echo "==> Laravel entrypoint starting..."

# Host volume can hide image vendor/
if [ ! -f vendor/autoload.php ]; then
    echo "==> Running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

if [ ! -f .env ]; then
    echo "==> Copying .env.example -> .env"
    cp .env.example .env
fi

if ! grep -qE '^APP_KEY=base64:.+' .env 2>/dev/null; then
    echo "==> Generating APP_KEY..."
    php artisan key:generate --force
fi

DB_HOST="${DB_HOST:-mysql-service}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USERNAME:-my_database_user}"
DB_PASS="${DB_PASSWORD:-my_database_password}"

echo "==> Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
for i in $(seq 1 45); do
    if php -r "try { new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USER}', '${DB_PASS}'); exit(0);} catch (Throwable \$e) { exit(1);}" 2>/dev/null; then
        echo "==> MySQL is ready."
        break
    fi
    if [ "$i" -eq 45 ]; then
        echo "==> WARNING: MySQL not ready after 90s — starting anyway."
    else
        sleep 2
    fi
done

echo "==> Running migrations..."
php artisan migrate --force || echo "==> WARNING: migrate failed."

echo "==> Starting php artisan serve on 0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
