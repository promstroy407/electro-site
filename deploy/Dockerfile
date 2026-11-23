# Боевой образ сайта. Собирается на сервере из этого репозитория:
#   docker compose -f deploy/compose.yaml build
# Три ступени: зависимости PHP → сборка фронтенда → итоговые образы
# app (php-fpm, он же воркер очереди) и web (Caddy со статикой и HTTPS).

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist \
        --no-interaction --no-progress --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

FROM php:8.4-fpm-bookworm AS app
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev \
        libfreetype6-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql intl zip bcmath gd exif pcntl opcache \
    # pecl.php.net иногда отдаёт битый архив («unable to unpack») — до 3 попыток.
    && for attempt in 1 2 3; do \
         pecl install redis && break; \
         [ "$attempt" = 3 ] && exit 1; \
         rm -rf /tmp/pear; sleep 10; \
       done \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY deploy/php.ini "$PHP_INI_DIR/conf.d/zz-site.ini"

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# То, что локально делают скрипты composer: список пакетов, ассеты
# Filament и ссылка public/storage → storage/app/public (файлы объектов).
RUN php artisan package:discover --ansi \
    && php artisan filament:assets \
    && php artisan storage:link \
    && chown -R www-data:www-data public bootstrap/cache storage \
    && chmod +x deploy/start-app.sh

USER www-data
CMD ["deploy/start-app.sh"]

FROM caddy:2-alpine AS web
COPY deploy/Caddyfile /etc/caddy/Caddyfile
COPY --from=app /var/www/html/public /var/www/html/public
