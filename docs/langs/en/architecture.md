# Architecture

`mvc-v1` is an educational server-side PHP application with a small custom MVC core. The architecture is deliberately straightforward: the router selects a scenario, the controller coordinates the request, the model works with data, the view builds the page, and the finished HTTP response is sent at a single point.

The project is not intended to be a general-purpose framework and does not attempt to reproduce the full feature set of Symfony, Laravel, or other production-grade solutions.

## Overall request flow

```text
HTTP request
    ↓
Router
    ↓
Track
    ↓
Dispatcher
    ↓
controller method
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php
    ↓
status code + headers + body
```

The entry script [`public/index.php`](../../../public/index.php) loads the configuration and routes, normalizes the URI, obtains a `Track` from the `Router`, and passes it to the `Dispatcher`.

If the controller returns a `Page`, it is passed to `View` to generate HTML. If the controller has already returned a `Response` or `RedirectResponse`, the view is not invoked. In both cases, the entry script receives a finished `Response` and only then sends the status code, headers, and response body.

## Router and Track

[`Router`](../../../core/src/Router.php) matches the request path against the routes in [`config/routes.php`](../../../config/routes.php).

When parsing the URI:

- parameters after `?` do not affect route selection;
- named route segments become parameters;
- the controller name, method name, and parameters are packaged in a `Track`;
- if no route matches, a `Track` is created for `ErrorController::notFound()`.

`Router` neither creates controllers nor sends anything to the client itself.

## Dispatcher

[`Dispatcher`](../../../core/src/Dispatcher.php) is responsible for moving from a `Track` to the appropriate controller method.

It:

1. checks the allowed format of the controller and method names;
2. checks `APP_NAMESPACE`;
3. includes the controller file;
4. ensures that the class and method exist and are accessible;
5. calls the method with the route parameters;
6. accepts only a `Page` or `Response`.

A normal HTTP result is not represented by an exception. The controller returns a page, a regular response, or a `RedirectResponse`.

`Dispatcher` logs controller inclusion errors, inaccessible methods, and other actual failures to `logs/dispatcher-errors.txt` and returns a safe `Response` with status `500` to the user.

## Page, Response and RedirectResponse

[`Page`](../../../core/src/Page.php) describes a page that still needs to be rendered. It contains:

- the layout name;
- metadata;
- the view name;
- view data;
- the HTTP status code for the resulting page.

For example, `ErrorController` returns a regular `Page` with status `404` without changing the global response code from the controller.

[`Response`](../../../core/src/Response.php) represents a finished HTTP response: body, status code, and a set of headers. The class checks the valid range of status codes and validates headers, including protection against control characters and case-insensitive duplicate names.

[`RedirectResponse`](../../../core/src/RedirectResponse.php) specializes `Response` for redirects: it contains a `Location` header, an empty body, and permits codes `301`, `302`, `303`, `307`, and `308`.

Therefore, redirects, `403 Forbidden`, and `405 Method Not Allowed` remain ordinary results of method execution; exceptions are reserved for actual failures.

## Controllers

The base [`Controller`](../../../core/src/Controller.php) helps create a `Page` through `render()` and contains shared pagination logic.

The main example is [`ArticleController`](../../../app/Controllers/ArticleController.php):

- `all()` and `show()` return a `Page`;
- `create()`, `edit()`, and `delete()` return `Page|Response`;
- successful POST operations return a `RedirectResponse`;
- an invalid CSRF token results in `Response('Forbidden', 403)`;
- attempting to delete an article with a method other than POST returns `405` with the `Allow: POST` header;
- a missing article results in a `Page` with status `404`.

The controller does not send HTTP headers directly or terminate the PHP process with `exit`.

## Model and PostgreSQL

The base [`Model`](../../../core/src/Model.php) opens a PDO connection to PostgreSQL using environment variables.

The connection is configured as follows:

- PDO errors are converted into exceptions;
- results are returned as associative arrays by default;
- prepared statement emulation is disabled.

Queries are executed using PDO prepared statements. Article CRUD is implemented in [`ArticleModal`](../../../app/Models/ArticleModal.php).

On a `PDOException`, technical information is logged to `logs/database-errors.txt`, then a `DatabaseException` preserving the original exception is thrown. `Dispatcher` knows the database error has already been logged, so it does not log it again and returns a safe `500` response to the user.

## View and response delivery

[`View`](../../../core/src/View.php) receives a `Page`, renders the view and layout using output buffering, and then returns a finished `Response`.

If an error occurs while building the page:

- `View` clears only the output buffers it opened itself;
- the technical cause is logged;
- the user receives a safe `500` response.

Normal HTTP response delivery takes place only in [`public/index.php`](../../../public/index.php):

```text
http_response_code()
→ header()
→ echo
```

In other words, response construction is separate from actually sending it to the client.

## Validation, CSRF and output escaping

[`ArticleValidate`](../../../app/Validations/ArticleValidate.php) validates article data on the server. On validation failure, the controller stores the entered values and messages in the session, then returns a redirect.

[`SecurityHelper`](../../../app/Helper/SecurityHelper.php) creates and validates the CSRF token. The modifying operations `create`, `edit`, and `delete` perform this check before writing to PostgreSQL.

Dynamic values are escaped on output. Article content is stored as text: line breaks are converted when displayed, and arbitrary user-supplied HTML is not executed.

## Error boundaries

The project distinguishes normal HTTP responses from actual errors:

```text
redirect / 403 / 405
    → regular Response
    → no error log entry

PostgreSQL / PDO error
    → database log entry
    → DatabaseException
    → safe 500 response

controller or Dispatcher error
    → Dispatcher log entry
    → safe 500 response

view or layout error
    → View log entry
    → safe 500 response
```

Technical exception messages must not appear in the HTML response sent to the user.

## What was deliberately kept simple

The project remains small, so it does not introduce additional layers merely for architectural symmetry:

- no dependency container;
- no ORM;
- no separate repository layer over each model;
- no middleware pipeline;
- no event bus;
- no background jobs;
- no separate REST API or SPA.

In a larger application, some of these boundaries could be useful. Here they would only obscure the main MVC flow behind extra infrastructure.

For practical startup commands, the test database, coverage, and CI, see the [development guide](development.md).
