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

# Créer le fichier .env pour la production
RUN echo 'APP_ENV=prod\n\
APP_SECRET=e322ce7be5704119d8e71fb4ba34fbf8\n\
DATABASE_URL="mysql://user:password@localhost:3306/database"\n\
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem\n\
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem\n\
JWT_PASSPHRASE=knowledge\n\
MAILER_DSN=smtp://localhost:1025\n\
CORS_ALLOW_ORIGIN=*\n\
MESSENGER_TRANSPORT_DSN=doctrine://default' > .env

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