# syntax=docker/dockerfile:1.7
# Образ приложения Eurika: PHP 8.3-FPM + собранный фронтенд.
# Используется docker-compose.yml (локально) и подходит для продакшена.

# ---------- 1. Сборка фронтенда ----------
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------- 2. PHP-зависимости ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ---------- 3. Рабочий образ ----------
FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache icu-libs libzip libpng libjpeg-turbo freetype postgresql-libs \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev \
        libjpeg-turbo-dev freetype-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql intl zip gd bcmath opcache \
    && apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-eurika.ini

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=frontend /app/public/build ./public/build

RUN rm -f public/hot \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/eurika-entrypoint
RUN chmod +x /usr/local/bin/eurika-entrypoint

USER www-data
ENTRYPOINT ["eurika-entrypoint"]
CMD ["php-fpm"]

# ---------- 4. nginx со статикой ----------
# Загрузки (картинки вопросов) лежат в общем томе storage; ссылка public/storage ведёт туда.
FROM nginx:1.27-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public
COPY --from=frontend /app/public/build /var/www/html/public/build
RUN rm -f /var/www/html/public/hot /var/www/html/public/__preview-prime.* \
    && ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage
