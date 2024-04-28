# Project: Implementation of the MVC architectural pattern using a basic framework example

## Choose Language:

| Русский                                                     | English                              | Español                              | 中文                              | Français                              | Deutsch                              |
|-------------------------------------------------------------|--------------------------------------|--------------------------------------|---------------------------------|---------------------------------------|--------------------------------------|
| [Русский](../../README.md) | **Selected** | [Español](./README_es.md) | [中文](./README_zh.md) | [Français](./README_fr.md) | [Deutsch](./README_de.md) |

## Used Stack:

- PHP 8
- Mysql (PDO)
- Bootstrap 5.3

## Description:

The project implements the MVC architectural pattern using a simple homemade framework as an example. Within the framework, CRUD operations were implemented for the "Articles" section:

<details>
  <summary>Creating</summary>

![ajax filter is in operation](../img/mvc-create-article.gif)
</details>

<details>
  <summary>Displaying</summary>

![ajax filter is in operation](../img/mvc-read-article.gif)
</details>

<details>
  <summary>Updating</summary>

![ajax filter is in operation](../img/mvc-update-article.gif)
</details>

<details>
  <summary>Deleting</summary>

![ajax filter is in operation](../img/mvc-delete-article.gif)
</details>

Validation is implemented during article creation and updating:

<details>
  <summary>Validation Process</summary>

![ajax filter is in operation](../img/mvc-validation.gif)
</details>

In the directory `docs/conf/` you can find: `nginx-configuration.conf` - an example configuration file for `nginx`.

## Running the Project:

1. Add the configuration to your server. You can use the file from `docs/conf/` as a basis.
2. Run `composer i`
3. Rename `.env.example` to `.env` and fill in the `# DB info` section
4. Create a database and import the contents of the file `db-dump-with-articles.sql`, which is located in `docs/mysql-dump/`.

> Changes made in 2024 are minimal. I intentionally kept the core structure of the original, I did not add container functionality, DI, etc. Since this is one of my first works, I decided to keep it almost in its original form 😇