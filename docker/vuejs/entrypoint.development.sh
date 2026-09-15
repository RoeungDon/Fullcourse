#!/bin/sh
set -e

cd /app

echo "==> Vue entrypoint starting..."

if [ ! -d node_modules/vite ]; then
    echo "==> Running npm install..."
    npm install
fi

echo "==> Starting Vite on 0.0.0.0:5173"
exec npm run dev -- --host=0.0.0.0 --port=5173
