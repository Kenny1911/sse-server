FROM composer:2.9.2 AS composer

FROM php:8.4.16-cli

WORKDIR /var/www/html

RUN apt-get update && \
    apt-get install -y zip libzip-dev && \
    docker-php-ext-install zip && \
    docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install --no-dev --optimize-autoloader --prefer-dist

COPY . .
RUN chmod 777 .

EXPOSE 8000

CMD ["/usr/local/bin/php", "/var/www/html/bin/sse-server", "start"]
