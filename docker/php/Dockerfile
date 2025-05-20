FROM php:8.4-fpm

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libmagickwand-dev \
    libicu-dev \
    zlib1g-dev

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor | tee /usr/share/keyrings/yarnkey.gpg >/dev/null && \
    echo "deb [signed-by=/usr/share/keyrings/yarnkey.gpg] https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && \
    apt-get install -y yarn


# Configurer et installer les extensions PHP
RUN docker-php-ext-configure zip && \
    docker-php-ext-configure intl && \
    docker-php-ext-configure gd

# Installer les extensions PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    intl \
    mbstring \
    curl \
    opcache \
    gd \
    soap \
    fileinfo

# Installer des extensions supplémentaires via PECL - Inutilisé apparemment
#RUN pecl install imagick && \
#    docker-php-ext-enable imagick

# Créer et configurer les répertoires de cache
RUN mkdir -p /var/www/.cache/composer /var/www/.cache/yarn && touch /var/www/.yarnrc && \
    chown -R www-data:www-data /var/www/.cache /var/www/.yarnrc

# Changer l'utilisateur pour les commandes suivantes
USER www-data

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurer les variables d'environnement pour les caches
ENV COMPOSER_HOME=/var/www/.cache/composer \
    YARN_CACHE_FOLDER=/var/www/.cache/yarn

# Revenir à l'utilisateur root pour le reste de l'installation
USER root
# Nettoyer pour réduire la taille de l'image
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier la configuration PHP
COPY php.ini /usr/local/etc/php/php.ini