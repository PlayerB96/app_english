#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="docker-compose.prod.yml"
BRANCH="${DEPLOY_BRANCH:-main}"

cd "$(dirname "$0")/.."

if [[ ! -f .env ]]; then
    echo "Error: no existe .env en $(pwd). Copia .env.example y configura DB_HOST y APP_URL." >&2
    exit 1
fi

echo "==> Actualizando código (rama ${BRANCH})..."
git pull origin "${BRANCH}"

echo "==> Construyendo imagen..."
docker compose -f "${COMPOSE_FILE}" build app

echo "==> Reiniciando contenedor..."
docker compose -f "${COMPOSE_FILE}" up -d

echo "==> Ejecutando migraciones..."
docker compose -f "${COMPOSE_FILE}" exec -T app php artisan migrate --force

echo "==> Cacheando configuración..."
docker compose -f "${COMPOSE_FILE}" exec -T app php artisan config:cache

echo "==> Despliegue completado."
