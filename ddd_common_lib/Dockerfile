FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    librdkafka-dev \
    git \
    unzip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/ddd_common_lib
