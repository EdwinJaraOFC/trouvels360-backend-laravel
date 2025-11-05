#!/bin/bash

echo "🚀 Iniciando aplicación Laravel..."

# Función mejorada para verificar conexión a MySQL
wait_for_mysql() {
    echo "🔄 Esperando conexión a MySQL..."
    max_attempts=60  # Aumentamos a 60 intentos
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        # Verificamos tanto la conexión básica como que la base de datos esté lista
        if php -r "
            try {
                \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
                \$pdo->query('SELECT 1');
                echo 'SUCCESS';
                exit(0);
            } catch (Exception \$e) {
                echo 'FAILED: ' . \$e->getMessage();
                exit(1);
            }
        " > /dev/null 2>&1; then
            echo "✅ MySQL está disponible y listo"
            return 0
        fi
        
        echo "⏳ Intento $attempt/$max_attempts - MySQL no disponible, esperando..."
        sleep 3  # Aumentamos el tiempo de espera
        attempt=$((attempt + 1))
    done
    
    echo "❌ Error: No se pudo conectar a MySQL después de $max_attempts intentos"
    exit 1
}

# Esperar a MySQL
wait_for_mysql

# Generar clave de aplicación si no existe
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate --force
fi

# Limpiar cachés antes de las migraciones
echo "🧹 Limpiando cachés..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Verificar conexión a la base de datos antes de migrar
echo "🔍 Verificando conexión a la base de datos..."
php artisan migrate:status || {
    echo "❌ Error: No se puede conectar a la base de datos"
    exit 1
}

# Ejecutar migraciones
echo "📊 Ejecutando migraciones..."
php artisan migrate --force

# Verificar que las migraciones se ejecutaron correctamente
echo "✅ Verificando migraciones..."
if php artisan migrate:status | grep -q "Migration"; then
    echo "✅ Migraciones ejecutadas correctamente"
else
    echo "⚠️ Advertencia: Las migraciones podrían no haberse ejecutado completamente"
fi

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