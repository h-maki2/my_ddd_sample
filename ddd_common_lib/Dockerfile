FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    curl \
    git \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml sockets

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/ddd_common_lib
