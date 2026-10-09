#!/bin/sh
set -e

cd /app

if [ ! -f index.html ]; then
  echo "ERROR: /app/index.html is missing. Vite cannot start without it."
  exit 1
fi

if [ ! -f package.json ]; then
  echo "ERROR: /app/package.json is missing."
  exit 1
fi

# Named volume can be empty on first boot; install deps if vite binary is missing
if [ ! -x node_modules/.bin/vite ]; then
  echo "Installing npm dependencies..."
  npm install
fi

echo "Starting Vite on 0.0.0.0:5173..."
exec npm run dev -- --host 0.0.0.0 --port 5173 --strictPort
