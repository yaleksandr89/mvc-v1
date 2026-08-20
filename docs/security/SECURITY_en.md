# Security

## Choose a language

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/SECURITY.md) | **English** | [Español](SECURITY_es.md) | [中文](SECURITY_zh.md) | [Français](SECURITY_fr.md) | [Deutsch](SECURITY_de.md) |

Please report potential vulnerabilities responsibly. `mvc-v1` is an educational MVC project, but security problems in routing, CRUD, PostgreSQL access, user-input handling, and data rendering are treated as seriously as in any public repository.

## What is better reported privately

- A CSRF-protection bypass when creating, editing, or deleting an article.
- SQL injection or an ability to influence SQL identifiers or query parameters.
- Stored or reflected XSS, HTML-escaping bypass, or unsafe output of user data.
- A way to replace a route/path parameter through the query string or otherwise act on another entity.
- Disclosure of internal paths, exceptions, configuration, `.env` contents, PostgreSQL credentials, or other sensitive information.
- A session, CSRF-token, or HTTP-method handling flaw that enables an unwanted state change.
- An exploitable dependency issue that materially affects this project.
- Compromise of source code, CI, the dependency chain, or another supply-chain element.

## What may be published in Issues

- A routing error without a security impact.
- Incorrect CRUD behavior or validation error without a security impact.
- A pagination, rendering, or interface problem.
- A PHP or PostgreSQL compatibility problem without disclosure of sensitive data.
- A documentation error.
- A request for a new feature or improvement.

If you are unsure whether an issue affects security, use the private channel first.

## How to report

- If the repository Security section provides a private vulnerability-reporting form, use it first.
- Do not publish exploit code, real secrets, production `.env` contents, PostgreSQL passwords, session identifiers, or other sensitive data in Issues, Discussions, Pull Requests, or logs.
- If the private form is temporarily unavailable, create a minimal public Issue without exploitation details and ask for a private contact channel.
- Do not disclose technical exploitation details publicly until agreeing with the maintainer and a fix is available.

## What to include

Where possible, provide:

- the affected version or commit SHA;
- the PHP version;
- the PostgreSQL version when the issue concerns the database;
- the affected area: routing, controller, model/PDO, validation, view/output, session/CSRF, configuration, or dependency chain;
- the impact;
- minimal reproduction steps;
- a sanitized request, response, or log excerpt when useful.

Use synthetic data only. Never include real passwords, tokens, cookies, session IDs, production `.env` contents, or other secrets.

## What happens next

- The project has one maintainer; no guaranteed SLA exists.
- The maintainer will try to acknowledge the report, reproduce the problem, assess its impact, and prepare a fix.
- No bug-bounty program is promised.
- Coordinate public disclosure until a fix is available.
