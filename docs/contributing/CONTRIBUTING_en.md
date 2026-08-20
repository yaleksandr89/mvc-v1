# Contributing to mvc-v1

## Choose a language

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/CONTRIBUTING.md) | **English** | [Español](CONTRIBUTING_es.md) | [中文](CONTRIBUTING_zh.md) | [Français](CONTRIBUTING_fr.md) | [Deutsch](CONTRIBUTING_de.md) |

Thank you for your interest in `mvc-v1`. It is an educational PHP MVC project with a small custom framework.

## Before you start

Check existing Issues and Pull Requests, and keep the proposed work focused and clear. Report security-sensitive matters through the [security policy](../../.github/SECURITY.md), not a public Issue.

## Project contract

Preserve the educational purpose and visible `Router → Dispatcher → Controller → Model/View` flow, article CRUD, and PostgreSQL behavior through PDO. The PHP baseline is 8.5; the architecture is intentionally small and framework-agnostic.

Do not replace the project with Symfony or Laravel, or add DI/container, ORM, repository/service layers without a concrete need. Avoid broad refactoring unrelated to the Issue.

## Branches

Create a focused branch from the current target branch. Use a short descriptive name, such as `fix/article-validation` or `docs/contributing`.

## Commits

Conventional Commits are recommended: `fix(router): preserve path parameters`, `feat(articles): validate excerpt`, `docs: clarify local checks`. Keep commits small, coherent, and clearly described.

## Local checks

```shell
composer install
composer check
```

For targeted checks, use:

```shell
composer test
composer analyse
```

There is no formatter or code-style command yet. When changing runtime or database behavior, describe the verification performed.

## Pull Request

Explain what changed, why, and how to verify it. Keep one task per PR. Update relevant documentation when public setup or behavior changes; synchronize translations when policies change.

## Final checklist and security hygiene

- No secrets, production `.env`, tokens, cookies, or personal data.
- `composer check` passes.
- Package-owned regression tests are added or updated when behavior changes.
- The custom MVC purpose and small architecture remain intentional.
- Runtime/database verification is described when relevant.
- Documentation and policy translations are updated when relevant.
