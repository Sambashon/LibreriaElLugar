FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev zlib1g-dev \
  && docker-php-ext-install zip pdo_mysql \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p /php/vendor

WORKDIR /php

RUN echo '{ "require": { "phpmailer/phpmailer": "^6.8" } }' > composer.json

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html /php

EXPOSE 80
EXPOSE 443