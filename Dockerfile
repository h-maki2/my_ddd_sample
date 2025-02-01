# PHP 8.2 イメージをベースに使用
FROM php:8.2-apache

# 必要な PHP 拡張モジュールをインストール
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

# Composer のインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod proxy
RUN a2enmod proxy_http

ARG APACHE_DOCUMENT_ROOT
ENV APACHE_DOCUMENT_ROOT=${APACHE_DOCUMENT_ROOT}

RUN sed -i "s|DocumentRoot /var/www/html|DocumentRoot ${APACHE_DOCUMENT_ROOT}|" /etc/apache2/sites-available/000-default.conf

# Apacheモジュールの有効化
RUN a2enmod rewrite

# ポートの公開
EXPOSE 80