#!/bin/bash

echo "🚀 Iniciando aplicación Laravel..."

# Función para verificar conexión a MySQL
wait_for_mysql() {
    echo "🔄 Esperando conexión a MySQL..."
    max_attempts=30
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if php artisan migrate:status > /dev/null 2>&1; then
            echo "✅ MySQL está disponible"
            return 0
        fi
        
        echo "⏳ Intento $attempt/$max_attempts - MySQL no disponible, esperando..."
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ Error: No se pudo conectar a MySQL después de $max_attempts intentos"
    exit 1
}

# Esperar a MySQL
wait_for_mysql

# Generar clave de aplicación si no existe
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate --force
fi

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Ejecutar migraciones
echo "📊 Ejecutando migraciones..."
php artisan migrate --force

# Optimizar para producción
echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar permisos
echo "🔒 Verificando permisos..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "🎉 Aplicación lista - Iniciando Apache..."

# Iniciar Apache
exec apache2-foreground