# Вклад в mvc-v1

## Выберите язык

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| **Русский** | [English](https://github.com/yaleksandr89/mvc-v1/blob/master/docs/contributing/CONTRIBUTING_en.md) | [Español](https://github.com/yaleksandr89/mvc-v1/blob/master/docs/contributing/CONTRIBUTING_es.md) | [中文](https://github.com/yaleksandr89/mvc-v1/blob/master/docs/contributing/CONTRIBUTING_zh.md) | [Français](https://github.com/yaleksandr89/mvc-v1/blob/master/docs/contributing/CONTRIBUTING_fr.md) | [Deutsch](https://github.com/yaleksandr89/mvc-v1/blob/master/docs/contributing/CONTRIBUTING_de.md) |

Спасибо за интерес к `mvc-v1`. Это учебный PHP MVC-проект с небольшим самописным фреймворком.

## Перед началом

Проверьте существующие Issues и Pull Requests, опишите задачу небольшим и понятным объёмом. Вопросы, затрагивающие безопасность, сообщайте по [политике безопасности](https://github.com/yaleksandr89/mvc-v1/security/policy), а не в публичном Issue.

## Контракт проекта

Сохраняйте учебную цель и видимый поток `Router → Dispatcher → Controller → Model/View`, CRUD статей и работу PostgreSQL через PDO. Базовая версия PHP — 8.5; архитектура намеренно небольшая и framework-agnostic.

Не заменяйте проект Symfony или Laravel и не добавляйте DI/container, ORM, repository/service-слои без конкретной необходимости. Избегайте широкого рефакторинга, не связанного с Issue.

## Ветки

Создавайте тематическую ветку от актуальной целевой ветки. Имя должно кратко отражать изменение, например `fix/article-validation` или `docs/contributing`.

## Коммиты

Рекомендуются Conventional Commits: `fix(router): preserve path parameters`, `feat(articles): validate excerpt`, `docs: clarify local checks`. Делайте небольшие, логически цельные коммиты с понятным сообщением.

## Локальные проверки

```shell
composer install
composer check
```

Для целевой проверки используйте:

```shell
composer test
composer analyse
```

Команды форматирования или code-style в проекте пока нет. Если изменение затрагивает runtime или базу данных, опишите выполненную проверку.

## Pull Request

Опишите, что изменилось и зачем, а также как это проверить. Ограничивайте PR одной задачей. При изменении публичной установки или поведения обновите относящуюся документацию; при изменении политик синхронизируйте переводы.

## Финальный чек-лист и безопасность

- Нет секретов, production `.env`, токенов, cookies или персональных данных.
- `composer check` проходит.
- При изменении поведения добавлены или обновлены относящиеся к пакету регрессионные тесты.
- Назначение и небольшая архитектура custom MVC сохранены.
- Проверка runtime/базы данных описана, если применимо.
- Документация и переводы политик обновлены, если применимо.
