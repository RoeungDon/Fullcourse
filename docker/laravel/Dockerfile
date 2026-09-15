FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring bcmath exif pcntl gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/laravel/*.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

COPY laravel-app /var/www/html

RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true

EXPOSE 8000

CMD ["bash", "/usr/local/bin/entrypoint.development.sh"]
