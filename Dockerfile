# Dockerfile pour Symfony 7.3 + PHP 8.3

FROM php:8.3-apache

# Installer extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring zip \
    && a2enmod rewrite

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier le projet Symfony
COPY . /var/www/html/
WORKDIR /var/www/html

# Installer les dépendances PROD avec Composer (Runtime inclus)
RUN composer install --no-dev --optimize-autoloader

# Exécuter manuellement les commandes Symfony
RUN php bin/console cache