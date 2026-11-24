# Entwicklung

## Voraussetzungen

Für die empfohlene Ausführung mit Docker Compose werden auf dem Host benötigt:

- Git;
- Make;
- Docker mit Compose-Unterstützung.

PHP, Composer, PHPUnit, PHPStan und PHP CS Fixer laufen im PHP-Container. Eine separate Installation auf dem Host ist nicht erforderlich.

Docker ist nicht zwingend erforderlich: Das Projekt kann auch in einer herkömmlichen Umgebung mit PHP, Composer, PostgreSQL und einem Webserver betrieben werden. Diese Variante wird im Abschnitt [Start ohne Docker](#start-ohne-docker) separat beschrieben.

## Erster Start

| Befehl | Funktion | Hinweis |
|---|---|---|
| `make init` | Erstellt `.env.docker`, `logs/` und `runtime/cache/` | Erstellt `.env.docker` nur, wenn die Datei fehlt |
| `make build` | Baut das PHP-Docker-Image | |
| `make up` | Startet Nginx, PHP-FPM und PostgreSQL | Wartet auf die Bereitschaft der Dienste |
| `make composer-install` | Installiert Abhängigkeiten aus `composer.lock` | Läuft im PHP-Container |
| `make demo-data` | Lädt 50 Demo-Artikel | Nur in eine leere Tabelle `blog_posts` |
| `make db-check` | Zeigt die PostgreSQL-Version und den Zustand von `blog_posts` | Nützliche Prüfung nach dem ersten Start |

Nach dem Start ist die Anwendung standardmäßig unter `http://localhost:8080` erreichbar.

Die Umgebung lässt sich mit `make down` stoppen: Die Container werden angehalten, das PostgreSQL-Volume bleibt erhalten.

## Docker Compose

[`compose.yaml`](../../../compose.yaml) startet drei Dienste:

- `php` — PHP 8.5 FPM und Entwicklungswerkzeuge;
- `nginx` — Nginx 1.30.4;
- `postgres` — PostgreSQL 18.4.

Der PHP-Container wird aus [`docker/php/Dockerfile`](../../../docker/php/Dockerfile) gebaut. Der Benutzer `app` erhält die UID/GID des Host-Benutzers, sodass `vendor`, Berichte und andere im Container erstellte Dateien nicht `root` gehören sollten.

Die vollständige Liste der Befehle zur Verwaltung der Umgebung steht unter [Alle Make-Befehle](#alle-make-befehle).

## Umgebungsvariablen

Docker verwendet `.env.docker`, das aus [`.env.docker.example`](../../../.env.docker.example) erstellt wird.

Wichtige Einstellungsgruppen:

- `HOST_UID`, `HOST_GID` — UID/GID des Host-Benutzers;
- `APP_PORT` — externer HTTP-Port, standardmäßig `8080`;
- `DB_FORWARD_PORT` — PostgreSQL-Port auf dem Host;
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` — lokale PostgreSQL-Einstellungen;
- `APP_ENV`, `APP_NAMESPACE`, `APP_TIMEZONE` — Anwendungseinstellungen;
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — Datenbankverbindung der Anwendung.

Die Werte in der Beispieldatei sind nur für die lokale Entwicklung bestimmt. Echte Geheimnisse gehören nicht in Git.

Für den manuellen Start ohne Docker erstellen Sie eine `.env` im Projektstamm auf Basis von [`.env.example`](../../../.env.example). Die Anwendung lädt diese Datei selbst, ohne externe dotenv-Bibliothek. Bereits gesetzte Prozessumgebungsvariablen und Werte in `$_ENV` haben Vorrang und werden durch `.env` nicht überschrieben.

Die `.env` im Projektstamm ist für das einfache Format `KEY=VALUE` ausgelegt; die erweiterte Syntax von dotenv-Bibliotheken wird nicht verwendet.

## PostgreSQL und Demo-Daten

Beim ersten Start eines neuen PostgreSQL-Volumes wird das Schema aus [`docs/db_schema_only.sql`](../../db_schema_only.sql) erstellt. Zur Demonstration lassen sich 50 Artikel aus [`docs/db_demo_data.sql`](../../db_demo_data.sql) laden.

| Befehl | Funktion | Hinweis |
|---|---|---|
| `make db-check` | Prüft die PostgreSQL-Version und den Zustand von `blog_posts` | |
| `make demo-data` | Lädt 50 Demo-Artikel | Funktioniert nur mit einer leeren Tabelle `blog_posts` |
| `make postgres-reinit CONFIRM=postgres18` | Erstellt das lokale PostgreSQL-Volume vollständig neu | Löscht die aktuellen lokalen Daten |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Erstellt PostgreSQL neu und lädt sofort 50 Artikel | Löscht die aktuellen lokalen Daten |

> [!IMPORTANT]
> `make demo-data` überschreibt keine vorhandenen Artikel. Ist die Tabelle bereits gefüllt, endet der Befehl mit einem Fehler, ohne Daten zu ändern. Ein vollständiges Zurücksetzen ist nur über `postgres-reinit` mit ausdrücklicher Bestätigung möglich.

## Separate Testdatenbank

PHPUnit arbeitet nicht mit der Hauptdatenbank `mvc_v1`. Für die Tests wird die separate Datenbank `mvc_v1_test` verwendet.

| Befehl | Funktion | Hinweis |
|---|---|---|
| `make test-db-create` | Erstellt `mvc_v1_test`, falls sie noch nicht existiert | Löscht die vorhandene Datenbank nicht |
| `make test-db-reset` | Erstellt das Schema neu und lädt drei Testartikel | Wird vor Tests automatisch verwendet |
| `make test-db-check` | Prüft den Datenbanknamen und den Zustand der Testdaten | Erwartet werden 3 Artikel mit IDs von 1 bis 3 |
| `make test-db-drop CONFIRM=mvc_v1_test` | Löscht nur `mvc_v1_test` | Erfordert eine ausdrückliche Bestätigung |

`make test`, `make test-dox`, `make coverage`, `make coverage-html` und `make check` setzen die Testdatenbank vor der Ausführung selbst zurück.

## Tests, Analyse und Coverage

| Befehl | Funktion | Hinweis |
|---|---|---|
| `make test` | Führt PHPUnit aus | |
| `make test-dox` | Führt PHPUnit mit lesbaren Szenarionamen aus | |
| `make cs-check` | Prüft den PHP-Codestil und zeigt bei Verstößen einen Diff an | Ändert keine Quelldateien und setzt die Testdatenbank nicht zurück |
| `make cs-fix` | Formatiert PHP-Code | Ändert Quelldateien; setzt die Testdatenbank nicht zurück |
| `make analyse` | Führt PHPStan aus | |
| `make check` | Führt Composer-Validierung, Composer-Audit, Stilprüfung mit PHP CS Fixer, PHPStan und PHPUnit aus | Zentrale Gesamtprüfung; setzt vorher die Testdatenbank zurück |
| `make coverage` | Zeigt die Coverage an und erstellt Clover XML | `runtime/coverage.xml` |
| `make coverage-html` | Erstellt einen HTML-Coverage-Bericht | `runtime/coverage/index.html` |
| `make smoke` | Führt eine durchgängige HTTP-Prüfung aus | Benötigt 50 Demo-Artikel |

PHP CS Fixer ist als Entwicklungsabhängigkeit installiert. Regeln und Formatierungsumfang sind in [`.php-cs-fixer.dist.php`](../../../.php-cs-fixer.dist.php) festgelegt: PHP-Code in `app/`, `config/`, `core/src/`, `tests/`, `views/`, die Datei `public/index.php` und die Konfigurationsdatei selbst. Riskante Regeln sind deaktiviert; fremde Frontend-Ressourcen werden nicht formatiert.

Die Review-Grundlage ist PER Coding Style 3.1 und PSR-1; der Formatter wendet den konfigurierten Regelsatz `@PER-CS` und zusätzliche Regeln an. Die vereinbarte Ausnahme für gemischte PHP/HTML-Templates und die übrigen Anforderungen sind in [`AGENTS.md`](../../../AGENTS.md) beschrieben.

Ohne Docker verwenden Sie `composer cs:check` zur Stilprüfung und `composer cs:fix` zur Korrektur. Über Make laufen diese Befehle im PHP-Container als Benutzer `app`. Der Formatter-Cache liegt in `runtime/cache/php-cs-fixer.cache` und wird nicht in Git aufgenommen. Die separaten Prüf- und Formatierungsbefehle benötigen keine Datenbankverbindung.

Coverage dient als Diagnosewerkzeug, nicht als öffentliche Qualitätskennzahl. CI überträgt Clover per OIDC an Codecov. Es gibt keine separate Anforderung, 100 % zu erreichen: Tests sollen das tatsächliche Projektverhalten schützen, nicht für eine Prozentzahl künstlich unerreichbare Zweige ausführen.

## Durchgängige HTTP-Prüfung

`make smoke` führt ein echtes HTTP-Szenario in der laufenden Docker-Umgebung aus und prüft:

- `200` für normale Seiten;
- `403` bei ungültigem CSRF-Token;
- `302`-Weiterleitungen und den Header `Location`;
- `405 Method Not Allowed` und den Header `Allow: POST`;
- `404` nach dem Löschen eines Artikels;
- die Wiederherstellung der ursprünglichen Anzahl der Demo-Datensätze.

Diese Prüfung ergänzt PHPUnit: Die Tests prüfen Code und Integration innerhalb des PHP-Prozesses, während `make smoke` den gesamten Weg Nginx → PHP-FPM → Anwendung → PostgreSQL durchläuft.

## GitHub Actions

Der Workflow [`.github/workflows/ci.yml`](../../../.github/workflows/ci.yml) läuft für Pull Requests an `master`, bei Pushes auf `master` und manuell über Actions → CI → Run workflow.

In einer sauberen GitHub-Actions-Umgebung führt er Folgendes aus:

1. erstellt die lokale Umgebung;
2. prüft die Docker-Compose-Konfiguration;
3. baut und startet die Container;
4. installiert Abhängigkeiten aus `composer.lock`;
5. validiert die Composer-Datei des eingebetteten MVC-Kerns;
6. prüft die PHP-Syntax;
7. führt `make check` aus: strikte Composer-Validierung, Abhängigkeitsaudit, Stilprüfung, PHPStan und PHPUnit;
8. erstellt einen Coverage-Bericht und überträgt Clover an Codecov;
9. lädt Demo-Daten;
10. prüft PostgreSQL;
11. führt `make smoke` aus;
12. löscht die Testdatenbank und stoppt die Container.

Dieser Workflow führt kein Deployment der Anwendung durch.

## Alle Make-Befehle

`make` ohne Argumente zeigt die integrierte Hilfe an. Nachfolgend stehen alle Befehle, die im aktuellen [`Makefile`](../../../Makefile) definiert sind.

| Befehl | Funktion | Hinweis |
|---|---|---|
| `make` / `make help` | Zeigt die Befehlshilfe an | |
| `make init` | Erstellt `.env.docker`, `logs/` und `runtime/cache/` | Überschreibt keine vorhandene `.env.docker` |
| `make check-env` | Prüft, ob `.env.docker` und Docker Compose vorhanden sind | |
| `make config` | Prüft die effektive Docker-Compose-Konfiguration | |
| `make build` | Baut das PHP-Docker-Image | |
| `make up` | Startet die Umgebung und wartet auf die Bereitschaft der Dienste | |
| `make down` | Stoppt die Umgebung | Das PostgreSQL-Volume bleibt erhalten |
| `make restart <service>` | Startet den gewählten Dienst neu | `php`, `nginx` oder `postgres` |
| `make ps` | Zeigt den Zustand der Projektcontainer an | |
| `make log <service>` | Zeigt das Protokoll des gewählten Dienstes in Echtzeit an | `php`, `nginx` oder `postgres` |
| `make log-all` | Zeigt die Protokolle aller Dienste in Echtzeit an | |
| `make in <service>` | Öffnet eine Shell im gewählten Container | `php`, `nginx` oder `postgres` |
| `make php CMD="..."` | Führt einen beliebigen PHP-Befehl als Benutzer `app` aus | Beispiel: `make php CMD="-v"` |
| `make composer CMD="..."` | Führt einen beliebigen Composer-Befehl aus | Beispiel: `make composer CMD="validate"` |
| `make composer-install` | Installiert Abhängigkeiten aus `composer.lock` | |
| `make test` | Führt PHPUnit aus | Setzt vorher die Testdatenbank zurück |
| `make test-dox` | Führt PHPUnit im TestDox-Format aus | Setzt vorher die Testdatenbank zurück |
| `make coverage` | Zeigt die Coverage an und erstellt Clover XML | Setzt vorher die Testdatenbank zurück |
| `make coverage-html` | Erstellt einen HTML-Coverage-Bericht | Setzt vorher die Testdatenbank zurück |
| `make cs-check` | Prüft den PHP-Codestil und zeigt bei Verstößen einen Diff an | Ändert keine Quelldateien und setzt die Testdatenbank nicht zurück |
| `make cs-fix` | Formatiert PHP-Code | Ändert Quelldateien; setzt die Testdatenbank nicht zurück |
| `make analyse` | Führt PHPStan aus | |
| `make check` | Führt Composer-Validierung, Composer-Audit, Stilprüfung mit PHP CS Fixer, PHPStan und PHPUnit aus | Setzt vorher die Testdatenbank zurück |
| `make smoke` | Führt eine durchgängige CRUD/CSRF-Prüfung über HTTP aus | Die Hauptdatenbank muss 50 Demo-Artikel enthalten |
| `make db-check` | Zeigt die PostgreSQL-Version und den Zustand von `blog_posts` | |
| `make demo-data` | Lädt 50 Demo-Artikel | Nur in eine leere Tabelle `blog_posts` |
| `make test-db-create` | Erstellt `mvc_v1_test`, falls sie fehlt | |
| `make test-db-reset` | Erstellt Schema und Testdaten neu | Löscht nur das Schema in `mvc_v1_test` |
| `make test-db-check` | Prüft die Testdatenbank | |
| `make test-db-drop CONFIRM=mvc_v1_test` | Löscht `mvc_v1_test` | Erfordert die exakte Bestätigung |
| `make postgres-reinit CONFIRM=postgres18` | Erstellt das lokale PostgreSQL-Volume neu | Löscht lokale Daten der Hauptdatenbank |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Erstellt PostgreSQL neu und lädt 50 Artikel | Löscht lokale Daten der Hauptdatenbank |

## Dateiberechtigungen

PHP- und Composer-Befehle, die Dateien erstellen, laufen im Container als Benutzer `app`, der über `.env.docker` mit der UID/GID des Host-Benutzers verknüpft ist.

Die normale Arbeit über Make sollte daher keine Dateien in der Arbeitskopie erzeugen, die `root` gehören. Flächendeckende `chmod`-, `chown`-Operationen und das Löschen von Verzeichnissen sind für den regulären Entwicklungszyklus nicht erforderlich.

## Start ohne Docker

Docker Compose bleibt die empfohlene und am besten reproduzierbare Startmethode. Falls Docker aus irgendeinem Grund nicht geeignet ist, lässt sich das Projekt auch manuell starten.

Eine eigene Abhandlung über die Installation von PHP-FPM und PostgreSQL sowie die Konfiguration von Nginx gibt es hier nicht: Wer den manuellen Weg gewählt hat, wird die grundlegende Einrichtung dieser Dienste meiner Einschätzung nach bewältigen. Damit man nicht ganz bei null anfangen muss, enthält das Repository eine Beispielkonfiguration für Nginx; die Docker-Konfiguration zeigt die verwendeten PHP-Erweiterungen und Umgebungseinstellungen.

Dafür werden benötigt:

- PHP 8.5 mit `mbstring`, `pdo` und `pdo_pgsql`;
- Composer;
- PostgreSQL;
- Nginx oder einen anderen Webserver mit `public/` als Document Root.

Konfigurieren Sie die `.env` im Projektstamm anhand der Vorlage [`.env.example`](../../../.env.example). Bei Bedarf können Werte über die Systemumgebung oder PHP-FPM gesetzt werden; sie haben Vorrang vor `.env`.

Schema und Demo-Daten befinden sich in:

- [`docs/db_schema_only.sql`](../../db_schema_only.sql);
- [`docs/db_demo_data.sql`](../../db_demo_data.sql).

Beispiel einer minimalen Nginx- + PHP-FPM-Konfiguration: [`docs/conf/nginx-configuration.conf`](../../conf/nginx-configuration.conf). Vor der Verwendung müssen `server_name`, `root` und `fastcgi_pass` an das eigene System angepasst werden.

Für ein produktives Deployment reicht dieses Beispiel nicht aus: TLS, Prozessverwaltung, Geheimnisse, Backups und Betriebsrichtlinien liegen außerhalb des Umfangs des Lernprojekts.
