FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    zip \
    pdo_mysql \
    xml \
    gd \
    mbstring \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Install Composer dependencies OUTSIDE the bind mount
WORKDIR /php

RUN composer init --no-interaction \
    --name="app/core" \
    --require="phpmailer/phpmailer:^6.8" \
    --require="phpoffice/phpspreadsheet:^2.0"

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Application code will be mounted here
WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]