# ARM Sales - Makefile для управления Docker окружением
# Простая конфигурация: nginx + php + postgres

.PHONY: help setup build up down restart logs shell clean

# Помощь
help:
	@echo "ARM Sales - Команды управления:"
	@echo ""
	@echo "  setup     - Первоначальная настройка проекта"
	@echo "  build     - Сборка Docker контейнеров"
	@echo "  up        - Запуск всех сервисов"
	@echo "  down      - Остановка всех сервисов"
	@echo "  restart   - Перезапуск сервисов"
	@echo "  logs      - Просмотр логов"
	@echo "  shell     - Доступ к PHP контейнеру"
	@echo "  clean     - Очистка Docker кеша"
	@echo ""
	@echo "  Laravel команды:"
	@echo "  migrate   - Выполнить миграции"
	@echo "  seed      - Заполнить базу тестовыми данными"
	@echo "  cache     - Очистить кеши Laravel"
	@echo ""

# Первоначальная настройка
setup:
	@echo "🛠️ Настройка проекта..."
	@if [ ! -f "laravel/.env" ]; then \
		cp laravel/.env.example laravel/.env; \
		echo "✅ Создан laravel/.env файл"; \
	fi
	@if [ ! -f ".env" ]; then \
		echo "COMPOSE_PROJECT_NAME=arm_sales" > .env; \
		echo "✅ Создан .env файл"; \
	fi
	@echo "🎯 Готово! Теперь выполните: make up"

# Сборка контейнеров
build:
	@echo "🔨 Сборка Docker контейнеров..."
	docker-compose build --no-cache

# Запуск сервисов
up:
	@echo "🚀 Запуск сервисов..."
	docker-compose up -d
	@echo "⏳ Ожидание готовности базы данных..."
	@sleep 20
	@make migrate
	@echo "✅ Приложение доступно по адресу: http://localhost"

# Остановка сервисов
down:
	@echo "🛑 Остановка сервисов..."
	docker-compose down

# Перезапуск
restart: down up

# Просмотр логов
logs:
	docker-compose logs -f

# Доступ к PHP контейнеру
shell:
	docker-compose exec php sh

# Очистка Docker кеша
clean:
	@echo "🧹 Очистка Docker кеша..."
	docker system prune -f
	docker volume prune -f

# Laravel команды
migrate:
	@echo "🗄️ Выполнение миграций..."
	docker-compose exec php php artisan migrate --force

seed:
	@echo "🌱 Заполнение базы тестовыми данными..."
	docker-compose exec php php artisan db:seed --force

cache:
	@echo "🧹 Очистка кешей Laravel..."
	docker-compose exec php php artisan cache:clear
	docker-compose exec php php artisan config:clear
	docker-compose exec php php artisan route:clear
	docker-compose exec php php artisan view:clear

# Быстрое развертывание для демо
demo: setup build up seed
	@echo "🎉 Демо-окружение готово!"
	@echo "📋 Доступные аккаунты:"
	@echo "   Руководитель: head@demo.com / password"
	@echo "   Менеджер: manager@demo.com / password"