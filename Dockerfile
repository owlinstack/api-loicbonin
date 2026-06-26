FROM dunglas/frankenphp:1.4-php8.4-alpine

# Dépendances système et extensions PHP pour Laravel 13 + Filament 5
RUN apk add --no-cache \
    unzip \
    sqlite \
    && install-php-extensions \
    gd \
    pcntl \
    opcache \
    zip \
    bcmath \
    pdo_sqlite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuration FrankenPHP
ENV FRANKENPHP_CONFIG="import worker.Caddyfile"
ENV PORT=8000

WORKDIR /app

# Installation des dépendances PHP (couche de cache optimisée)
COPY composer.json composer.lock ./
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copie du code source
COPY . .

# Permissions Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Finalisation autoloader
RUN composer dump-autoload --no-dev --optimize

# Entrypoint : migrations + cache + démarrage FrankenPHP
COPY --chmod=755 <<-'EOF' /usr/local/bin/docker-entrypoint.sh
#!/bin/sh
set -e

# Création du fichier SQLite s'il n'existe pas
touch /app/database/database.sqlite
chown www-data:www-data /app/database/database.sqlite

# Cache de configuration et de routes pour la prod
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exécution des migrations
php artisan migrate --force

exec frankenphp run --config /etc/caddy/Caddyfile
EOF

ENTRYPOINT ["docker-entrypoint.sh"]
