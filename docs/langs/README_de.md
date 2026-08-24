# MVC in PHP

[![Source Code](https://img.shields.io/badge/source-yaleksandr89%2Fmvc--v1-blue.svg?style=flat-square)](https://github.com/yaleksandr89/mvc-v1)
[![CI](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml/badge.svg)](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.4-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](../../LICENSE.md)

<p align="center">
  <img
    src="../img/mvc-v1-readme-cover.png"
    alt="MVC in PHP — Lernblog mit eigenem MVC-Kern, PostgreSQL und Docker Compose"
    width="100%"
  >
</p>

## Sprache auswählen

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../README.md) | [English](./README_en.md) | [Español](./README_es.md) | [中文](./README_zh.md) | [Français](./README_fr.md) | **Ausgewählt** |

Dies ist eines meiner ersten Projekte mit einer selbst geschriebenen MVC-Implementierung, ohne auf ein fertiges Framework zurückzugreifen. Als Beispiel dient ein einfacher Artikel-Blog: Routing, Controller, Modelle, Views und CRUD sind so aufgebaut, dass sich der vollständige Weg einer Anfrage direkt im eigenen Projektcode nachvollziehen lässt.

Das Projekt verzichtet bewusst darauf, zu einer Sammlung fertiger Bibliotheken zu werden. PostgreSQL wird direkt über PDO verwendet, ohne ORM. Im Produktivcode gibt es keine PHP-Bibliotheken von Drittanbietern: Der eigene MVC-Kern wird als lokales Composer-Paket `yaa/mvc-framework` eingebunden, während die Anwendung auf PHP und den erforderlichen Erweiterungen `mbstring`, `PDO` und `pdo_pgsql` basiert.

> [!NOTE]
> **UPDATE 2026.** Die ursprüngliche Idee ist unverändert: Es ist weiterhin dasselbe kleine MVC-Projekt, inzwischen auf PHP 8.5 sowie aktuelle PostgreSQL- und Bootstrap-Versionen gebracht und mit einer sichereren Verarbeitung zustandsändernder Anfragen. Docker Compose stellt eine reproduzierbare lokale Umgebung bereit, ohne PHP oder Composer auf dem Host installieren zu müssen; eine manuelle Einrichtung ohne Docker bleibt trotzdem möglich.

## Funktionen

- Artikel erstellen, anzeigen, bearbeiten und löschen;
- PostgreSQL über PDO und parametrisierte Abfragen;
- Paginierung der Artikelliste;
- serverseitige Validierung mit Erhalt der eingegebenen Werte bei Fehlern;
- CSRF-Schutz für zustandsändernde Anfragen;
- Meldungen nach CRUD-Operationen;
- Escaping dynamischer Ausgaben;
- typisierte `Page`, `Response` und `RedirectResponse`;
- Docker Compose mit Nginx, PHP-FPM und PostgreSQL.

## Schnellstart

Benötigt werden Git, Make und Docker mit Compose-Unterstützung.

| Befehl | Zweck | Hinweis |
|---|---|---|
| `git clone https://github.com/yaleksandr89/mvc-v1.git` | Klont das Repository | |
| `cd mvc-v1` | Wechselt in das Projektverzeichnis | |
| `make init` | Erstellt `.env.docker` und Arbeitsverzeichnisse | Wird normalerweise einmal nach dem Klonen ausgeführt |
| `make build` | Baut das PHP-Docker-Image | |
| `make up` | Startet Nginx, PHP-FPM und PostgreSQL | |
| `make composer-install` | Installiert Abhängigkeiten aus `composer.lock` | PHP und Composer werden auf dem Host nicht benötigt |
| `make demo-data` | Lädt 50 Demo-Artikel | Nur in eine leere Tabelle `blog_posts` |
| `make down` | Stoppt die Umgebung | PostgreSQL-Daten bleiben erhalten |

Nach dem Start ist die Anwendung unter [http://localhost:8080](http://localhost:8080) erreichbar.

> [!IMPORTANT]
> `make demo-data` funktioniert nur mit einer leeren Tabelle `blog_posts`. Sind bereits Daten vorhanden, beendet sich der Befehl, ohne etwas zu überschreiben. Um den lokalen PostgreSQL-Speicher vollständig neu zu erstellen und sofort Demo-Daten zu laden, kann `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` verwendet werden; dabei wird das lokale PostgreSQL-Volume entfernt.

Details zur Umgebung, zur isolierten Testdatenbank, zu weiteren Befehlen und zur manuellen Einrichtung ohne Docker stehen im [Entwicklungsleitfaden](../development.md).

## Aufbau der Anwendung

```text
HTTP-Anfrage
    ↓
Router
    ↓
Dispatcher
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php → Statuscode + Header + Body
```

`Page` beschreibt eine Seite, die noch gerendert werden muss, während `Response` und `RedirectResponse` bereits vollständige HTTP-Antworten darstellen. Statuscode, Header und Body werden ausschließlich im Einstiegspunkt `public/index.php` gesendet.

Das Artikelmodell greift direkt über PDO auf PostgreSQL zu. Echte Fehler bleiben innerhalb der Anwendung Exceptions, werden auf der passenden Ebene protokolliert und in eine sichere `500`-Antwort umgewandelt, ohne technische Details an Benutzer auszugeben.

Routing, Verantwortungsgrenzen und der vollständige Weg einer Anfrage werden im [Architekturleitfaden](../architecture.md) beschrieben.

## Prüfungen und Coverage

| Befehl | Zweck | Hinweis |
|---|---|---|
| `make test` | Führt PHPUnit aus | Setzt vorher die isolierte Testdatenbank zurück |
| `make test-dox` | Führt PHPUnit mit lesbaren Szenarionamen aus | |
| `make check` | Prüft Composer, Abhängigkeiten, PHPStan und PHPUnit | Zentrale Gesamtprüfung |
| `make coverage` | Zeigt die Coverage an und erstellt Clover XML | Ausgabe: `runtime/coverage.xml` |
| `make coverage-html` | Erstellt einen HTML-Coverage-Bericht | Ausgabe: `runtime/coverage/index.html` |
| `make smoke` | Prüft die Anwendung über echte HTTP-Anfragen | Benötigt 50 Demo-Artikel |

Die Tests verwenden die separate Datenbank `mvc_v1_test`, sodass die normale Entwicklungsdatenbank beim Testlauf nicht verändert wird.

Die vollständige Liste der Make-Befehle und die CI-Konfiguration stehen im [Entwicklungsleitfaden](../development.md).

## Was bewusst einfach gehalten ist

- der MVC-Kern bleibt klein, damit sich der zentrale Anfrageweg direkt im Code nachvollziehen lässt;
- es wurde kein separater Dependency-Injection-Container hinzugefügt;
- es wurden weder ein ORM noch eine zusätzliche Repository-Schicht ergänzt; der PostgreSQL-Zugriff erfolgt über PDO;
- es wurden weder eine Middleware-Kette noch ein Event-System eingeführt.

Diese Entscheidungen und Verantwortungsgrenzen werden im [Architekturleitfaden](../architecture.md) ausführlicher beschrieben.

## Feedback

- reproduzierbare Fehler → [GitHub Issues](https://github.com/yaleksandr89/mvc-v1/issues);
- Fragen und Ideen → [GitHub Discussions](https://github.com/yaleksandr89/mvc-v1/discussions).

---

<p align="center">
  Wenn das Projekt hilfreich war, gib ihm einen Stern auf GitHub — so können andere Entwickler es leichter finden. 🤘
</p>
