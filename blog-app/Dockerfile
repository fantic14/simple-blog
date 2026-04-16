FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    zip \
    unzip \
    sqlite3 \
    libpng-dev \
    libonig-dev \
    libxml2-dev

RUN docker-php-ext-install pdo_sqlite mbstring bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --no-scripts

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]