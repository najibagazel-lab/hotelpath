FROM php:8.2-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache git unzip libzip-dev libpq-dev sqlite-dev pkgconf nodejs npm \
    && docker-php-ext-install pdo_pgsql pdo_sqlite zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY package*.json ./
RUN npm ci

COPY . .

RUN npm run build \
    && composer dump-autoload --no-dev --classmap-authoritative \
    && mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chmod -R ug+rwx bootstrap/cache storage

COPY docker/start.sh /usr/local/bin/start-laravel
RUN chmod +x /usr/local/bin/start-laravel

EXPOSE 10000

CMD ["/usr/local/bin/start-laravel"]
