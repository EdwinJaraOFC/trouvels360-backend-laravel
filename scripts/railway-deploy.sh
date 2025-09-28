#!/bin/bash

echo "Running deployment script..."

# Optimizar Composer autoloader
composer install --optimize-autoloader --no-dev

# Limpiar caché de configuración
php artisan config:clear
php artisan cache:clear

# Ejecutar migraciones
php artisan migrate --force

# Cachear configuración para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment completed!"