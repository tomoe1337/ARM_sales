#!/bin/bash

# Скрипт деплоя ARM Sales на production сервер
# Использование: ./deploy.sh

set -e

echo "================================================"
echo "🚀 Деплой ARM Sales"
echo "================================================"

# Проверка наличия docker и docker-compose
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен!"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose не установлен!"
    exit 1
fi

# Проверка наличия .env файла
if [ ! -f ./laravel/.env ]; then
    echo "⚠️  Файл .env не найден!"
    echo "📝 Создаю из .env.example..."
    cp ./laravel/.env.example ./laravel/.env
    echo "✅ Файл .env создан. Проверьте настройки!"
fi

# Остановка старых контейнеров
echo ""
echo "🛑 Остановка старых контейнеров..."
docker-compose down

# Сборка новых образов
echo ""
echo "🏗️  Сборка Docker образов..."
docker-compose build --no-cache --pull

# Запуск контейнеров
echo ""
echo "🚀 Запуск контейнеров..."
docker-compose up -d

# Ожидание готовности PostgreSQL
echo ""
echo "⏳ Ожидание готовности базы данных..."
sleep 15

# Установка зависимостей Composer
echo ""
echo "📦 Установка зависимостей Composer..."
docker-compose exec -T php composer install --no-dev --optimize-autoloader --no-interaction

# Генерация ключа приложения (если не установлен)
echo ""
echo "🔑 Проверка APP_KEY..."
if ! docker-compose exec -T php grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "🔑 Генерация APP_KEY..."
    docker-compose exec -T php php artisan key:generate --force
else
    echo "✅ APP_KEY уже установлен"
fi

# Создание необходимых директорий
echo ""
echo "📁 Создание необходимых директорий..."
docker-compose exec -T php mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Установка прав доступа
echo ""
echo "🔐 Установка прав доступа..."
docker-compose exec -T php chmod -R 775 storage bootstrap/cache

# Миграция базы данных
echo ""
echo "📊 Выполнение миграций..."
docker-compose exec -T php php artisan migrate --force

# Создание символической ссылки для storage
echo ""
echo "🔗 Создание символической ссылки storage..."
docker-compose exec -T php php artisan storage:link || true

# Очистка и создание кеша
echo ""
echo "🗑️  Очистка кеша..."
docker-compose exec -T php php artisan config:clear
docker-compose exec -T php php artisan cache:clear
docker-compose exec -T php php artisan route:clear
docker-compose exec -T php php artisan view:clear

echo ""
echo "📦 Создание кеша конфигурации..."
docker-compose exec -T php php artisan config:cache
docker-compose exec -T php php artisan route:cache
docker-compose exec -T php php artisan view:cache

# Статус контейнеров
echo ""
echo "================================================"
echo "✅ Деплой завершен успешно!"
echo "================================================"
echo ""
echo "📊 Статус контейнеров:"
docker-compose ps

echo ""
echo "🌐 Приложение доступно по адресу: http://localhost"
echo ""
echo "📝 Полезные команды:"
echo "  - Логи:            docker-compose logs -f"
echo "  - Остановка:       docker-compose down"
echo "  - Перезапуск:      docker-compose restart"
echo "  - Статус:          docker-compose ps"
echo "  - Artisan:         docker-compose exec php php artisan"
echo ""