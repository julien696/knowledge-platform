# Base PHP avec Apache
FROM php:8.2-apache

# Installer extensions PHP et outils nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libicu-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet
COPY . /var/www/html/
WORKDIR /var/www/html

# Variables d'environnement pour prod
ARG APP_ENV=prod
ENV APP_ENV=${APP_ENV}
ENV APP_SECRET=changeme123

# Installer les dépendances Symfony (prod seulement)
RUN composer install --no-dev --optimize-autoloader

# Vider le cache prod pour éviter les erreurs
RUN php bin/console cache:clear --env=prod --no-warmup

# Tester la connexion PDO MySQL (optionnel mais pratique pour debug)
# RUN php bin/console doctrine:database:connect || echo "Check DATABASE_URL"

# Exposer le port Apache
EXPOSE 80

# Lancer Apache
CMD ["apache2-foreground"]

