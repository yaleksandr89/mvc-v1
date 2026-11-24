# Contribuer à mvc-v1

## Choisir une langue

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../../.github/CONTRIBUTING.md) | [English](../en/CONTRIBUTING.md) | [Español](../es/CONTRIBUTING.md) | [中文](../zh/CONTRIBUTING.md) | **Français** | [Deutsch](../de/CONTRIBUTING.md) |

Merci de votre intérêt pour `mvc-v1`. C'est un projet PHP MVC pédagogique avec un petit framework maison.

## Avant de commencer

Consultez les Issues et Pull Requests existantes et gardez le travail proposé clair et ciblé. Signalez les sujets sensibles via la [politique de sécurité](../../../.github/SECURITY.md), et non dans une Issue publique.

## Contrat du projet

Préservez le but pédagogique et le flux visible `Router → Dispatcher → Controller → Model/View`, le CRUD des articles et le comportement PostgreSQL via PDO. La base PHP est 8.5 ; l'architecture est volontairement petite et indépendante d'un framework.

Ne remplacez pas le projet par Symfony ou Laravel et n'ajoutez pas de couches DI/container, ORM ou repository/service sans besoin concret. Évitez les refactorisations étendues sans rapport avec l'Issue.

## Branches

Créez une branche ciblée depuis la branche cible actuelle. Utilisez un nom court et descriptif, par exemple `fix/article-validation` ou `docs/contributing`.

## Commits

Les Conventional Commits sont recommandés : `fix(router): preserve path parameters`, `feat(articles): validate excerpt`, `docs: clarify local checks`. Gardez des commits petits, cohérents et clairement décrits.

## Vérifications locales

Avec Docker Compose, utilisez `make composer-install`, `make check`, `make cs-check` et `make cs-fix`. `make check` réinitialise la base de test isolée `mvc_v1_test` ; si vous exécutez Composer directement, préparez vous-même cette base isolée avant les tests.

```shell
composer install
composer check
```

Pour des vérifications ciblées, utilisez :

```shell
composer cs:check
composer test
composer analyse
```

`composer check` exécute dans l'ordre la validation stricte de Composer, l'audit des dépendances de `composer.lock`, la vérification du style avec PHP CS Fixer, PHPStan et PHPUnit.

Pour corriger les écarts de style, utilisez `composer cs:fix` : cette commande modifie les fichiers PHP. Les commandes indépendantes `composer cs:check` et `composer cs:fix` ne nécessitent pas de connexion à la base de données.

Lors d'une modification du runtime ou de la base de données, décrivez la vérification effectuée.

## Pull Request

Expliquez ce qui change, pourquoi et comment le vérifier. Limitez un PR à une tâche. Mettez à jour la documentation concernée si l'installation ou le comportement public change ; synchronisez les traductions si les politiques changent.

## Liste finale et hygiène de sécurité

- Aucun secret, `.env` de production, token, cookie ou donnée personnelle.
- `composer check` passe.
- Ajoutez ou mettez à jour les tests de régression du paquet si le comportement change.
- Le but MVC maison et la petite architecture restent intentionnels.
- Décrivez la vérification runtime/base de données si nécessaire.
- Mettez à jour documentation et traductions de politiques si nécessaire.
