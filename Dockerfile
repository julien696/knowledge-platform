# Utiliser l'image PHP 8.3 avec Apache
FROM php:8.3-apache

# Installer les extensions PHP nécessaires pour Symfony
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring zip \
    && a2enmod rewrite

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier le projet Symfony dans le conteneur
COPY . /var/www/html/

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Donner les droits à Apache sur var/cache et var/log
RUN chown -R www-data:www-data var

# Exposer le port 80 pour Render
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]