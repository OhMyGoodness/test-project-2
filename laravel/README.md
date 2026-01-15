# Content Platform API

REST API контент-платформы на **Laravel 11** (PHP 8.2+) с сервис-ориентированной архитектурой.

Поддерживает управление новостями, видеопостами и полиморфной системой комментариев с вложенными ответами.

## Стек технологий

- **PHP** 8.2+
- **Laravel** 11
- **MySQL** (в Docker)
- **Nginx** (в Docker)
- **Swagger/OpenAPI** (zircote/swagger-php)
- **PHPUnit** 11

## Архитектура

Проект использует **Service-Oriented Architecture** с чётким разделением слоёв:

```
app/
├── Enums/                          # CommentableType и др.
├── Exceptions/                     # Кастомные исключения (NotFoundException, BusinessLogicException)
├── Repositories/                   # Базовый интерфейс и абстрактная реализация
├── Services/
│   ├── Shared/                     # Общая логика (AbstractContentService)
│   ├── News/                       # Модуль новостей
│   │   ├── Models/
│   │   ├── DTO/
│   │   ├── Repositories/
│   │   ├── Providers/
│   │   └── Http/ (Controllers, Requests, Resources)
│   ├── VideoPost/                  # Модуль видеопостов
│   └── Comment/                    # Модуль комментариев
```

### Ключевые паттерны

- **Repository Pattern** — абстракция работы с БД через интерфейсы
- **Service Layer** — бизнес-логика изолирована от контроллеров
- **DTO** (`final readonly class`) — строго типизированный обмен данными между слоями
- **Полиморфные комментарии** — `morphMany` для привязки к News и VideoPost, вложенность через `parent_id`
- **Enum-маппинг** — `CommentableType` вместо FQCN в API
- **Soft Deletes** — для News и VideoPost
- **Кастомные исключения** — централизованная обработка ошибок
- **Модульные ServiceProviders** — DI-биндинги для каждого модуля

## Быстрый старт

### Требования

- Docker и Docker Compose
- Внешняя Docker-сеть `global_net`:

```bash
docker network create global_net
```

### Запуск

```bash
# Перейти в директорию Docker
cd test-project-2/.dev

# Запустить контейнеры
docker-compose up -d

# Установить зависимости
docker-compose run composer install --ignore-platform-reqs

# Скопировать .env (если ещё не создан)
cp ../laravel/.env.example ../laravel/.env

# Сгенерировать ключ приложения
docker exec test_project_server_php php /app/artisan key:generate

# Выполнить миграции
docker exec test_project_server_php php /app/artisan migrate
```

Приложение будет доступно на `http://localhost`.

## API эндпоинты

Все маршруты доступны под префиксом `/api`.

### News

| Метод    | URL               | Описание                  |
|----------|--------------------|---------------------------|
| `GET`    | `/api/news`        | Список новостей (пагинация) |
| `POST`   | `/api/news`        | Создать новость           |
| `GET`    | `/api/news/{id}`   | Получить новость          |
| `PUT`    | `/api/news/{id}`   | Обновить новость          |
| `DELETE` | `/api/news/{id}`   | Удалить новость           |

### Video Posts

| Метод    | URL                      | Описание                       |
|----------|---------------------------|--------------------------------|
| `GET`    | `/api/video-posts`        | Список видеопостов (пагинация) |
| `POST`   | `/api/video-posts`        | Создать видеопост              |
| `GET`    | `/api/video-posts/{id}`   | Получить видеопост             |
| `PUT`    | `/api/video-posts/{id}`   | Обновить видеопост             |
| `DELETE` | `/api/video-posts/{id}`   | Удалить видеопост              |

### Comments

| Метод    | URL                    | Описание               |
|----------|-------------------------|------------------------|
| `POST`   | `/api/comments`         | Создать комментарий    |
| `PUT`    | `/api/comments/{id}`    | Обновить комментарий   |
| `DELETE` | `/api/comments/{id}`    | Удалить комментарий    |

Комментарии загружаются вместе с родительской сущностью (News/VideoPost). Поддерживается курсорная пагинация и один уровень вложенности (ответы).

## Swagger-документация

Генерация актуальной документации:

```bash
docker exec test_project_server_php php /app/vendor/bin/openapi /app/app -o /app/public/swagger.json
```

Файл будет доступен по адресу `http://localhost/swagger.json`.

## Тестирование

```bash
# Запуск всех тестов
docker exec test_project_server_php php /app/artisan test

# Запуск конкретного теста
docker exec test_project_server_php php /app/artisan test --filter=NewsServiceTest
```

Тесты расположены в:
- `tests/Unit/Services/` — unit-тесты сервисов (моки через интерфейсы)
- `tests/Feature/Api/` — feature-тесты API эндпоинтов

## Docker-контейнеры

| Контейнер                     | Порт  | Назначение     |
|-------------------------------|-------|----------------|
| `test_project_server_nginx`   | 80    | Веб-сервер     |
| `test_project_server_php`     | —     | PHP-FPM        |
| `test_project_server_mysql`   | 3306  | База данных    |

## Структура проекта

```
test-project-2/
├── .dev/                  # Docker-конфигурация
│   ├── docker-compose.yml
│   ├── nginx/             # Конфиг Nginx
│   ├── php/               # Dockerfile и php.ini
│   └── ssl/               # SSL-сертификаты
└── laravel/               # Код приложения Laravel
    ├── app/
    ├── routes/
    ├── tests/
    └── ...
```
