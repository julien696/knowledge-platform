FROM php:8.2-apache

# Installer PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier les fichiers
COPY . /var/www/html/
WORKDIR /var/www/html

# Installer les dépendances
RUN composer install --no-dev --optimize-autoloader

# Exposer le port Apache
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]