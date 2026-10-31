FROM php:8.5-cli AS php-deps
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libpq-dev libzip-dev unzip \
    && docker-php-ext-install -j"$(nproc)" bcmath intl pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM node:22-bookworm-slim AS assets
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
COPY --from=php-deps /var/www/html/vendor ./vendor
RUN npm run build

FROM php:8.5-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=php-deps /var/www/html ./
COPY --from=assets /var/www/html/public/build ./public/build

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enmod rewrite \
    && a2enconf laravel \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000
CMD ["sh", "-c", "php artisan migrate --force && apache2-foreground"]
