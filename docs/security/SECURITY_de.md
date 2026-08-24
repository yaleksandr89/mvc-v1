# Sicherheit

## Sprache wählen

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/SECURITY.md) | [English](SECURITY_en.md) | [Español](SECURITY_es.md) | [中文](SECURITY_zh.md) | [Français](SECURITY_fr.md) | **Deutsch** |

Bitte melden Sie mögliche Sicherheitslücken verantwortungsvoll. `mvc-v1` ist ein pädagogisches MVC-Projekt, doch Sicherheitsprobleme in Routing, CRUD, PostgreSQL-Zugriff, Verarbeitung von Benutzereingaben und Datenausgabe werden ebenso ernst genommen wie in jedem öffentlichen Repository.

## Was besser privat gemeldet wird

- Umgehung des CSRF-Schutzes beim Erstellen, Bearbeiten oder Löschen eines Artikels.
- SQL-Injection oder die Möglichkeit, SQL-Identifikatoren oder Abfrageparameter zu beeinflussen.
- Stored oder reflected XSS, Umgehung von HTML-Escaping oder unsichere Ausgabe von Benutzerdaten.
- Möglichkeit, einen route/path-Parameter über query string zu ersetzen oder auf eine andere Entität einzuwirken.
- Offenlegung interner Pfade, Ausnahmen, Konfiguration, `.env`-Inhalten, PostgreSQL-Zugangsdaten oder anderer sensibler Informationen.
- Schwachstelle in Session-, CSRF-token- oder HTTP-Methoden-Verarbeitung, die eine unerwünschte Zustandsänderung ermöglicht.
- Ausnutzbares Abhängigkeitsproblem mit wesentlicher Auswirkung auf dieses Projekt.
- Kompromittierung von Quellcode, CI, dependency chain oder einem anderen Supply-Chain-Element.

## Was in Issues veröffentlicht werden kann

- Routing-Fehler ohne Sicherheitsauswirkung.
- Fehlerhaftes CRUD-Verhalten oder Validierungsfehler ohne Sicherheitsauswirkung.
- Problem mit Paginierung, Ausgabe oder Oberfläche.
- PHP- oder PostgreSQL-Kompatibilitätsproblem ohne Offenlegung sensibler Daten.
- Dokumentationsfehler.
- Wunsch nach einer neuen Funktion oder Verbesserung.

Wenn Sie unsicher sind, ob ein Problem die Sicherheit betrifft, nutzen Sie zuerst den privaten Kanal.

## So melden Sie

- Wenn der Security-Bereich des Repositorys ein privates Formular für Schwachstellenmeldungen bietet, verwenden Sie es zuerst.
- Veröffentlichen Sie keinen Exploit-Code, echte Geheimnisse, produktive `.env`-Inhalte, PostgreSQL-Passwörter, Session-Identifiers oder andere sensible Daten in Issues, Discussions, Pull Requests oder Logs.
- Ist das private Formular vorübergehend nicht verfügbar, erstellen Sie ein minimales öffentliches Issue ohne Ausnutzungsdetails und bitten Sie um einen privaten Kontaktkanal.
- Legen Sie technische Ausnutzungsdetails erst nach Absprache mit dem Maintainer und verfügbarem Fix öffentlich offen.

## Was Sie beifügen sollten

Geben Sie nach Möglichkeit an:

- betroffene Version oder Commit-SHA;
- PHP-Version;
- PostgreSQL-Version, falls die Datenbank betroffen ist;
- betroffener Bereich: routing, controller, model/PDO, validation, view/output, session/CSRF, configuration oder dependency chain;
- Auswirkung;
- minimale Reproduktion;
- bereinigten Anfrage-, Antwort- oder Log-Ausschnitt, falls hilfreich.

Verwenden Sie nur synthetische Daten. Fügen Sie niemals echte Passwörter, Tokens, Cookies, Session-IDs, produktive `.env`-Inhalte oder andere Geheimnisse bei.

## Was danach geschieht

- Das Projekt hat einen Maintainer; es gibt kein garantiertes SLA.
- Der Maintainer wird versuchen, den Bericht zu bestätigen, das Problem zu reproduzieren, die Auswirkung zu bewerten und einen Fix vorzubereiten.
- Ein Bug-Bounty-Programm wird nicht versprochen.
- Stimmen Sie die öffentliche Offenlegung ab, bis ein Fix verfügbar ist.
