FROM php:8.2-apache

# Dépendances système
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libicu-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuration Apache pour Symfony
RUN a2enmod rewrite
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet
COPY . /var/www/html/
WORKDIR /var/www/html/

# Variables d'environnement
ENV APP_ENV=prod
ENV APP_SECRET=e322ce7be5704119d8e71fb4ba34fbf8

# Installer les dépendances Symfony (prod)
RUN composer install --no-dev --optimize-autoloader

# Générer les clés JWT
RUN php bin/console lexik:jwt:generate-keypair --no-interaction

# Vider le cache prod
RUN php bin/console cache:clear --env=prod --no-warmup

EXPOSE 80
CMD ["apache2-foreground"]