# Projekt: Implementierung des MVC-Architekturmusters anhand eines einfachen Framework-Beispiels

## Sprache auswählen:

| Русский  | English                              | Español                              | 中文                              | Français                              | Deutsch                              |
|----------|--------------------------------------|--------------------------------------|---------------------------------|---------------------------------------|--------------------------------------|
| [Русский](../../README.md) | [English](./README_en.md) | [Español](./README_es.md) | [中文](./README_zh.md) | [Français](./README_fr.md) | **Ausgewählt** |

## Verwendeter Stack:

- PHP 8
- Postgresql (PDO)
- Bootstrap 5.3

## Beschreibung:

Das Projekt implementiert das MVC-Architekturmuster anhand eines einfachen selbstgemachten Frameworks als Beispiel. Innerhalb des Frameworks wurden CRUD-Operationen für den Abschnitt "Artikel" implementiert:

<details>
  <summary>Erstellung</summary>

![ajax filter is in operation](../img/mvc-create-article.gif)
</details>

<details>
  <summary>Anzeige</summary>

![ajax filter is in operation](../img/mvc-read-article.gif)
</details>

<details>
  <summary>Aktualisierung</summary>

![ajax filter is in operation](../img/mvc-update-article.gif)
</details>

<details>
  <summary>Löschung</summary>

![ajax filter is in operation](../img/mvc-delete-article.gif)
</details>

Bei der Erstellung und Aktualisierung von Artikeln wird eine Validierung durchgeführt:

<details>
  <summary>Validierungsprozess</summary>

![ajax filter is in operation](../img/mvc-validation.gif)
</details>

Im Verzeichnis `docs/conf/` finden Sie: `nginx-configuration.conf` - ein Beispielkonfigurationsdatei für `nginx`.

## Projekt starten:

1. Fügen Sie die Konfiguration Ihrem Server hinzu. Sie können die Datei aus `docs/conf/` als Basis verwenden.
2. Führen Sie `composer i` aus
3. Benennen Sie `.env.example` in `.env` um und füllen Sie den Abschnitt `# DB info` aus
4. Erstellen Sie eine Datenbank und importieren Sie den Inhalt der Datei `db_dump.sql` (Beispielbefehl für den Import: `sudo -iu postgres psql -U postgres mvc_v1 < /www/mvc-v1.col/docs/db_dump.sql`), die sich im Ordner `docs/` befindet.

> Die Änderungen, die 2024 vorgenommen wurden, sind minimal. Ich habe absichtlich die Kernstruktur des Originals beibehalten, ohne Containerfunktionalität, DI usw. hinzuzufügen. Da dies eine meiner ersten Arbeiten ist, habe ich beschlossen, sie fast im Originalzustand zu belassen 😇
