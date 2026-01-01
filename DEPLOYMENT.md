# 🚀 Развертывание ARM Sales на VPS

## Архитектура проекта

```
ARM_sales/
├── laravel/                 # Laravel приложение
│   ├── app/                 # Код приложения  
│   ├── database/            # Миграции и сидеры
│   ├── public/              # Веб-корень
│   ├── composer.json        # PHP зависимости
│   └── ...                  # Остальные файлы Laravel
├── docker/                  # Docker конфигурации
│   ├── nginx/               # Nginx настройки
│   ├── php/                 # PHP-FPM Dockerfile и настройки  
│   └── postgres/            # PostgreSQL инициализация
├── docker-compose.yml       # Docker Compose конфигурация
├── deploy.sh               # Скрипт автоматического развертывания
├── .env                    # Docker окружение
└── README.md               # Документация
```

## Требования
- Ubuntu 22.04 LTS
- Docker CE (последняя версия)
- Docker Compose (последняя версия)
- Минимум 2GB RAM, 1 CPU, 15GB storage

## Быстрое развертывание

### 1. Клонирование проекта
```bash
git clone https://github.com/your-username/ARM_sales.git
cd ARM_sales
```

### 2. Подготовка конфигурации
```bash
# Копирование настроек Laravel
cp laravel/.env.example laravel/.env

# Редактирование настроек Laravel (опционально)
nano laravel/.env

# Docker настройки уже готовы в корневом .env
```

### 3. Автоматическое развертывание
```bash
# Сделать скрипт исполняемым
chmod +x deploy.sh

# Запуск развертывания
./deploy.sh
```

### 4. Ручное развертывание (альтернатива)
```bash
# Сборка и запуск контейнеров
docker-compose up -d --build

# Ожидание готовности БД
sleep 30

# Миграции и инициализация
docker-compose exec php php artisan migrate --force
docker-compose exec php php artisan db:seed --force
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan config:cache
```

## Настройка после развертывания

### 1. AI-аналитика (Gemini API)
Получите API токен от [ProxyAPI.ru](https://proxyapi.ru) и добавьте в `laravel/.env`:
```env
AI_API_TOKEN=your_actual_token_here
```

### 2. Настройка домена
Замените в `laravel/.env`:
```env
APP_URL=https://your-domain.com
```

### 3. SSL сертификат (опционально)
```bash
# Создание самоподписанного сертификата
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/nginx/ssl/key.pem \
    -out docker/nginx/ssl/cert.pem
```

## Управление приложением

### Основные команды
```bash
# Статус контейнеров
docker-compose ps

# Логи всех сервисов
docker-compose logs -f

# Логи конкретного сервиса
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f postgres

# Остановка
docker-compose down

# Перезапуск
docker-compose restart

# Полная пересборка
docker-compose down && docker-compose up -d --build
```

### Laravel команды
```bash
# Выполнение artisan команд
docker-compose exec php php artisan migrate
docker-compose exec php php artisan queue:work
docker-compose exec php php artisan cache:clear

# Доступ к контейнеру
docker-compose exec php sh

# Работа с Composer
docker-compose exec php composer install
docker-compose exec php composer update
```

### Резервное копирование
```bash
# Бэкап базы данных
docker-compose exec postgres pg_dump -U arm_user arm_sales > backup.sql

# Восстановление
docker-compose exec -T postgres psql -U arm_user arm_sales < backup.sql
```

## Демо-аккаунты

После развертывания доступны тестовые аккаунты:

| Роль | Email | Пароль | Доступ |
|------|-------|---------|---------|
| Руководитель | head@demo.com | password | Полный доступ + AI-аналитика |
| Менеджер | manager@demo.com | password | Клиенты, сделки, задачи |
| Админ | admin@demo.com | password | Filament админка |

## Мониторинг ресурсов

### Проверка использования памяти
```bash
# Статистика Docker контейнеров
docker stats

# Использование диска
df -h

# Использование памяти системы
free -h
```

### Оптимизация производительности
```bash
# Очистка Docker кеша
docker system prune -f

# Очистка Laravel кешей
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan route:clear
docker-compose exec php php artisan view:clear
```

## Troubleshooting

### Частые проблемы

1. **Контейнер PHP не запускается**
   ```bash
   docker-compose logs php
   # Проверить права доступа к файлам
   sudo chown -R 1000:1000 storage bootstrap/cache
   ```

2. **Ошибка подключения к БД**
   ```bash
   # Проверить статус PostgreSQL
   docker-compose logs postgres
   # Перезапуск БД
   docker-compose restart postgres
   ```

3. **AI-аналитика не работает**
   - Проверить AI_API_TOKEN в .env
   - Проверить доступ к интернету из контейнера

4. **Нехватка памяти**
   ```bash
   # Добавить SWAP
   sudo fallocate -l 2G /swapfile
   sudo chmod 600 /swapfile
   sudo mkswap /swapfile
   sudo swapon /swapfile
   ```

5. **Медленная работа**
   - Включить OPcache (уже настроен)
   - Проверить логи на долгие запросы
   - Оптимизировать запросы к БД

## Обновление

```bash
# Обновление кода
git pull origin main

# Пересборка с новым кодом
docker-compose down
docker-compose up -d --build

# Применение новых миграций
docker-compose exec php php artisan migrate --force
```

## Безопасность

1. **Смените пароли БД** в `.env`
2. **Настройте файрвол** на VPS
3. **Отключите SSH root** доступ
4. **Настройте SSL** для продакшена
5. **Регулярно обновляйте** Docker образы

---

**Поддержка**: При проблемах проверьте логи `docker-compose logs` и документацию Laravel.