#!/usr/bin/env bash
set -euo pipefail

echo "[1/5] Build and start containers"
docker compose -f docker-compose.prod.yml up -d --build

echo "[2/5] Wait a few seconds for database"
sleep 8

echo "[3/5] Generate app key if missing"
docker compose -f docker-compose.prod.yml exec -T app php artisan key:generate --force || true

echo "[4/5] Run migrations and seeders"
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan db:seed --force

echo "[5/5] Optimize cache"
docker compose -f docker-compose.prod.yml exec -T app php artisan optimize

echo "Deploy completed."
