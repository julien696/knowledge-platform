# Base PHP avec Apache
FROM php:8.2-apache

# Installer les extensions et outils nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libicu-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet dans le container
COPY . /var/www/html/
WORKDIR /var/www/html

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Exposer le port Apache
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]
