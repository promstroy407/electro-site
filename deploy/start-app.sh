#!/usr/bin/env sh
# Старт контейнера app: кеши Laravel из переменных окружения, затем php-fpm.
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

exec php-fpm
