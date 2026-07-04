FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    libpq-dev \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    postgresql-client \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring intl zip bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
