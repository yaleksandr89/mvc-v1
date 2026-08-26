# MVC en PHP

[![Source Code](https://img.shields.io/badge/source-yaleksandr89%2Fmvc--v1-blue.svg?style=flat-square)](https://github.com/yaleksandr89/mvc-v1)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4.svg?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18.4-4169E1.svg?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![CI](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml/badge.svg)](https://github.com/yaleksandr89/mvc-v1/actions/workflows/ci.yml)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](../../LICENSE.md)

<p align="center">
  <img
    src="../img/mvc-v1-readme-cover.png"
    alt="MVC en PHP — blog pédagogique avec un noyau MVC maison, PostgreSQL et Docker Compose"
    width="100%"
  >
</p>

## Choisir une langue

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../README.md) | [English](./README_en.md) | [Español](./README_es.md) | [中文](./README_zh.md) | **Sélectionné** | [Deutsch](./README_de.md) |

C'est l'un de mes premiers projets avec une implémentation MVC écrite directement, sans s'appuyer sur un framework prêt à l'emploi. L'exemple est un blog simple d'articles : routage, contrôleurs, modèles, vues et CRUD sont organisés de façon à pouvoir suivre tout le parcours d'une requête dans le code du projet.

Le projet évite volontairement de devenir un assemblage de bibliothèques prêtes à l'emploi. PostgreSQL est utilisé directement via PDO, sans ORM. Le code de production ne dépend d'aucune bibliothèque PHP tierce : le noyau MVC maison est chargé comme paquet Composer local `yaa/mvc-framework`, tandis que l'application s'appuie sur PHP et les extensions requises `mbstring`, `PDO` et `pdo_pgsql`.

> [!NOTE]
> **MISE À JOUR 2026.** L'idée d'origine n'a pas changé : il s'agit toujours du même petit projet MVC, désormais mis à niveau pour PHP 8.5, les versions actuelles de PostgreSQL et Bootstrap, avec un traitement plus sûr des requêtes qui modifient les données. Docker Compose fournit un environnement local reproductible sans installer PHP ni Composer sur l'hôte, tout en conservant la possibilité d'une installation manuelle sans Docker.

## Fonctionnalités

- création, consultation, modification et suppression d'articles ;
- PostgreSQL via PDO et des requêtes paramétrées ;
- pagination de la liste des articles ;
- validation côté serveur avec conservation des valeurs saisies en cas d'erreur ;
- protection CSRF des requêtes qui modifient les données ;
- messages après les opérations CRUD ;
- échappement des données dynamiques à l'affichage ;
- `Page`, `Response` et `RedirectResponse` typés ;
- Docker Compose avec Nginx, PHP-FPM et PostgreSQL.

## Démarrage rapide

Git, Make et Docker avec le support de Compose sont nécessaires.

| Commande | Rôle | Remarque |
|---|---|---|
| `git clone https://github.com/yaleksandr89/mvc-v1.git` | Clone le dépôt | |
| `cd mvc-v1` | Ouvre le répertoire du projet | |
| `make init` | Crée `.env.docker` et les répertoires de travail | À exécuter généralement une fois après le clonage |
| `make build` | Construit l'image Docker PHP | |
| `make up` | Démarre Nginx, PHP-FPM et PostgreSQL | |
| `make composer-install` | Installe les dépendances depuis `composer.lock` | PHP et Composer ne sont pas nécessaires sur l'hôte |
| `make demo-data` | Charge 50 articles de démonstration | Uniquement dans une table `blog_posts` vide |
| `make down` | Arrête l'environnement | Les données PostgreSQL sont conservées |

Après le démarrage, l'application est accessible à l'adresse [http://localhost:8080](http://localhost:8080).

> [!IMPORTANT]
> `make demo-data` fonctionne uniquement avec une table `blog_posts` vide. Si elle contient déjà des données, la commande s'arrête sans rien écraser. Pour recréer complètement le stockage PostgreSQL local et charger immédiatement les données de démonstration, utilisez `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` ; cette commande supprime le volume PostgreSQL local.

Les détails de l'environnement, la base de données de test isolée, les autres commandes et l'installation manuelle sans Docker sont décrits dans le [guide de développement](../development.md).

## Fonctionnement de l'application

```text
Requête HTTP
    ↓
Router
    ↓
Dispatcher
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php → code d'état + en-têtes + corps
```

`Page` décrit une page qui doit encore être rendue, tandis que `Response` et `RedirectResponse` représentent des réponses HTTP déjà complètes. Le code d'état, les en-têtes et le corps sont envoyés uniquement depuis le point d'entrée `public/index.php`.

Le modèle des articles utilise PostgreSQL directement via PDO. Les véritables erreurs restent des exceptions dans l'application, sont journalisées au niveau approprié puis converties en une réponse `500` sûre, sans exposer de détails techniques à l'utilisateur.

Le routage, les limites de responsabilité et le parcours complet d'une requête sont détaillés dans le [guide d'architecture](../architecture.md).

## Vérifications et couverture

| Commande | Rôle | Remarque |
|---|---|---|
| `make test` | Lance PHPUnit | Réinitialise d'abord la base de test isolée |
| `make test-dox` | Lance PHPUnit avec des noms de scénarios lisibles | |
| `make check` | Vérifie Composer, les dépendances, PHPStan et PHPUnit | Vérification globale principale |
| `make coverage` | Affiche la couverture et crée un fichier Clover XML | Résultat : `runtime/coverage.xml` |
| `make coverage-html` | Crée un rapport HTML de couverture | Résultat : `runtime/coverage/index.html` |
| `make smoke` | Vérifie l'application via de vraies requêtes HTTP | Nécessite 50 articles de démonstration |

Les tests utilisent la base séparée `mvc_v1_test`, de sorte que la base principale de développement n'est pas modifiée pendant leur exécution.

La liste complète des commandes Make et la configuration de CI sont décrites dans le [guide de développement](../development.md).

## Ce qui reste volontairement simple

- le noyau MVC reste petit afin que le parcours principal d'une requête puisse être suivi directement dans le code ;
- aucun conteneur d'injection de dépendances séparé n'a été ajouté ;
- aucun ORM ni couche de repository supplémentaire n'a été ajouté ; l'accès à PostgreSQL se fait via PDO ;
- aucune chaîne de middleware ni système d'événements n'a été introduit.

Ces choix et les limites de responsabilité sont expliqués plus en détail dans le [guide d'architecture](../architecture.md).

## Retours

- bugs reproductibles → [GitHub Issues](https://github.com/yaleksandr89/mvc-v1/issues) ;
- questions et idées → [GitHub Discussions](https://github.com/yaleksandr89/mvc-v1/discussions).

---

<p align="center">
  Si le projet vous a été utile, ajoutez une étoile sur GitHub : cela aidera d'autres développeurs à le trouver. 🤘
</p>
