# Contribuir a mvc-v1

## Elija un idioma

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/CONTRIBUTING.md) | [English](CONTRIBUTING_en.md) | **Español** | [中文](CONTRIBUTING_zh.md) | [Français](CONTRIBUTING_fr.md) | [Deutsch](CONTRIBUTING_de.md) |

Gracias por su interés en `mvc-v1`. Es un proyecto educativo PHP MVC con un pequeño framework propio.

## Antes de empezar

Revise los Issues y Pull Requests existentes y mantenga el trabajo propuesto claro y acotado. Informe los asuntos de seguridad mediante la [política de seguridad](../../.github/SECURITY.md), no en un Issue público.

## Contrato del proyecto

Conserve el propósito educativo y el flujo visible `Router → Dispatcher → Controller → Model/View`, el CRUD de artículos y el comportamiento PostgreSQL mediante PDO. La base de PHP es 8.5; la arquitectura es deliberadamente pequeña e independiente de frameworks.

No sustituya el proyecto por Symfony o Laravel ni agregue capas DI/container, ORM o repository/service sin una necesidad concreta. Evite refactorizaciones amplias no relacionadas con el Issue.

## Ramas

Cree una rama enfocada desde la rama objetivo actual. Use un nombre breve y descriptivo, como `fix/article-validation` o `docs/contributing`.

## Commits

Se recomiendan Conventional Commits: `fix(router): preserve path parameters`, `feat(articles): validate excerpt`, `docs: clarify local checks`. Mantenga los commits pequeños, coherentes y claros.

## Comprobaciones locales

```shell
composer install
composer check
```

Para comprobaciones específicas, use:

```shell
composer test
composer analyse
```

Todavía no hay comando de formatter o code-style. Al cambiar el comportamiento del runtime o de la base de datos, describa la verificación realizada.

## Pull Request

Explique qué cambió, por qué y cómo verificarlo. Mantenga una tarea por PR. Actualice la documentación relevante si cambia la instalación o el comportamiento público; sincronice las traducciones si cambian las políticas.

## Lista final e higiene de seguridad

- No incluya secretos, `.env` de producción, tokens, cookies ni datos personales.
- `composer check` debe pasar.
- Agregue o actualice pruebas de regresión del paquete cuando cambie el comportamiento.
- El propósito MVC propio y la arquitectura pequeña siguen siendo intencionales.
- Describa la verificación de runtime/base de datos cuando corresponda.
- Actualice la documentación y las traducciones de políticas cuando corresponda.
