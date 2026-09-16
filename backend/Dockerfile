# Laravel 12 + PHP 8.2 + Apache
FROM php:8.2-apache

# --------------------------------------------------
# System dependencies
# --------------------------------------------------
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

# --------------------------------------------------
# Composer
# --------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Application directory
# --------------------------------------------------
WORKDIR /var/www/html

# --------------------------------------------------
# PHP configuration
# --------------------------------------------------
RUN { \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
    echo "max_execution_time=180"; \
    echo "expose_php=Off"; \
} > /usr/local/etc/php/conf.d/zz-tefera.ini

# --------------------------------------------------
# Apache configuration
# Laravel must use /public as DocumentRoot
# --------------------------------------------------
RUN sed -ri \
    's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' \
    /etc/apache2/sites-available/000-default.conf

RUN sed -ri \
    's#<Directory /var/www/>#<Directory /var/www/html/public/>#' \
    /etc/apache2/apache2.conf

# Allow Laravel .htaccess
RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# --------------------------------------------------
# Copy Laravel application
# --------------------------------------------------
COPY . .

# --------------------------------------------------
# Install PHP dependencies
# --------------------------------------------------
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

# --------------------------------------------------
# Laravel storage/cache directories
# --------------------------------------------------
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

# --------------------------------------------------
# Storage symbolic link
# --------------------------------------------------
RUN php artisan storage:link --no-interaction || true

# --------------------------------------------------
# Permissions
# --------------------------------------------------
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    && chmod -R 775 \
    storage \
    bootstrap/cache

# --------------------------------------------------
# Render uses PORT=10000 by default.
# Configure Apache to listen on Render's PORT.
# --------------------------------------------------
RUN sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' \
        /etc/apache2/sites-available/000-default.conf

# --------------------------------------------------
# Expose Render's default HTTP port
# --------------------------------------------------
EXPOSE 10000

# --------------------------------------------------
# Start Apache
# --------------------------------------------------
CMD ["apache2-foreground"]
