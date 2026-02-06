# PHP 8.4 FPM Alpine - Laravel 12 + Filament + Livewire
FROM php:8.4-fpm-alpine

# Variables
ARG UID=1000
ARG GID=1000

# Extensions requises
RUN apk add --no-cache \
    git curl zip unzip bash shadow \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev libxml2-dev oniguruma-dev icu-dev \
    sqlite-dev \
    fontconfig ttf-freefont ttf-dejavu \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_sqlite pdo_mysql \
        mbstring exif pcntl bcmath gd zip intl opcache

# Configuration PHP optimisee
RUN echo "upload_max_filesize=100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Utilisateur www-data avec UID/GID personnalise
RUN deluser www-data 2>/dev/null || true \
    && delgroup www-data 2>/dev/null || true \
    && addgroup -g ${GID} www-data \
    && adduser -u ${UID} -G www-data -s /bin/sh -D www-data

WORKDIR /var/www/html

# Copie du code source
COPY --chown=www-data:www-data . .

# Installation des dependances
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions Laravel
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache \
    && chmod 664 database/database.sqlite

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
