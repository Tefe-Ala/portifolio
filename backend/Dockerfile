```dockerfile
# syntax=docker/dockerfile:1

# TEFERA Portfolio — Laravel 12 / PHP 8.2
# Production Docker container for Render
# Build context: ./backend

FROM php:8.2-apache

# ---------------------------------------------------------
# System dependencies + PHP extensions
# ---------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        curl \
        libzip-dev \
        libsqlite3-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libcurl4-openssl-dev \
        libsodium-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_sqlite \
        mbstring \
        exif \
        intl \
        zip \
        opcache \
        gd \
        sodium \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------
# Application directory
# ---------------------------------------------------------
WORKDIR /var/www/html

# ---------------------------------------------------------
# PHP configuration
# ---------------------------------------------------------
RUN { \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=64M'; \
    echo 'post_max_size=64M'; \
    echo 'max_execution_time=180'; \
    echo 'expose_php=Off'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.max_accelerated_files=20000'; \
  } > /usr/local/etc/php/conf.d/zz-tefera.ini

# ---------------------------------------------------------
# Apache configuration
# Laravel must be served from /public
# ---------------------------------------------------------
RUN sed -ri \
    's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#; \
     s#<Directory /var/www/html>#<Directory /var/www/html/public>#' \
    /etc/apache2/sites-available/000-default.conf

# Allow Laravel .htaccess
RUN sed -i '/<Directory \/var\/www\/html\/public>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
    /etc/apache2/sites-available/000-default.conf

# ---------------------------------------------------------
# Copy Laravel application
# ---------------------------------------------------------
COPY . .

# ---------------------------------------------------------
# Install PHP dependencies
# ---------------------------------------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

# ---------------------------------------------------------
# Laravel storage and cache directories
# ---------------------------------------------------------
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

# ---------------------------------------------------------
# Create SQLite database if it doesn't exist
# ---------------------------------------------------------
RUN mkdir -p database \
    && touch database/database.sqlite

# ---------------------------------------------------------
# Laravel storage link
# ---------------------------------------------------------
RUN php artisan storage:link --no-interaction || true

# ---------------------------------------------------------
# Permissions
# ---------------------------------------------------------
RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
        database \
    && chmod -R 775 \
        storage \
        bootstrap/cache \
        database

# ---------------------------------------------------------
# Render PORT configuration
# ---------------------------------------------------------
# Render provides the PORT environment variable.
# Default Render web-service port is 10000.
#
# Apache inside the container listens on 10000.
# Render forwards external traffic to this port.

RUN sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

EXPOSE 10000

# ---------------------------------------------------------
# Entrypoint
# ---------------------------------------------------------
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# ---------------------------------------------------------
# Start Apache
# ---------------------------------------------------------
CMD ["apache2-foreground"]
```
