FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev zlib1g-dev \
    libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
  && docker-php-ext-install zip pdo_mysql xml gd \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /php

RUN cat <<'EOF' > composer.json
{
  "require": {
    "phpmailer/phpmailer": "^6.8",
    "phpoffice/phpspreadsheet": "^2.0"
  }
}
EOF

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html /php

EXPOSE 80
EXPOSE 443