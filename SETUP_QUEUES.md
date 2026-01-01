# Настройка очередей

## Шаги для запуска

```bash
# 1. Добавить в laravel/.env
echo "QUEUE_CONNECTION=database" >> laravel/.env

# 2. Остановить контейнеры
docker-compose down

# 3. Применить миграции для очередей
docker-compose up -d
docker-compose exec php php artisan migrate

# 4. Запустить воркер очередей
docker-compose --profile workers up -d

# 5. Проверить что все работает
docker ps  # Должны видеть arm_queue_worker
docker logs -f arm_queue_worker
```

## Как проверить что очередь работает

### До изменений (sync):
1. Нажимаешь "Посмотреть детальный анализ"
2. Страница висит 30-60 секунд
3. Потом редирект с готовым отчетом

### После изменений (database):
1. Нажимаешь "Посмотреть детальный анализ"  
2. **Мгновенный** редирект с сообщением "Генерация запущена..."
3. Обновляешь страницу через минуту → видишь отчет

## Мониторинг очереди

```bash
# Посмотреть задачи в очереди
docker-compose exec php php artisan queue:monitor

# Логи воркера в реальном времени
docker logs -f arm_queue_worker

# Список неудачных задач
docker-compose exec php php artisan queue:failed

# Повторить неудачную задачу
docker-compose exec php php artisan queue:retry {id}
```

## Отличия для юзера

**Раньше (sync):**
- ❌ Долгое ожидание на странице
- ❌ Риск таймаута браузера
- ❌ Нельзя работать параллельно

**Сейчас (database + worker):**
- ✅ Мгновенный ответ
- ✅ Работа в фоне
- ✅ Можно делать другие действия
- ✅ Повторные попытки при ошибке

