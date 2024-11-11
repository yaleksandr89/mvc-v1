# Projet : Implémentation du modèle d'architecture MVC à l'aide d'un exemple de framework simple

## Choisissez la Langue :

| Русский  | English                              | Español                              | 中文                              | Français                              | Deutsch                              |
|----------|--------------------------------------|--------------------------------------|---------------------------------|---------------------------------------|--------------------------------------|
| [Русский](../../README.md) | [English](./README_en.md) | [Español](./README_es.md) | [中文](./README_zh.md) | **Sélectionné** | [Deutsch](./README_de.md) |

## Stack Utilisé :

- PHP 8
- Postgresql (PDO)
- Bootstrap 5.3

## Description :

Le projet met en œuvre le modèle d'architecture MVC à l'aide d'un exemple de framework simple fait maison. Au sein du framework, les opérations CRUD ont été implémentées pour la section "Articles" :

<details>
  <summary>Création</summary>

![ajax filter is in operation](../img/mvc-create-article.gif)
</details>

<details>
  <summary>Affichage</summary>

![ajax filter is in operation](../img/mvc-read-article.gif)
</details>

<details>
  <summary>Mise à jour</summary>

![ajax filter is in operation](../img/mvc-update-article.gif)
</details>

<details>
  <summary>Suppression</summary>

![ajax filter is in operation](../img/mvc-delete-article.gif)
</details>

La validation est mise en œuvre lors de la création et de la mise à jour de l'article :

<details>
  <summary>Processus de Validation</summary>

![ajax filter is in operation](../img/mvc-validation.gif)
</details>

Dans le répertoire `docs/conf/`, vous pouvez trouver : `nginx-configuration.conf` - un exemple de fichier de configuration pour `nginx`.

## Lancement du Projet :

1. Ajoutez la configuration à votre serveur. Vous pouvez utiliser le fichier de `docs/conf/` comme base.
2. Exécutez `composer i`
3. Renommez `.env.example` en `.env` et remplissez la section `# DB info`
4. Créez une base de données et importez le contenu du fichier `db_dump.sql` (exemple de commande pour l'importation : `sudo -iu postgres psql -U postgres mvc_v1 < /www/mvc-v1.col/docs/db_dump.sql`), qui se trouve dans le dossier `docs/`.

> Les modifications apportées en 2024 sont minimes. J'ai délibérément conservé la structure de base de l'original, je n'ai pas ajouté de fonctionnalités de conteneur, DI, etc. Étant donné que c'est l'un de mes premiers travaux, j'ai décidé de le conserver presque tel quel 😇
