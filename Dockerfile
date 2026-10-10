# Manager Library - API
# Imagen: PHP 8.3-FPM + nginx (backend Laravel 11)
FROM php:8.3-fpm

# Extensiones PHP que Laravel/PostgreSQL necesitan + nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        libonig-dev \
        libpq-dev \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        bcmath \
        intl \
        zip \
        gd \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer (con su contenedor oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Código de la aplicación (vendor/.git/.env excluidos con .dockerignore)
COPY . .

# Dependencias de producción (sin --no-scripts; artisan ya existe para package:discover)
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader \
    && composer clear-cache

# Permisos de escritura de Laravel y quitamos el server por defecto de nginx
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && rm -f /etc/nginx/sites-enabled/default \
    && mkdir -p /var/www/html/storage/logs

# Configuración de nginx (sirve public/), entrypoint y php.ini del reto
COPY .docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY .docker/php.ini $PHP_INI_DIR/conf.d/99-mllib.ini
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]