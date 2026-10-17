#!/usr/bin/env bash
# Actualiza el servidor con la última versión de GitHub.
# Uso (desde la carpeta del proyecto):  bash scripts/deploy.sh
# Variables opcionales: PHP_BIN (por defecto "php"), WEB_USER (por defecto "nobody", usuario de OpenLiteSpeed)
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
WEB_USER="${WEB_USER:-nobody}"
WEB_GROUP="${WEB_GROUP:-nogroup}"
cd "$(dirname "$0")/.."

echo "→ Modo mantenimiento"
$PHP_BIN artisan down --retry=30 || true

echo "→ Descargando cambios"
git pull --ff-only

echo "→ Dependencias PHP"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "→ Migraciones"
$PHP_BIN artisan migrate --force

echo "→ Compilando estilos y scripts"
npm ci --no-audit --no-fund
npm run build

echo "→ Cachés de producción"
$PHP_BIN artisan storage:link 2>/dev/null || true
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "→ Permisos ($WEB_USER:$WEB_GROUP)"
chown -R "$WEB_USER:$WEB_GROUP" storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

$PHP_BIN artisan up
echo "✔ Despliegue terminado"
