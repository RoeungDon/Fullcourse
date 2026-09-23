#!/bin/bash
set -e

# Ensure dependencies exist (useful when volume-mounting source over the image)
if [ -f composer.json ] && [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

php artisan key:generate --force --no-interaction || true
php artisan migrate --force --no-interaction

# HTTP server in background; queue worker stays in foreground (keeps container alive)
php artisan serve --host=0.0.0.0 --port=8000 &

php artisan queue:work --tries=3 --timeout=60
