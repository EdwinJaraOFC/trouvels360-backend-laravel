FROM php:8.2-apache

# Dependencias del sistema y extensiones PHP

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
  && docker-php-ext-configure zip \
  && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
  && rm -rf /var/lib/apt/lists/*

# Instalar Composer (desde imagen oficial ligera)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite (URLs limpias Laravel)
RUN a2enmod rewrite

# Configurar DocumentRoot para que apunte a /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

#### Dependencias Composer (aprovecha cache si solo cambia código)

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts \
    && rm -rf /root/.composer/cache

COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Variables útiles para runtime (no secretas)
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=10000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=128 \
    PHP_OPCACHE_INTERNED_STRINGS_BUFFER=16

# Exponer puerto HTTP
EXPOSE 80

CMD ["apache2-foreground"]