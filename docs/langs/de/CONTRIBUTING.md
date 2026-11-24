# Zu mvc-v1 beitragen

## Sprache wählen

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../../.github/CONTRIBUTING.md) | [English](../en/CONTRIBUTING.md) | [Español](../es/CONTRIBUTING.md) | [中文](../zh/CONTRIBUTING.md) | [Français](../fr/CONTRIBUTING.md) | **Deutsch** |

Danke für Ihr Interesse an `mvc-v1`. Es ist ein pädagogisches PHP-MVC-Projekt mit einem kleinen eigenen Framework.

## Bevor Sie beginnen

Prüfen Sie vorhandene Issues und Pull Requests und halten Sie die Arbeit klar und fokussiert. Melden Sie sicherheitsrelevante Themen über die [Sicherheitsrichtlinie](../../../.github/SECURITY.md), nicht über ein öffentliches Issue.

## Projektvertrag

Bewahren Sie den pädagogischen Zweck und den sichtbaren Ablauf `Router → Dispatcher → Controller → Model/View`, Artikel-CRUD sowie PostgreSQL-Verhalten über PDO. Die PHP-Basis ist 8.5; die Architektur ist bewusst klein und framework-unabhängig.

Ersetzen Sie das Projekt nicht durch Symfony oder Laravel und fügen Sie ohne konkreten Bedarf keine DI/container-, ORM- oder repository/service-Schichten hinzu. Vermeiden Sie breite Refactorings ohne Bezug zum Issue.

## Branches

Erstellen Sie einen fokussierten Branch vom aktuellen Ziel-Branch. Verwenden Sie einen kurzen beschreibenden Namen wie `fix/article-validation` oder `docs/contributing`.

## Commits

Conventional Commits werden empfohlen: `fix(router): preserve path parameters`, `feat(articles): validate excerpt`, `docs: clarify local checks`. Halten Sie Commits klein, zusammenhängend und klar beschrieben.

## Lokale Prüfungen

Bei der Arbeit mit Docker Compose verwenden Sie `make composer-install`, `make check`, `make cs-check` und `make cs-fix`. `make check` setzt die isolierte Testdatenbank `mvc_v1_test` zurück; bei direkter Ausführung von Composer bereiten Sie diese isolierte Datenbank vor den Tests selbst vor.

```shell
composer install
composer check
```

Für gezielte Prüfungen verwenden Sie:

```shell
composer cs:check
composer test
composer analyse
```

`composer check` führt nacheinander die strikte Composer-Validierung, die Sicherheitsprüfung der Abhängigkeiten aus `composer.lock`, die Stilprüfung mit PHP CS Fixer, PHPStan und PHPUnit aus.

Um Stilabweichungen zu korrigieren, verwenden Sie `composer cs:fix`: Der Befehl ändert PHP-Dateien. Die einzelnen Befehle `composer cs:check` und `composer cs:fix` benötigen keine Datenbankverbindung.

Beschreiben Sie bei Änderungen am Runtime- oder Datenbankverhalten die durchgeführte Prüfung.

## Pull Request

Erklären Sie, was sich geändert hat, warum und wie es geprüft wird. Beschränken Sie einen PR auf eine Aufgabe. Aktualisieren Sie relevante Dokumentation bei Änderungen an öffentlichem Setup oder Verhalten; synchronisieren Sie Übersetzungen bei Richtlinienänderungen.

## Abschließende Checkliste und Sicherheitshygiene

- Keine Geheimnisse, produktive `.env`, Tokens, Cookies oder personenbezogenen Daten.
- `composer check` ist erfolgreich.
- Bei Verhaltensänderungen werden paketbezogene Regressionstests ergänzt oder aktualisiert.
- Zweck und kleine Architektur des eigenen MVC bleiben bewusst erhalten.
- Runtime-/Datenbankprüfung wird bei Bedarf beschrieben.
- Dokumentation und Richtlinienübersetzungen werden bei Bedarf aktualisiert.
