FROM php:8.4-fpm-alpine3.19

ARG HOST_USER_ID
ARG HOST_GROUP_ID

# Создаем пользователя hostuser с ID хоста (как в iec-exchange-backend)
RUN if [ -n "$HOST_USER_ID" ]; then \
        adduser hostuser -u $HOST_USER_ID -D || true; \
    fi

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_MEMORY_LIMIT=256M \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

# Установка системных зависимостей и PHP расширений для Laravel
RUN set -eux; \
    apk add --no-cache \
      bash \
      icu-dev \
      libpq \
      postgresql-dev \
      postgresql-client \
      libzip-dev \
      oniguruma-dev \
      libjpeg-turbo-dev \
      libpng-dev \
      freetype-dev \
      curl-dev \
      openssl-dev \
      autoconf \
      g++ \
      make \
      git \
      wget \
      dcron \
      dcron-openrc; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j$(nproc) \
      bcmath \
      exif \
      gd \
      intl \
      mbstring \
      opcache \
      pdo \
      pdo_pgsql \
      pgsql \
      zip; \
    apk del autoconf g++ make

# Установка Composer из официального образа
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Конфигурация PHP
RUN { \
      echo "memory_limit=${PHP_MEMORY_LIMIT}"; \
      echo "upload_max_filesize=20M"; \
      echo "post_max_size=20M"; \
      echo "max_execution_time=60"; \
      echo "date.timezone=Europe/Kiev"; \
      echo "opcache.enable=1"; \
      echo "opcache.memory_consumption=128"; \
      echo "opcache.max_accelerated_files=10000"; \
      echo "opcache.revalidate_freq=2"; \
      echo "opcache.validate_timestamps=${PHP_OPCACHE_VALIDATE_TIMESTAMPS}"; \
      echo "expose_php=Off"; \
    } > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Копируем crontab файл (как в iec-exchange-backend)
COPY docker/php/crontabs/* /etc/crontabs/