FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    mysql-client \
    mariadb-connector-c

# Install PHP extensions untuk MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install additional extensions jika diperlukan
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

WORKDIR /var/www/html