FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

FROM php:8.4-fpm-alpine AS app
RUN apk add --no-cache libpq-dev sqlite-dev \
    && docker-php-ext-install pdo_pgsql pdo_sqlite bcmath
WORKDIR /var/www/html
COPY --from=vendor /app .
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache
EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

FROM nginx:1.28-alpine AS web
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=vendor /app/public /var/www/html/public
