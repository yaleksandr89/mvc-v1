# AGENTS.md

## Scope and purpose

- `mvc-v1` is an educational server-rendered PHP application demonstrating a small custom MVC framework and PostgreSQL article CRUD.
- This root contract covers the application, embedded `core/` library, configuration, PHP views and tests. `core/` is a Composer path dependency, not a separate Git repository or independently released package.
- Preserve the educational purpose, existing routes, visible request flow, public setup, HTTP behavior and persisted data unless the owner approves a specific change.
- Read the relevant source, configuration and tests before editing. Keep each approved task coherent and within its exact file allowlist.

## Runtime and dependencies

- Current development and CI runtime: PHP 8.5 FPM, PostgreSQL 18.4, Nginx and Docker Compose. Both Composer manifests currently declare `^8.5`; this does not prove compatibility with a newer PHP minor.
- `composer.json` is type `project`; keep the committed `composer.lock` consistent with intentional dependency changes. `core/composer.json` declares `yaa/mvc-framework` and autoloads `Yaa\\Framework\\` from `core/src/`; the application autoloads `App\\` from `app/`.
- Do not change PHP support, runtime images, dependencies, the embedded package name or namespaces without a specific approved decision. Verify trusted sources, maintenance, compatibility, security and necessity before adding dependencies.
- Preserve the supported manual setup without Docker. Do not introduce a framework, ORM, DI container, service locator or extra layers simply for uniformity.

## Architecture and behavior

- `public/index.php` is the HTTP entry point and bootstrap; it loads `config/app.php`, environment, Composer autoload and `config/routes.php`.
- Preserve `Router → Track → Dispatcher → Controller → Page | Response`; `View` renders `Page` into `Response`. Only the entry point sends HTTP status, headers and body.
- Keep SQL in the existing model boundary and presentation in presenters/views. Use native types and explicit failure semantics. Expected HTTP outcomes are responses, not exceptions; technical failures must not leak details into HTTP responses.
- The existing MVC core uses a singleton model and shared PDO connection. Treat these as current architecture, not a pattern to expand. Do not introduce new hidden dependencies; prefer explicit injection for new replaceable dependencies. Establish the composition root and agree on any architectural migration before changing these boundaries.
- Do not change database schema, session semantics, route contracts, public setup or user-visible behavior as an incidental cleanup. Published application releases and existing user data require an explicit compatibility/migration decision for externally observable changes.

## PHP style

- For new or substantively changed first-party PHP, use PHP-FIG PER Coding Style 3.1 with PSR-1 as the normative baseline. Do not claim that all existing code already conforms.
- Use UTF-8, LF, one final newline, four spaces, `declare(strict_types=1);`, native types, explicit visibility, strict comparisons, short arrays, English identifiers and braces for control structures.
- Follow PER-CS layout for methods, multiline parameter lists and trailing commas; use compact `{}` for genuinely empty bodies. Prefer direct `new Foo()->method()` and imports for global classes where useful.
- Prefer `final` for non-extension leaf classes and `readonly` for immutable state when appropriate. Do not introduce inheritance, patterns or abstractions without a real use case.
- Meaningful first-party comments and PHPDoc prose use `RU:` first, then `EN:` with equal meaning. Group all Russian lines before all English lines; machine-readable tags and type shapes are written once. Avoid comments that restate code or native signatures.
- Existing `.editorconfig` governs non-PHP files. PHP CS Fixer enforces the configured `@PER-CS` baseline, ordered and used imports, global class imports, direct `new` dereferencing and compact empty bodies. PHP-FIG PER-CS 3.1 and PSR-1 remain the review standard for requirements beyond the formatter rules. Use `composer cs:check` or `make cs-check` to check style and `composer cs:fix` or `make cs-fix` to apply it. Introduce or change automated style enforcement only in an approved tooling task. Do not reformat unrelated code or third-party frontend assets. Resolve conflicts between the normative standard, existing code and executable tooling explicitly.
- Approved template exception to PER-CS 3.1 section 3: in mixed PHP/HTML views, keep `declare(strict_types=1);`, imports, PHPDoc and any other initial PHP statements in a single PHP block when they precede the markup. Do not close and reopen PHP merely to isolate the declaration. Follow the configured formatter for whitespace within the block. If the declaration is the only statement before markup, the one-line opening and closing tag form is permitted. Declare strict types exactly once per template.

## Security and data

- Treat request paths, query/form data and session state as untrusted. Preserve route/action restrictions, CSRF validation before mutations, HTTP method checks, server-side validation and HTML escaping.
- Preserve parameterized SQL for untrusted values; do not interpolate untrusted SQL fragments or identifiers. Keep safe response/error boundaries and avoid duplicate or sensitive logging.
- The application is an educational CRUD demo; CSRF protection is not authentication or authorization. Do not imply it is suitable for untrusted public deployment without a separate security decision.
- Never expose real `.env` files, database credentials, tokens, cookies, session IDs, private data or production logs. Use synthetic fixtures.
- Before changing a security-sensitive boundary, identify the risk, review applicable failure/abuse cases and add meaningful regression tests. Stop for any new security or product decision.

## Runtime ownership and tests

- Use existing Make targets from the repository root. Inspect targets and environment before write-producing commands. PHP/Composer processes in Docker must run as `app`, mapped to the host UID/GID; do not use root for generated project files.
- Preserve existing generated paths (`runtime/`, `logs/`, `vendor/`), including `runtime/cache/phpunit`, `runtime/cache/php-cs-fixer.cache` and `runtime/coverage.xml`. Do not relocate them without synchronizing all affected runtime, CI and documentation contracts.
- `make composer-install` installs the locked dependencies. `make analyse` runs PHPStan (currently level 8); `make test` runs PHPUnit; `make check` resets the test database and then runs strict Composer validation, locked audit, PHP CS Fixer, PHPStan and PHPUnit. The standalone style targets do not reset the database.
- `make test`, `make test-dox`, `make check`, `make coverage` and `make coverage-html` reset the isolated `mvc_v1_test` database. Verify the disposable local target and test database safeguards before running them. Never run them against production or an unverified shared PostgreSQL server.
- `make smoke` requires the local application and its expected demo dataset; it exercises the real HTTP path. `make db-check` inspects PostgreSQL. Do not seed, reset or drop databases or volumes without an approved isolated target and required confirmation. `make postgres-reinit` destroys the local PostgreSQL volume.
- Maintain deterministic tests for relevant behavior, security boundaries and regressions; preserve PHPUnit bootstrap safeguards and the existing coverage/Codecov contract. Use focused checks during edits and the relevant aggregate gate before closure. Do not invent test results.

## Documentation and Git

- Russian is the primary human-facing documentation language; preserve existing English, Spanish, Chinese, French and German README/CONTRIBUTING/SECURITY translations and their navigation. Update affected translations when changing their source contract.
- Keep README concise and preserve its real commands, cover, language switcher, links and documented limitations. Technical details belong in the existing guides. Do not publish invented output or remove useful examples without approval.
- Apply changes only within the approved scope; preserve user edits. Review the full diff and relevant checks, including `git diff --check` or `git diff --cached --check` for staged changes.
- Remote policy: `READ_ONLY_FOR_AI`. The owner performs commits, pushes, PR creation/merge, settings, signed tags and releases. No autonomous commit, push, merge, rebase, reset, restore, clean, stash, force operations, branch/tag deletion, production changes or broad permission/file deletion.
- Read-only tasks must not modify project or generated state. Stop when required evidence is unavailable, the allowlist must expand, a compatibility/security/architecture decision is needed, or a runtime target is unsafe.
- Report actual changed paths, full diff, performed checks and their results; distinguish unperformed checks from PASS. Correct all in-scope findings together before acceptance. Never claim a checkpoint or release is complete without evidence.
