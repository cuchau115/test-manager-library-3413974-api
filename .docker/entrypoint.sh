#!/bin/sh
set -e

# Comandos únicos (artisan, php, composición) sin levantar el servidor web.
case "$1" in
    artisan)
        shift
        exec php /var/www/html/artisan "$@"
        ;;
    php | composer | sh | bash | nginx | php-fpm)
        exec "$@"
        ;;
esac

# Arranque normal: php-fpm en segundo plano y nginx al frente.
php-fpm -D
nginx -g 'daemon off;'