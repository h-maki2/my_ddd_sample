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
    librdkafka-dev \
    libzip-dev \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka


# Composer のインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js と npm のインストール
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

RUN npm install -g serverless && npm install --save-dev serverless-localstack

COPY ./awsCredentialsMock /root/.aws/credentials

RUN a2enmod proxy
RUN a2enmod proxy_http

RUN sed -i "s|DocumentRoot /var/www/html|DocumentRoot /var/www/html/identity_access/src/public|" /etc/apache2/sites-available/000-default.conf

# Apacheモジュールの有効化
RUN a2enmod rewrite

# ポートの公開
EXPOSE 80

WORKDIR /var/www/html/identity_access