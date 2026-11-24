# Development

## Requirements

For the recommended Docker Compose setup, the host needs:

- Git;
- Make;
- Docker with Compose support.

PHP, Composer, PHPUnit, PHPStan, and PHP CS Fixer run inside the PHP container. There is no need to install them separately on the host.

Docker is not mandatory: the project can also run in a conventional environment with PHP, Composer, PostgreSQL, and a web server. This option is described separately in [Running without Docker](#running-without-docker).

## First startup

| Command | What it does | Notes |
|---|---|---|
| `make init` | Creates `.env.docker`, `logs/`, and `runtime/cache/` | Creates `.env.docker` only if missing |
| `make build` | Builds the PHP Docker image | |
| `make up` | Starts Nginx, PHP-FPM, and PostgreSQL | Waits for services to be ready |
| `make composer-install` | Installs dependencies from `composer.lock` | Runs inside the PHP container |
| `make demo-data` | Loads 50 demo articles | Only into an empty `blog_posts` table |
| `make db-check` | Shows the PostgreSQL version and the state of `blog_posts` | Useful check after the first startup |

After startup, the application is available at `http://localhost:8080` by default.

Use `make down` to stop the environment: the containers are stopped and the PostgreSQL volume is preserved.

## Docker Compose

[`compose.yaml`](../../../compose.yaml) starts three services:

- `php` — PHP 8.5 FPM and development tools;
- `nginx` — Nginx 1.30.4;
- `postgres` — PostgreSQL 18.4.

The PHP container is built from [`docker/php/Dockerfile`](../../../docker/php/Dockerfile). The `app` user receives the host user's UID/GID, so `vendor`, reports, and other files created inside the container should not become owned by `root`.

The full list of environment management commands is in [All Make commands](#all-make-commands).

## Environment variables

Docker uses `.env.docker`, created from [`.env.docker.example`](../../../.env.docker.example).

Main configuration groups:

- `HOST_UID`, `HOST_GID` — host user's UID/GID;
- `APP_PORT` — external HTTP port, `8080` by default;
- `DB_FORWARD_PORT` — PostgreSQL port on the host;
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` — local PostgreSQL settings;
- `APP_ENV`, `APP_NAMESPACE`, `APP_TIMEZONE` — application settings;
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — application's database connection.

Values in the example file are intended for local development only. Do not commit real secrets to Git.

For manual startup without Docker, create a root `.env` based on [`.env.example`](../../../.env.example). The application loads this file itself without a third-party dotenv library. Existing process environment variables and values in `$_ENV` take precedence and are not overwritten by `.env` values.

The root `.env` is designed for the simple `KEY=VALUE` format; the project does not use the extended syntax of dotenv libraries.

## PostgreSQL and demo data

On the first startup of a new PostgreSQL volume, the schema is created from [`docs/db_schema_only.sql`](../../db_schema_only.sql). For demonstration, you can load 50 articles from [`docs/db_demo_data.sql`](../../db_demo_data.sql).

| Command | What it does | Notes |
|---|---|---|
| `make db-check` | Checks the PostgreSQL version and the state of `blog_posts` | |
| `make demo-data` | Loads 50 demo articles | Works only with an empty `blog_posts` table |
| `make postgres-reinit CONFIRM=postgres18` | Completely recreates the local PostgreSQL volume | Deletes current local data |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Recreates PostgreSQL and immediately loads 50 articles | Deletes current local data |

> [!IMPORTANT]
> `make demo-data` does not overwrite existing articles. If the table is already populated, the command fails without changing the data. A full reset is available only through `postgres-reinit` with explicit confirmation.

## Separate test database

PHPUnit does not operate on the main `mvc_v1` database. Tests use a separate `mvc_v1_test` database.

| Command | What it does | Notes |
|---|---|---|
| `make test-db-create` | Creates `mvc_v1_test` if it does not exist yet | Does not delete the existing database |
| `make test-db-reset` | Recreates the schema and loads three test articles | Used automatically before tests |
| `make test-db-check` | Checks the database name and the state of test data | Expects 3 articles with IDs from 1 to 3 |
| `make test-db-drop CONFIRM=mvc_v1_test` | Deletes only `mvc_v1_test` | Requires explicit confirmation |

`make test`, `make test-dox`, `make coverage`, `make coverage-html`, and `make check` reset the test database before running.

## Tests, analysis, and coverage

| Command | What it does | Notes |
|---|---|---|
| `make test` | Runs PHPUnit | |
| `make test-dox` | Runs PHPUnit with readable scenario names | |
| `make cs-check` | Checks PHP style and shows a diff for violations | Does not modify source files or reset the test database |
| `make cs-fix` | Formats PHP code | Modifies source files; does not reset the test database |
| `make analyse` | Runs PHPStan | |
| `make check` | Runs Composer validation, Composer audit, a PHP CS Fixer style check, PHPStan, and PHPUnit | Main combined quality check; resets the test database first |
| `make coverage` | Prints coverage and creates Clover XML | `runtime/coverage.xml` |
| `make coverage-html` | Creates an HTML coverage report | `runtime/coverage/index.html` |
| `make smoke` | Runs an end-to-end HTTP check | Requires 50 demo articles |

PHP CS Fixer is installed as a dev dependency. The rules and formatting scope are defined in [`.php-cs-fixer.dist.php`](../../../.php-cs-fixer.dist.php): PHP code in `app/`, `config/`, `core/src/`, `tests/`, `views/`, the file `public/index.php`, and the configuration file itself. Risky rules are disabled; third-party frontend assets are not formatted.

The review baseline is PER Coding Style 3.1 and PSR-1; the formatter applies the configured `@PER-CS` rule set and additional rules. The agreed exception for mixed PHP/HTML templates and the remaining requirements are described in [`AGENTS.md`](../../../AGENTS.md).

Without Docker, use `composer cs:check` to check style and `composer cs:fix` to fix it. With Make, these commands run in the PHP container as the `app` user. The formatter cache is stored in `runtime/cache/php-cs-fixer.cache` and is not tracked by Git. The separate style-check and formatting commands do not require a database connection.

Coverage is used as a diagnostic tool, not a public quality metric. CI generates a Clover coverage report without sending it to external services. There is no separate requirement to reach 100%: tests should protect the project's actual behavior, not artificially execute unreachable branches for the sake of a percentage.

## End-to-end HTTP check

`make smoke` runs a real HTTP scenario through the running Docker environment and checks:

- `200` for regular pages;
- `403` for an invalid CSRF token;
- `302` redirects and the `Location` header;
- `405 Method Not Allowed` and the `Allow: POST` header;
- `404` after deleting an article;
- restoration of the original number of demo rows.

This check complements PHPUnit: the tests verify code and integration within the PHP process, while `make smoke` follows the complete path Nginx → PHP-FPM → application → PostgreSQL.

## GitHub Actions

The workflow [`.github/workflows/ci.yml`](../../../.github/workflows/ci.yml) runs for pull requests to `master`, on pushes to `master`, and manually via Actions → CI → Run workflow.

In a clean GitHub Actions environment, it:

1. creates the local environment;
2. checks the Docker Compose configuration;
3. builds and starts the containers;
4. installs dependencies from `composer.lock`;
5. validates the Composer file of the embedded MVC core;
6. checks PHP syntax;
7. runs `make check`: strict Composer validation, dependency audit, style check, PHPStan, and PHPUnit;
8. creates a Clover coverage report;
9. loads demo data;
10. checks PostgreSQL;
11. runs `make smoke`;
12. drops the test database and stops the containers.

This workflow does not deploy the application.

## All Make commands

`make` without arguments prints the built-in help. Below are all commands defined by the current [`Makefile`](../../../Makefile).

| Command | What it does | Notes |
|---|---|---|
| `make` / `make help` | Shows command help | |
| `make init` | Creates `.env.docker`, `logs/`, and `runtime/cache/` | Does not overwrite an existing `.env.docker` |
| `make check-env` | Checks for `.env.docker` and Docker Compose | |
| `make config` | Checks the effective Docker Compose configuration | |
| `make build` | Builds the PHP Docker image | |
| `make up` | Starts the environment and waits for services to be ready | |
| `make down` | Stops the environment | Preserves the PostgreSQL volume |
| `make restart <service>` | Restarts the selected service | `php`, `nginx`, or `postgres` |
| `make ps` | Shows the status of the project containers | |
| `make log <service>` | Shows the selected service's log in real time | `php`, `nginx`, or `postgres` |
| `make log-all` | Shows logs from all services in real time | |
| `make in <service>` | Opens a shell in the selected container | `php`, `nginx`, or `postgres` |
| `make php CMD="..."` | Runs an arbitrary PHP command as the `app` user | For example: `make php CMD="-v"` |
| `make composer CMD="..."` | Runs an arbitrary Composer command | For example: `make composer CMD="validate"` |
| `make composer-install` | Installs dependencies from `composer.lock` | |
| `make test` | Runs PHPUnit | Resets the test database first |
| `make test-dox` | Runs PHPUnit in TestDox format | Resets the test database first |
| `make coverage` | Prints coverage and creates Clover XML | Resets the test database first |
| `make coverage-html` | Creates an HTML coverage report | Resets the test database first |
| `make cs-check` | Checks PHP style and shows a diff for violations | Does not modify source files or reset the test database |
| `make cs-fix` | Formats PHP code | Modifies source files; does not reset the test database |
| `make analyse` | Runs PHPStan | |
| `make check` | Runs Composer validation, Composer audit, a PHP CS Fixer style check, PHPStan, and PHPUnit | Resets the test database first |
| `make smoke` | Runs an end-to-end CRUD/CSRF check over HTTP | The main database must contain 50 demo articles |
| `make db-check` | Shows the PostgreSQL version and the state of `blog_posts` | |
| `make demo-data` | Loads 50 demo articles | Only into an empty `blog_posts` table |
| `make test-db-create` | Creates `mvc_v1_test` if it is missing | |
| `make test-db-reset` | Recreates the schema and test data | Deletes only the schema inside `mvc_v1_test` |
| `make test-db-check` | Checks the test database | |
| `make test-db-drop CONFIRM=mvc_v1_test` | Deletes `mvc_v1_test` | Requires exact confirmation |
| `make postgres-reinit CONFIRM=postgres18` | Recreates the local PostgreSQL volume | Deletes local data in the main database |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Recreates PostgreSQL and loads 50 articles | Deletes local data in the main database |

## File permissions

PHP and Composer commands that create files run in the container as the `app` user, linked to the host user's UID/GID through `.env.docker`.

Therefore, normal work through Make should not create files owned by `root` in the working copy. Bulk `chmod`, `chown`, and directory deletion are not required for the regular development cycle.

## Running without Docker

Docker Compose remains the recommended and most reproducible way to run the project. But if Docker is unsuitable for some reason, you can also start the project manually.

This guide does not provide a separate treatise on installing PHP-FPM and PostgreSQL and configuring Nginx: if you have chosen the manual route, I think you can handle the basic setup of these services. To avoid starting entirely from scratch, the repository includes an example Nginx configuration, and the Docker configuration shows the PHP extensions and environment settings used.

You will need:

- PHP 8.5 with `mbstring`, `pdo`, and `pdo_pgsql`;
- Composer;
- PostgreSQL;
- Nginx or another web server with its document root set to `public/`.

Configure the root `.env` using the [`.env.example`](../../../.env.example) template. If necessary, values can be set via the system environment or PHP-FPM: they take precedence over `.env`.

The schema and demo data are located in:

- [`docs/db_schema_only.sql`](../../db_schema_only.sql);
- [`docs/db_demo_data.sql`](../../db_demo_data.sql).

Example of a minimal Nginx + PHP-FPM configuration: [`docs/conf/nginx-configuration.conf`](../../conf/nginx-configuration.conf). Before using it, adapt `server_name`, `root`, and `fastcgi_pass` to your system.

This example is not sufficient for production deployment: TLS, process management, secrets, backups, and operational policies are outside the scope of this educational project.
