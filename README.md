# MVC на PHP

[![Source Code](https://img.shields.io/badge/source-yaleksandr89%2Fmvc--v1-blue.svg?style=flat-square)](https://github.com/yaleksandr89/mvc-v1)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.4-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.8-7952B3.svg?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

## Выберите язык

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| **Выбран** | [English](docs/langs/README_en.md) | [Español](docs/langs/README_es.md) | [中文](docs/langs/README_zh.md) | [Français](docs/langs/README_fr.md) | [Deutsch](docs/langs/README_de.md) |

Это один из моих первых проектов, где я разбирался с MVC не через готовый фреймворк, а писал небольшое ядро сам. В качестве примера я выбрал простой блог со статьями: маршрутизация, контроллеры, модели, представления и CRUD собраны так, чтобы весь путь запроса можно было проследить по собственному коду.

Я сознательно не хотел превращать проект в конструктор из готовых библиотек. PostgreSQL используется напрямую через нативный PDO, без ORM. Из сторонних PHP-библиотек в рабочем коде нужен только Symfony Dotenv для загрузки переменных окружения; Bootstrap отвечает за интерфейс, а PHPUnit и PHPStan используются при разработке.

> [!NOTE]
> **UPD 2026.** Исходную идею я не менял: это всё тот же небольшой MVC-проект. Я привёл его в порядок под PHP 8.5, обернул локальный запуск в Docker, обновил PostgreSQL и Bootstrap, добавил защиту изменяющих запросов, тесты, статический анализ и сквозную HTTP-проверку. Теперь проект можно поднять несколькими командами, не устанавливая PHP и Composer на хост.

## Возможности

- создание, просмотр, редактирование и удаление статей;
- PostgreSQL через PDO и параметризованные запросы;
- пагинация списка статей;
- серверная валидация с сохранением введённых значений при ошибках;
- CSRF-защита изменяющих запросов;
- сообщения после CRUD-операций;
- экранирование динамического вывода;
- типизированные `Page`, `Response` и `RedirectResponse`;
- Docker Compose с Nginx, PHP-FPM и PostgreSQL;
- PHPUnit, PHPStan, локальные отчёты покрытия и сквозная HTTP-проверка.

## Быстрый старт

Понадобятся Git, Make и Docker с поддержкой Compose.

| Команда | Что делает | Примечание |
|---|---|---|
| `git clone https://github.com/yaleksandr89/mvc-v1.git` | Клонирует репозиторий | |
| `cd mvc-v1` | Переходит в каталог проекта | |
| `make init` | Создаёт `.env.docker` и рабочие каталоги | Обычно выполняется один раз после клонирования |
| `make build` | Собирает Docker-образ PHP | |
| `make up` | Запускает Nginx, PHP-FPM и PostgreSQL | |
| `make composer-install` | Устанавливает зависимости из `composer.lock` | PHP и Composer на хосте не нужны |
| `make demo-data` | Загружает 50 демонстрационных статей | Только в пустую таблицу `blog_posts` |
| `make down` | Останавливает окружение | Данные PostgreSQL сохраняются |

После запуска приложение доступно по адресу [http://localhost:8080](http://localhost:8080).

> [!IMPORTANT]
> `make demo-data` работает только с пустой таблицей `blog_posts`: если в ней уже есть данные, команда остановится и ничего не перезапишет. Полностью пересоздать локальную PostgreSQL и сразу загрузить демонстрационные данные можно командой `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1`; она удаляет локальный том PostgreSQL.

Подробности об окружении, тестовой базе и остальных командах собраны в [руководстве по разработке](docs/development.md).

## Как устроено приложение

```text
HTTP-запрос
    ↓
Router
    ↓
Dispatcher
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php → код состояния + заголовки + тело
```

`Page` описывает страницу, которую ещё нужно отрендерить, а `Response` и `RedirectResponse` представляют уже готовые HTTP-ответы. Код состояния, заголовки и тело отправляются только во входном скрипте `public/index.php`.

Модель статей работает с PostgreSQL напрямую через PDO. Реальные ошибки остаются исключениями внутри приложения, записываются в журнал на своём уровне и преобразуются в безопасный ответ `500` без вывода технических деталей пользователю.

Маршрутизация, границы ответственности и полный путь запроса подробно разобраны в [описании архитектуры](docs/architecture.md).

## Проверки и покрытие

| Команда | Что делает | Примечание |
|---|---|---|
| `make test` | Запускает PHPUnit | Перед запуском сбрасывается отдельная тестовая база |
| `make test-dox` | Запускает PHPUnit с читаемыми названиями сценариев | |
| `make check` | Проверяет Composer, зависимости, PHPStan и PHPUnit | Основная комплексная проверка |
| `make coverage` | Показывает покрытие и создаёт Clover XML | Результат: `runtime/coverage.xml` |
| `make coverage-html` | Создаёт HTML-отчёт покрытия | Результат: `runtime/coverage/index.html` |
| `make smoke` | Проверяет приложение через настоящий HTTP-запрос | Нужны 50 демонстрационных статей |

Тесты используют отдельную базу `mvc_v1_test`. В CI отчёт Clover передаётся в Codecov как диагностический инструмент: покрытие помогает находить слабые места, но не используется как публичный процент качества.

Полный список команд Make и устройство CI приведены в [руководстве по разработке](docs/development.md).

## Что намеренно оставлено простым

- MVC-ядро остаётся небольшим, чтобы основной путь запроса можно было проследить непосредственно по коду;
- отдельный контейнер зависимостей не добавлен;
- ORM и дополнительный слой репозиториев не добавлены, работа с PostgreSQL идёт через PDO;
- отдельная цепочка промежуточных обработчиков и система событий не вводились;
- проект не пытается заменить Symfony, Laravel или другой промышленный фреймворк.

Более подробно эти решения и границы ответственности описаны в [документе об архитектуре](docs/architecture.md).

## Обратная связь

- воспроизводимые ошибки — [GitHub Issues](https://github.com/yaleksandr89/mvc-v1/issues);
- вопросы и идеи — [GitHub Discussions](https://github.com/yaleksandr89/mvc-v1/discussions).

---

<p align="center">
  Если проект оказался полезен, поставьте звезду на GitHub — так его будет проще найти другим разработчикам. 🤘
</p>
