FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod proxy
RUN a2enmod proxy_http

RUN sed -i "s|DocumentRoot /var/www/html|DocumentRoot /var/www/html/task_management/src/public|" /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80

WORKDIR /var/www/html/task_management