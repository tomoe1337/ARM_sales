#!/bin/sh
set -e

# Создание необходимых директорий Laravel
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Установка прав доступа:
# - 775 для директорий (rwxrwxr-x)
# - 664 для файлов (rw-rw-r--)
find /var/www/html/storage -type d -exec chmod 775 {} \;
find /var/www/html/storage -type f -exec chmod 664 {} \;
find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;
find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

# Выполнение оригинальной команды
exec "$@"

