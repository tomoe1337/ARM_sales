#!/bin/bash

# ARM Sales - Скрипт развертывания на VPS
# Использование: ./deploy.sh

set -e

echo "🚀 Начинаем развертывание ARM Sales..."

# Проверка Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен!"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose не установлен!"
    exit 1
fi

# Создание .env файла в папке Laravel
if [ ! -f "laravel/.env" ]; then
    echo "📝 Создание .env файла..."
    
    # Копирование шаблона
    if [ -f "laravel/.env.example" ]; then
        cp laravel/.env.example laravel/.env
    elif [ -f ".env.example" ]; then
        cp .env.example laravel/.env
    else
        echo "❌ Не найден .env.example файл!"
        exit 1
    fi
    
    # Генерация ключа приложения
    APP_KEY=$(openssl rand -base64 32)
    sed -i "s/APP_KEY=.*/APP_KEY=base64:$APP_KEY/" laravel/.env
    
    echo "✅ .env файл создан"
fi

# Остановка старых контейнеров
echo "🛑 Остановка старых контейнеров..."
docker-compose down --remove-orphans

# Сборка и запуск контейнеров
echo "🔨 Сборка и запуск контейнеров..."
docker-compose up -d --build

# Ожидание готовности базы данных
echo "⏳ Ожидание готовности PostgreSQL..."
sleep 30

# Проверка статуса контейнеров
echo "📊 Проверка статуса контейнеров..."
docker-compose ps

# Выполнение миграций Laravel
echo "🗄️ Выполнение миграций..."
docker-compose exec -T php php artisan migrate --force

# Выполнение сидеров (создание демо-данных)
echo "🌱 Создание демо-данных..."
docker-compose exec -T php php artisan db:seed --force

# Очистка и кеширование
echo "🧹 Очистка кешей и оптимизация..."
docker-compose exec -T php php artisan config:cache
docker-compose exec -T php php artisan route:cache
docker-compose exec -T php php artisan view:cache

# Права доступа
echo "🔐 Настройка прав доступа..."
docker-compose exec -T php chown -R www:www /var/www/html/storage
docker-compose exec -T php chown -R www:www /var/www/html/bootstrap/cache

# Проверка доступности
echo "🌐 Проверка доступности приложения..."
sleep 10

if curl -f http://localhost > /dev/null 2>&1; then
    echo "✅ Приложение успешно развернуто!"
    echo "🎯 Доступно по адресу: http://your-server-ip"
    echo ""
    echo "📋 Демо-аккаунты:"
    echo "   Руководитель: head@demo.com / password"
    echo "   Менеджер: manager@demo.com / password"
    echo "   Админ: admin@demo.com / password"
    echo ""
    echo "🔧 Полезные команды:"
    echo "   Логи: docker-compose logs -f"
    echo "   Статус: docker-compose ps"
    echo "   Остановка: docker-compose down"
else
    echo "❌ Ошибка развертывания!"
    echo "📋 Проверьте логи: docker-compose logs"
    exit 1
fi

echo "🎉 Развертывание завершено успешно!"