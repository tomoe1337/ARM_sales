#!/bin/sh

# Получаем UID и GID из переменных окружения или используем значения по умолчанию
TARGET_UID=${HOST_USER_ID:-${UID:-1000}}
TARGET_GID=${HOST_GROUP_ID:-${GID:-1000}}

# Создание необходимых директорий Laravel
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Если мы root - устанавливаем права и владельца
if [ "$(id -u)" = "0" ]; then
    # Установка владельца для директорий и файлов
    chown -R ${TARGET_UID}:${TARGET_GID} /var/www/html/storage 2>/dev/null || true
    chown -R ${TARGET_UID}:${TARGET_GID} /var/www/html/bootstrap/cache 2>/dev/null || true
    
    # Установка прав доступа:
    # - 775 для директорий (rwxrwxr-x)
    # - 664 для файлов (rw-rw-r--)
    find /var/www/html/storage -type d -exec chmod 775 {} \; 2>/dev/null || true
    find /var/www/html/storage -type f -exec chmod 664 {} \; 2>/dev/null || true
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
    find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true
    
    # Переключаемся на нужного пользователя для выполнения команды
    if [ -n "$TARGET_UID" ] && [ "$TARGET_UID" != "0" ]; then
        exec su-exec ${TARGET_UID}:${TARGET_GID} "$@"
    else
        exec "$@"
    fi
else
    # Если не root - пытаемся установить права только на то, что можем
    find /var/www/html/storage -type d -exec chmod 775 {} \; 2>/dev/null || true
    find /var/www/html/storage -type f -exec chmod 664 {} \; 2>/dev/null || true
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
    find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true
    
    # Выполнение оригинальной команды
    exec "$@"
fi

