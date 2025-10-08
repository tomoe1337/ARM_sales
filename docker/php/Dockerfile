FROM php:8.2-fpm-alpine

# Установка системных зависимостей
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    openssl-dev

# Установка PHP расширений для Laravel + PostgreSQL
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        sockets

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка Node.js и npm для Vite
RUN apk add --no-cache nodejs npm

# Создание пользователя для Laravel
RUN addgroup -g 1000 -S www \
    && adduser -u 1000 -S www -G www

# Установка рабочей директории
WORKDIR /var/www/html

# Копирование файлов проекта из папки laravel
COPY --chown=www:www ../../laravel/ /var/www/html/

# Права доступа
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Установка зависимостей Composer
RUN if [ -f "composer.json" ]; then composer install --optimize-autoloader --no-dev --no-interaction; fi

# Установка зависимостей npm и сборка фронтенда
RUN if [ -f "package.json" ]; then npm ci && npm run build; fi

# Очистка кеша и оптимизация Laravel
RUN if [ -f "artisan" ]; then \
        php artisan config:cache && \
        php artisan route:cache && \
        php artisan view:cache; \
    fi

# Переключение на пользователя www
USER www

# Expose порт 9000 для PHP-FPM
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"]