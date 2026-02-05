# Dockerfile pour Laravel 12 avec PHP 8.4
# Optimisé pour Filament, Livewire et export PDF

FROM php:8.4-fpm-alpine

# Arguments de build
ARG USER_ID=1000
ARG GROUP_ID=1000

# Variables d'environnement
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer
ENV PATH="${PATH}:/composer/vendor/bin"

# Installation des dépendances système
RUN apk update && apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    bash \
    shadow \
    # Pour netcat (attente DB)
    netcat-openbsd \
    # Bibliothèques pour les extensions PHP
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev \
    # Pour PDF (DomPDF)
    fontconfig \
    ttf-freefont \
    ttf-dejavu \
    # MySQL client
    mysql-client \
    # Node.js pour les assets
    nodejs \
    npm

# Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        xml

# Installation de Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Configuration PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Configuration PHP-FPM
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Script d'entrée (avant de changer d'utilisateur)
COPY docker/php/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Création de l'utilisateur www-data avec les bons UID/GID
RUN deluser www-data 2>/dev/null || true \
    && delgroup www-data 2>/dev/null || true \
    && addgroup -g ${GROUP_ID} www-data \
    && adduser -u ${USER_ID} -G www-data -s /bin/bash -D www-data

# Création du répertoire de logs PHP (après création utilisateur)
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

# Répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers Composer d'abord pour le cache
COPY --chown=www-data:www-data composer.json composer.lock* ./

# Installation des dépendances Composer (sans scripts pour le moment)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copie du reste de l'application
COPY --chown=www-data:www-data . .

# Finalisation de l'installation Composer
RUN composer dump-autoload --optimize \
    && composer run-script post-autoload-dump --no-interaction || true

# Installation des assets Node.js (si package.json existe)
RUN if [ -f package.json ]; then \
        npm ci --production=false \
        && npm run build \
        && rm -rf node_modules; \
    fi

# Permissions finales
RUN mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Utilisateur non-root
USER www-data

# Exposition du port PHP-FPM
EXPOSE 9000

# Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Commande par défaut
CMD ["php-fpm"]
