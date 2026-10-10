#!/bin/sh
set -e

# Comandos únicos (artisan, php, composer, sh, bash, nginx, php-fpm) sin levantar el servidor web.
case "$1" in
    artisan)
        shift
        exec php /var/www/html/artisan "$@"
        ;;
    php | composer | sh | bash | nginx | php-fpm)
        exec "$@"
        ;;
esac

# Arranque normal: migraciones + admin + servidores
php /var/www/html/artisan migrate --force

# Crear administrador inicial si no existe
php /var/www/html/artisan app:create-admin

# Arranque normal: php-fpm en segundo plano y nginx al frente
php-fpm -D
nginx -g 'daemon off;'