FROM php:8.3-apache

# Installer extensions PHP
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql intl mbstring zip \
    && a2enmod rewrite

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier projet Symfony
COPY . /var/www/html/
WORKDIR /var/www/html

# Installer dépendances sans scripts auto
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Exécuter manuellement Symfony
RUN php bin/console cache:clear --env=prod
RUN php bin/console cache:warmup --env=prod

# Droits Apache
RUN chown -R www-data:www-data var

EXPOSE 80
CMD ["apache2-foreground"]