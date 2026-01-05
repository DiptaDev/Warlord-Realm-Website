FROM php:8.5.2RC1-apache

# Enable Apache modules
RUN a2enmod rewrite headers expires

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    unzip \
    zip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configure GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install PHP extensions
RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    mbstring \
    intl \
    zip \
    exif \
    bcmath \
    soap \
    gd 

# Set working directory
WORKDIR /var/www/html
