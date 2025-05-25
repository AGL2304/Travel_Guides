FROM php:8.2-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    libicu-dev zip unzip git libonig-dev libxml2-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
