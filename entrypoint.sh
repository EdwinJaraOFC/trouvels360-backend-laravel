#!/bin/sh
set -e

# Migraciones
php artisan migrate --force

# Cache de Laravel
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Arrancar Apache
apache2-foreground