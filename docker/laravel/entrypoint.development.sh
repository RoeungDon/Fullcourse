#!/bin/bash
set -e

cd /var/www/html

# Ensure dependencies exist (useful when volume-mounting source over the image)
if [ -f composer.json ] && [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

# Storage / cache permissions for the bind-mounted app
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# Generate app key only if missing
if [ -f .env ] && ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force --no-interaction || true
fi

# Wait for MySQL (healthcheck can still race on first boot)
echo "Waiting for database..."
for i in $(seq 1 60); do
  if php artisan db:show --no-interaction >/dev/null 2>&1; then
    echo "Database is ready."
    break
  fi
  if [ "$i" -eq 60 ]; then
    echo "Database not ready after 60s — continuing anyway."
  fi
  sleep 1
done

php artisan migrate --force --no-interaction

# Ensure .env changes (e.g. Google OAuth) are picked up
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# HTTP server in background; queue worker stays in foreground (keeps container alive)
php artisan serve --host=0.0.0.0 --port=8000 &

exec php artisan queue:work --tries=3 --timeout=60 --sleep=1
