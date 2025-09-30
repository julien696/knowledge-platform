FROM php:8.2-apache

# Dépendances système
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git libicu-dev libonig-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le projet
COPY . /var/www/html/
WORKDIR /var/www/html

# Variables d'environnement pour le build
ARG APP_ENV=prod
ARG DATABASE_URL
ARG JWT_PASSPHRASE
ENV APP_ENV=${APP_ENV}
ENV DATABASE_URL=${DATABASE_URL}
ENV APP_SECRET=e322ce7be5704119d8e71fb4ba34fbf8
ENV JWT_PASSPHRASE=${JWT_PASSPHRASE}

# Copier les clés JWT pour prod
RUN mkdir -p config/jwt
COPY config/jwt/private.pem config/jwt/private.pem
COPY config/jwt/public.pem config/jwt/public.pem

# Installer les dépendances Symfony (prod)
RUN composer install --no-dev --optimize-autoloader

# Vider le cache prod
RUN php bin/console cache:clear --env=prod --no-warmup

EXPOSE 80
CMD ["apache2-foreground"]


