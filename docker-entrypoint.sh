#!/bin/bash
# ============================================================
# docker-entrypoint.sh — Inicialización del contenedor Laravel
# ============================================================

set -e

echo "==> Iniciando FamilyIntegral API..."

# Esperar a que la base de datos esté lista
echo "==> Esperando conexión con MySQL..."
until php artisan migrate:status &>/dev/null; do
  echo "    MySQL no disponible aún, reintentando en 3s..."
  sleep 3
done

# Ejecutar migraciones
echo "==> Ejecutando migraciones..."
php artisan migrate --force

# Limpiar caché de configuración
echo "==> Optimizando la aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajustar permisos finales
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "==> Aplicación lista en el puerto 80"

# Iniciar Apache
exec apache2-foreground