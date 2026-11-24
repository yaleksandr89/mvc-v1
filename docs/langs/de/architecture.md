# Architektur

`mvc-v1` ist eine serverseitige PHP-Lernanwendung mit einem kleinen eigenen MVC-Kern. Die Architektur bleibt bewusst geradlinig: Der Router wählt das Szenario, der Controller koordiniert die Anfrage, das Model verarbeitet Daten, die View erstellt die Seite und die fertige HTTP-Antwort wird an einer einzigen Stelle gesendet.

Das Projekt ist nicht als universelles Framework gedacht und versucht nicht, den gesamten Funktionsumfang von Symfony, Laravel oder anderen professionellen Lösungen nachzubilden.

## Allgemeiner Anfrageablauf

```text
HTTP-Anfrage
    ↓
Router
    ↓
Track
    ↓
Dispatcher
    ↓
Controller-Methode
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php
    ↓
Statuscode + Header + Body
```

Das Einstiegsskript [`public/index.php`](../../../public/index.php) lädt Konfiguration und Routen, normalisiert den URI, erhält einen `Track` vom `Router` und übergibt ihn an den `Dispatcher`.

Gibt der Controller eine `Page` zurück, wird sie zur HTML-Erzeugung an `View` übergeben. Hat er bereits eine `Response` oder `RedirectResponse` zurückgegeben, wird die View nicht aufgerufen. In beiden Fällen erhält das Einstiegsskript eine fertige `Response` und sendet erst dann Statuscode, Header und Body.

## Router und Track

[`Router`](../../../core/src/Router.php) gleicht den Anfragepfad mit den Routen aus [`config/routes.php`](../../../config/routes.php) ab.

Beim Auswerten des URI:

- Parameter nach `?` beeinflussen die Routenauswahl nicht;
- benannte Routensegmente werden zu Parametern;
- Controller-Name, Methodenname und Parameter werden in einem `Track` zusammengefasst;
- wird keine Route gefunden, entsteht ein `Track` für `ErrorController::notFound()`.

`Router` erstellt keine Controller und sendet selbst nichts an den Client.

## Dispatcher

[`Dispatcher`](../../../core/src/Dispatcher.php) ist für den Übergang vom `Track` zur passenden Controller-Methode verantwortlich.

Er:

1. prüft das zulässige Format von Controller- und Methodennamen;
2. prüft `APP_NAMESPACE`;
3. bindet die Controller-Datei ein;
4. stellt sicher, dass Klasse und Methode existieren und zugänglich sind;
5. ruft die Methode mit den Routenparametern auf;
6. akzeptiert nur eine `Page` oder `Response`.

Ein reguläres HTTP-Ergebnis wird nicht durch eine Exception dargestellt. Der Controller gibt eine Seite, eine normale Antwort oder eine `RedirectResponse` zurück.

`Dispatcher` protokolliert Fehler beim Einbinden des Controllers, nicht zugängliche Methoden und andere tatsächliche Fehler in `logs/dispatcher-errors.txt` und liefert dem Benutzer eine sichere `Response` mit Status `500`.

## Page, Response und RedirectResponse

[`Page`](../../../core/src/Page.php) beschreibt eine Seite, die noch gerendert werden muss. Sie enthält:

- den Layout-Namen;
- Metadaten;
- den View-Namen;
- View-Daten;
- den HTTP-Statuscode der resultierenden Seite.

Beispielsweise gibt `ErrorController` eine normale `Page` mit Status `404` zurück, ohne den globalen Antwortcode im Controller zu ändern.

[`Response`](../../../core/src/Response.php) stellt eine fertige HTTP-Antwort dar: Body, Statuscode und Header. Die Klasse prüft den zulässigen Statuscodebereich und validiert Header, einschließlich des Schutzes vor Steuerzeichen und doppelten Namen unabhängig von der Groß-/Kleinschreibung.

[`RedirectResponse`](../../../core/src/RedirectResponse.php) spezialisiert `Response` für Weiterleitungen: Sie enthält den Header `Location`, einen leeren Body und erlaubt die Codes `301`, `302`, `303`, `307` und `308`.

Weiterleitungen, `403 Forbidden` und `405 Method Not Allowed` bleiben daher reguläre Ergebnisse eines Methodenaufrufs; Exceptions sind tatsächlichen Fehlern vorbehalten.

## Controller

Der Basis-[`Controller`](../../../core/src/Controller.php) unterstützt die Erstellung einer `Page` über `render()` und enthält gemeinsame Paginierungslogik.

Das wichtigste Beispiel findet sich in [`ArticleController`](../../../app/Controllers/ArticleController.php):

- `all()` und `show()` geben eine `Page` zurück;
- `create()`, `edit()` und `delete()` geben `Page|Response` zurück;
- erfolgreiche POST-Operationen geben eine `RedirectResponse` zurück;
- ein ungültiges CSRF-Token führt zu `Response('Forbidden', 403)`;
- der Versuch, einen Artikel mit einer anderen Methode als POST zu löschen, liefert `405` mit dem Header `Allow: POST`;
- ein fehlender Artikel führt zu einer `Page` mit Status `404`.

Der Controller sendet HTTP-Header nicht direkt und beendet den PHP-Prozess nicht mit `exit`.

## Model und PostgreSQL

Das Basis-[`Model`](../../../core/src/Model.php) öffnet anhand der Umgebungsvariablen eine PDO-Verbindung zu PostgreSQL.

Die Verbindung ist wie folgt konfiguriert:

- PDO-Fehler werden in Exceptions umgewandelt;
- Ergebnisse werden standardmäßig als assoziative Arrays zurückgegeben;
- die Emulation vorbereiteter Anweisungen ist deaktiviert.

Abfragen werden über vorbereitete PDO-Anweisungen ausgeführt. Der Artikel-CRUD ist in [`ArticleModal`](../../../app/Models/ArticleModal.php) implementiert.

Bei einer `PDOException` werden technische Informationen in `logs/database-errors.txt` protokolliert. Anschließend wird eine `DatabaseException` ausgelöst, die die ursprüngliche Exception bewahrt. `Dispatcher` weiß, dass der Datenbankfehler bereits protokolliert wurde, wiederholt den Eintrag daher nicht und gibt dem Benutzer eine sichere `500`-Antwort zurück.

## View und Senden der Antwort

[`View`](../../../core/src/View.php) erhält eine `Page`, rendert View und Layout mittels Ausgabepufferung und gibt anschließend eine fertige `Response` zurück.

Tritt beim Erstellen der Seite ein Fehler auf:

- `View` bereinigt nur die selbst geöffneten Ausgabepuffer;
- die technische Ursache wird protokolliert;
- der Benutzer erhält eine sichere `500`-Antwort.

Das reguläre Senden der HTTP-Antwort erfolgt ausschließlich in [`public/index.php`](../../../public/index.php):

```text
http_response_code()
→ header()
→ echo
```

Die Erstellung der Antwort ist also von ihrem tatsächlichen Senden an den Client getrennt.

## Validierung, CSRF und Datenausgabe

[`ArticleValidate`](../../../app/Validations/ArticleValidate.php) validiert Artikeldaten auf dem Server. Bei einem Validierungsfehler speichert der Controller die eingegebenen Werte und Meldungen in der Session und gibt anschließend eine Weiterleitung zurück.

[`SecurityHelper`](../../../app/Helper/SecurityHelper.php) erstellt und prüft das CSRF-Token. Die ändernden Operationen `create`, `edit` und `delete` führen diese Prüfung vor dem Schreiben in PostgreSQL aus.

Dynamische Werte werden bei der Ausgabe maskiert. Artikelinhalte werden als Text gespeichert: Zeilenumbrüche werden bei der Darstellung umgewandelt, beliebiges Benutzer-HTML wird nicht ausgeführt.

## Fehlergrenzen

Das Projekt trennt reguläre HTTP-Antworten von tatsächlichen Fehlern:

```text
Weiterleitung / 403 / 405
    → reguläre Response
    → kein Eintrag im Fehlerprotokoll

PostgreSQL- / PDO-Fehler
    → Eintrag im Datenbankprotokoll
    → DatabaseException
    → sichere 500-Antwort

Controller- oder Dispatcher-Fehler
    → Eintrag im Dispatcher-Protokoll
    → sichere 500-Antwort

View- oder Layout-Fehler
    → Eintrag im View-Protokoll
    → sichere 500-Antwort
```

Technische Exception-Meldungen dürfen nicht in der HTML-Antwort an den Benutzer erscheinen.

## Was bewusst einfach gehalten ist

Das Projekt bleibt klein und führt daher keine zusätzlichen Schichten nur um architektonischer Symmetrie willen ein:

- kein Dependency-Container;
- kein ORM;
- keine separate Repository-Schicht über jedem Model;
- keine Middleware-Kette;
- kein Event-Bus;
- keine Hintergrundaufgaben;
- keine separate REST-API oder SPA.

In einer größeren Anwendung könnten einige dieser Grenzen nützlich sein. Hier würden sie den zentralen MVC-Ablauf nur hinter zusätzlicher Infrastruktur verbergen.

Praktische Startbefehle, die Testdatenbank, Coverage und CI sind im [Entwicklungsleitfaden](development.md) beschrieben.
