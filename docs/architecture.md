# Архитектура

`mvc-v1` — учебное серверное PHP-приложение с небольшим собственным MVC-ядром. Архитектура намеренно остаётся прямой: маршрутизатор определяет сценарий, контроллер координирует HTTP-запрос, модель работает с данными, представление рендерит страницу, а финальный HTTP-ответ отправляется в одной точке.

Проект не задуман как универсальный framework и не пытается воспроизводить полный набор возможностей Symfony, Laravel или других production-решений.

## Общий поток запроса

```text
HTTP-запрос
    ↓
Router
    ↓
Track
    ↓
Dispatcher
    ↓
Controller action
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php
    ↓
status + headers + body
```

Front controller [`public/index.php`](../public/index.php) загружает конфигурацию и маршруты, нормализует URI, получает `Track` от `Router` и передаёт его в `Dispatcher`.

Если контроллер возвращает `Page`, она рендерится через `View`. Если контроллер уже вернул `Response` или `RedirectResponse`, рендеринг не выполняется. В обоих случаях front controller получает готовый `Response` и только после этого выставляет HTTP status, заголовки и тело.

## Router и Track

[`Router`](../core/src/Router.php) сопоставляет путь запроса с маршрутами из [`config/routes.php`](../config/routes.php).

При разборе URI:

- query string не участвует в выборе маршрута;
- именованные сегменты маршрута превращаются в параметры;
- найденные controller/action/params упаковываются в `Track`;
- если маршрут не найден, создаётся `Track` для `ErrorController::notFound()`.

`Router` не создаёт контроллеры и не выполняет HTTP side effects.

## Dispatcher

[`Dispatcher`](../core/src/Dispatcher.php) отвечает за переход от `Track` к controller action.

Он:

1. проверяет допустимый формат имени контроллера и action;
2. проверяет `APP_NAMESPACE`;
3. подключает файл контроллера;
4. убеждается, что класс и action существуют и доступны;
5. вызывает action с параметрами маршрута;
6. принимает только `Page` или `Response`.

Нормальный HTTP-результат не выражается исключением. Контроллер может вернуть страницу, обычный ответ или redirect.

Ошибки подключения контроллера, некорректный action и другие реальные сбои `Dispatcher` логирует в `logs/dispatcher-errors.txt` и преобразует в безопасный `Response` со статусом `500`.

## Page, Response и RedirectResponse

[`Page`](../core/src/Page.php) — описание страницы, которую ещё нужно отрендерить. Она содержит:

- layout;
- meta-данные;
- имя view;
- данные view;
- HTTP status для итоговой страницы.

Например, `ErrorController` возвращает обычную `Page` со статусом `404`, не выставляя HTTP status глобально из контроллера.

[`Response`](../core/src/Response.php) — готовый HTTP-результат: body, status и набор заголовков. Класс проверяет диапазон HTTP status и валидирует имена/значения заголовков, включая защиту от управляющих символов и case-insensitive duplicate headers.

[`RedirectResponse`](../core/src/RedirectResponse.php) специализирует `Response` для redirect: содержит `Location`, пустое тело и разрешает redirect-status `301`, `302`, `303`, `307` и `308`.

Так redirect, `403 Forbidden` и `405 Method Not Allowed` остаются обычными результатами выполнения action, а исключения используются только для настоящих ошибок.

## Контроллеры

Базовый [`Controller`](../core/src/Controller.php) помогает создавать `Page` через `render()` и содержит общие вспомогательные операции пагинации.

Основной пример находится в [`ArticleController`](../app/Controllers/ArticleController.php):

- `all()` и `show()` возвращают `Page`;
- `create()`, `edit()` и `delete()` возвращают `Page|Response`;
- успешные POST-операции возвращают `RedirectResponse`;
- неверный CSRF token возвращает `Response('Forbidden', 403)`;
- попытка удалить статью не методом POST возвращает `405` с заголовком `Allow: POST`;
- отсутствие статьи возвращает renderable `404 Page`.

Контроллер не отправляет HTTP-заголовки напрямую и не завершает PHP-процесс через `exit`.

## Модель и PostgreSQL

Базовый [`Model`](../core/src/Model.php) открывает PDO-соединение с PostgreSQL по переменным окружения.

Соединение настроено с:

- `PDO::ERRMODE_EXCEPTION`;
- `PDO::FETCH_ASSOC`;
- отключёнными emulated prepares.

Запросы выполняются через подготовленные PDO statements. CRUD статей реализован в [`ArticleModal`](../app/Models/ArticleModal.php).

При `PDOException` техническая информация записывается в `logs/database-errors.txt`, после чего наружу передаётся `DatabaseException` с исходным исключением в `previous`. `Dispatcher` знает, что эта ошибка уже залогирована, поэтому преобразует её в generic `500` без повторной записи тех же деталей.

## View и финальная отправка ответа

[`View`](../core/src/View.php) получает `Page`, рендерит view и layout через output buffering и возвращает готовый `Response`.

Если ошибка возникает во время рендеринга:

- `View` очищает только те output buffers, которые открыл сам;
- техническая причина записывается в лог;
- пользователю возвращается безопасный `500`.

Нормальный HTTP emission выполняется только в [`public/index.php`](../public/index.php):

```text
http_response_code()
→ header()
→ echo response body
```

Это отделяет построение результата от его фактической отправки.

## Валидация, CSRF и вывод данных

[`ArticleValidate`](../app/Validations/ArticleValidate.php) проверяет данные статьи на сервере. При ошибке формы контроллер сохраняет введённые значения и сообщения в session state, после чего возвращает redirect.

[`SecurityHelper`](../app/Helper/SecurityHelper.php) отвечает за CSRF token и проверку POST-запросов. Изменяющие операции `create`, `edit` и `delete` проверяют CSRF до записи в PostgreSQL.

Динамические значения при выводе экранируются. Поле содержимого статьи хранится как текст и преобразует переводы строк при отображении, а не принимает произвольный HTML от пользователя.

## Границы ошибок

В проекте разделены нормальные HTTP outcomes и реальные failures:

```text
redirect / 403 / 405
    → Response value
    → без error logging

PDO failure
    → database log
    → DatabaseException
    → sanitized 500

controller/dispatcher failure
    → dispatcher log
    → sanitized 500

view/layout failure
    → view log
    → sanitized 500
```

Технические сообщения исключений не должны попадать в HTML-ответ пользователю.

## Что сознательно не усложнялось

Проект остаётся небольшим, поэтому в нём нет дополнительных слоёв только ради архитектурной симметрии:

- нет DI/service container;
- нет ORM;
- нет repository abstraction поверх каждой модели;
- нет middleware pipeline;
- нет event bus;
- нет фоновых jobs;
- нет отдельного REST API или SPA.

Если бы приложение развивалось как production-система, часть этих границ могла бы стать полезной. В учебном примере они только скрыли бы основной MVC-поток за инфраструктурой.

Практические команды запуска, тестовую базу, coverage и CI см. в [руководстве по разработке](development.md).
