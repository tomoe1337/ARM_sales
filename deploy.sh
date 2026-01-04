#!/bin/bash

# Скрипт деплоя ARM Sales
# Генерирует пароли, создает .env и запускает контейнеры

set -e

echo "🚀 Деплой ARM Sales"
echo ""

# Проверка Docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker не установлен!"
    exit 1
fi

# Генерация безопасного пароля
generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
}

# Создание или пересоздание .env
if [ ! -f .env ]; then
    CREATE_ENV=true
else
    echo "✅ .env уже существует"
    # Показываем текущий NGINX_CONF если есть
    if grep -q "NGINX_CONF" .env; then
        CURRENT_CONF=$(grep "NGINX_CONF" .env | cut -d'=' -f2)
        echo "   Текущий NGINX_CONF: ${CURRENT_CONF}"
    fi
    echo ""
    read -p "Пересоздать .env? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        CREATE_ENV=true
        echo "🔄 Будет создан новый .env файл"
    else
        CREATE_ENV=false
        echo "⏭️  Используется существующий .env"
    fi
fi

if [ "$CREATE_ENV" = true ]; then
    echo "🔑 Генерация паролей..."
    DB_PASSWORD=$(generate_password)
    
    # Выбор окружения
    echo ""
    echo "Выберите окружение:"
    echo "  1) Development (default.conf) - по умолчанию"
    echo "  2) Production (production.conf)"
    read -p "Ваш выбор [1]: " -n 1 -r
    echo
    
    case $REPLY in
        2)
            NGINX_CONF="production.conf"
            echo "✅ Выбрано: Production"
            ;;
        *)
            NGINX_CONF="default.conf"
            echo "✅ Выбрано: Development"
            ;;
    esac
    
    # Проверка существования выбранного конфига
    if [ "$NGINX_CONF" = "production.conf" ] && [ ! -f "./docker/nginx/production.conf" ]; then
        echo "⚠️  Внимание: файл production.conf не найден!"
        read -p "Продолжить с default.conf? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo "Отменено"
            exit 1
        fi
        NGINX_CONF="default.conf"
    fi
    
    echo ""
    echo "📝 Создание .env файла..."
    cat > .env <<EOF
# PostgreSQL настройки
POSTGRES_DB=arm_sales
POSTGRES_USER=arm_user
POSTGRES_PASSWORD=${DB_PASSWORD}

# Nginx конфигурация
NGINX_CONF=${NGINX_CONF}
EOF
    echo "✅ .env создан (NGINX_CONF=${NGINX_CONF})"
fi

echo ""
echo "🐳 Запуск Docker Compose..."
docker-compose --profile workers up -d --build

echo ""
echo "✅ Готово!"
docker-compose ps
