-- Инициализация базы данных ARM Sales
-- Создание расширений для PostgreSQL

-- UUID расширение (может понадобиться для Filament)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Полнотекстовый поиск для русского языка
CREATE EXTENSION IF NOT EXISTS "unaccent";

-- Настройки для русской локали
SET lc_messages TO 'en_US.UTF-8';
SET lc_monetary TO 'ru_RU.UTF-8';
SET lc_numeric TO 'ru_RU.UTF-8';
SET lc_time TO 'ru_RU.UTF-8';

-- Создание индексов для оптимизации поиска
-- (будут созданы автоматически после миграций Laravel)

-- Настройки производительности для ограниченной памяти
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET work_mem = '4MB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET random_page_cost = 1.1;
ALTER SYSTEM SET effective_io_concurrency = 200;

-- Настройки соединений
ALTER SYSTEM SET max_connections = 20;
ALTER SYSTEM SET max_worker_processes = 2;
ALTER SYSTEM SET max_parallel_workers_per_gather = 1;
ALTER SYSTEM SET max_parallel_workers = 2;

-- Логирование (для отладки на этапе разработки)
ALTER SYSTEM SET log_statement = 'none';
ALTER SYSTEM SET log_min_duration_statement = 1000;

-- Применение настроек
SELECT pg_reload_conf();