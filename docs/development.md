# Разработка

## Требования

Для основного сценария разработки на хосте нужны:

- Git;
- Make;
- Docker с поддержкой Compose.

PHP, Composer, PHPUnit и PHPStan запускаются внутри PHP-контейнера. Устанавливать их на хост отдельно не требуется.

## Первый запуск

```bash
make init
make build
make up
make composer-install
make demo-data
```

`make init`:

- создаёт `.env.docker` на основе [`.env.docker.example`](../.env.docker.example), если файла ещё нет;
- подставляет текущие host UID/GID;
- создаёт `logs/` и `runtime/cache/`.

После запуска приложение по умолчанию доступно на `http://localhost:8080`.

Полезная проверка состояния:

```bash
make ps
make db-check
```

Остановить контейнеры, сохранив PostgreSQL volume:

```bash
make down
```

## Docker Compose

[`compose.yaml`](../compose.yaml) поднимает три сервиса:

- `php` — PHP 8.5 FPM и инструменты разработки;
- `nginx` — Nginx 1.30.4;
- `postgres` — PostgreSQL 18.4.

PHP-контейнер собирается из [`docker/php/Dockerfile`](../docker/php/Dockerfile). Пользователь `app` получает UID/GID пользователя хоста, чтобы `vendor`, runtime-артефакты и другие создаваемые файлы не становились root-owned.

Основные команды:

```bash
make build
make up
make down
make restart php
make restart nginx
make restart postgres
make log php
make log nginx
make log postgres
make log-all
make in php
```

Проверить все доступные Make targets можно обычной командой:

```bash
make
```

## Переменные окружения

Для Docker используется `.env.docker`, создаваемый из [`.env.docker.example`](../.env.docker.example).

Основные группы настроек:

- `HOST_UID`, `HOST_GID` — права файлов на хосте;
- `APP_PORT` — внешний HTTP-порт, по умолчанию `8080`;
- `DB_FORWARD_PORT` — проброс PostgreSQL на хост;
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` — локальный PostgreSQL;
- `APP_ENV`, `APP_NAMESPACE`, `APP_TIMEZONE` — настройки приложения;
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — подключение приложения к БД.

Значения в example-файле предназначены только для локальной разработки. Production secrets в Git не добавляются.

Для ручного запуска без Docker используется отдельный шаблон [`.env.example`](../.env.example).

## PostgreSQL и демонстрационные данные

При первом старте нового PostgreSQL volume схема создаётся из [`docs/db_schema_only.sql`](db_schema_only.sql).

Заполнить пустую таблицу 50 демонстрационными статьями:

```bash
make demo-data
```

Команда специально отказывается работать, если `blog_posts` уже содержит строки.

Проверить версию PostgreSQL и состояние таблицы:

```bash
make db-check
```

Полное пересоздание локального PostgreSQL volume является деструктивной операцией и требует явного подтверждения:

```bash
make postgres-reinit CONFIRM=postgres18
```

С демонстрационными данными:

```bash
make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1
```

## Отдельная тестовая база

PHPUnit не работает с основной `mvc_v1`. Для тестов используется отдельная база `mvc_v1_test`.

Основные команды:

```bash
make test-db-create
make test-db-reset
make test-db-check
```

`make test`, `make test-dox`, `make coverage`, `make coverage-html` и `make check` сами вызывают reset тестовой базы перед запуском.

Тестовый fixture восстанавливает три детерминированные статьи. Удаление самой тестовой базы требует отдельного подтверждения:

```bash
make test-db-drop CONFIRM=mvc_v1_test
```

## Тесты и статический анализ

Основные проверки:

```bash
make test
make test-dox
make analyse
make check
```

- `make test` запускает PHPUnit;
- `make test-dox` выводит те же сценарии в читаемом TestDox-формате;
- `make analyse` запускает PHPStan;
- `make check` выполняет Composer validation, Composer audit, PHPStan и PHPUnit.

Запустить произвольную PHP- или Composer-команду внутри контейнера можно так:

```bash
make php CMD="-v"
make composer CMD="validate"
```

## Покрытие

Покрытие является диагностическим инструментом, а не публичным KPI проекта.

Текстовый отчёт и Clover XML:

```bash
make coverage
```

Clover сохраняется в:

```text
runtime/coverage.xml
```

HTML-отчёт:

```bash
make coverage-html
```

Результат:

```text
runtime/coverage/index.html
```

CI передаёт Clover в Codecov через OIDC. Отдельного требования достигать 100% нет: тесты должны защищать реальное поведение проекта, а не искусственно исполнять defensive/unreachable branches ради процента.

## Runtime smoke

После загрузки 50 demo articles можно запустить:

```bash
make smoke
```

Smoke выполняет настоящий HTTP-сценарий через поднятый Docker stack и проверяет, среди прочего:

- `200` для страниц;
- CSRF failure `403`;
- CRUD redirects `302` и `Location`;
- `405 Method Not Allowed` с `Allow: POST`;
- `404` после удаления статьи;
- восстановление исходного количества demo rows.

Это дополняет PHPUnit: unit/integration tests работают внутри процесса, а smoke проверяет связку Nginx → PHP-FPM → приложение → PostgreSQL.

## GitHub Actions

Workflow [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) запускается для push и pull request.

В чистом runner он:

1. создаёт локальное окружение;
2. проверяет Docker Compose config;
3. собирает и запускает Docker stack;
4. устанавливает зависимости из lock;
5. проверяет embedded framework Composer manifest;
6. выполняет PHP syntax lint;
7. запускает `make check`;
8. генерирует coverage и отправляет Clover в Codecov;
9. загружает demo data;
10. проверяет PostgreSQL;
11. запускает runtime smoke;
12. удаляет isolated test database и останавливает stack.

CI не выполняет deployment.

## Права на файлы

Write-producing PHP/Composer-команды выполняются в контейнере от пользователя `app`, связанного с host UID/GID через `.env.docker`.

Поэтому штатная работа через Make не должна создавать root-owned файлы в checkout. Исправлять права массовыми `chmod`/`chown` для обычного цикла разработки не требуется.

## Запуск без Docker

Docker Compose — рекомендуемый и воспроизводимый способ запуска, но приложение можно настроить вручную.

Для этого понадобятся:

- PHP 8.5 с `mbstring`, `pdo` и `pdo_pgsql`;
- Composer;
- PostgreSQL;
- Nginx или другой веб-сервер с document root на `public/`.

Используйте [`.env.example`](../.env.example) как шаблон окружения.

Схема и demo data находятся в:

- [`docs/db_schema_only.sql`](db_schema_only.sql);
- [`docs/db_demo_data.sql`](db_demo_data.sql).

Пример минимальной конфигурации Nginx + PHP-FPM: [`docs/conf/nginx-configuration.conf`](conf/nginx-configuration.conf). Перед использованием в нём нужно адаптировать `server_name`, `root` и `fastcgi_pass` под свою систему.

Для production-развёртывания этот пример сам по себе недостаточен: TLS, process supervision, секреты, backups и эксплуатационные политики находятся вне scope учебного проекта.
