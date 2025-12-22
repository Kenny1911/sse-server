FROM composer:2.9.2 AS composer

FROM php:8.4.16-fpm

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
