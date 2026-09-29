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

RUN mkdir -p /var/lib/el-lugar/portadas \
 && chown www-data:www-data /var/lib/el-lugar/portadas \
 && chmod 0755 /var/lib/el-lugar/portadas

RUN printf "upload_max_filesize=9M\npost_max_size=10M\n" \
    > /usr/local/etc/php/conf.d/uploads.ini

# Application code will be mounted here
WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]