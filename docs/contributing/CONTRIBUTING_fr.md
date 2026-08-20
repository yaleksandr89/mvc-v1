# Contribuer à mvc-v1

## Choisir une langue

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/CONTRIBUTING.md) | [English](CONTRIBUTING_en.md) | [Español](CONTRIBUTING_es.md) | [中文](CONTRIBUTING_zh.md) | **Français** | [Deutsch](CONTRIBUTING_de.md) |

Merci de votre intérêt pour `mvc-v1`. C'est un projet PHP MVC pédagogique avec un petit framework maison.

## Avant de commencer

Consultez les Issues et Pull Requests existantes et gardez le travail proposé clair et ciblé. Signalez les sujets sensibles via la [politique de sécurité](../../.github/SECURITY.md), et non dans une Issue publique.

## Contrat du projet

Préservez le but pédagogique et le flux visible `Router → Dispatcher → Controller → Model/View`, le CRUD des articles et le comportement PostgreSQL via PDO. La base PHP est 8.5 ; l'architecture est volontairement petite et indépendante d'un framework.

Ne remplacez pas le projet par Symfony ou Laravel et n'ajoutez pas de couches DI/container, ORM ou repository/service sans besoin concret. Évitez les refactorisations étendues sans rapport avec l'Issue.

## Branches

Créez une branche ciblée depuis la branche cible actuelle. Utilisez un nom court et descriptif, par exemple `fix/article-validation` ou `docs/contributing`.

## Commits

Les Conventional Commits sont recommandés : `fix(router): preserve path parameters`, `feat(articles): validate excerpt`, `docs: clarify local checks`. Gardez des commits petits, cohérents et clairement décrits.

## Vérifications locales

```shell
composer install
composer check
```

Pour des vérifications ciblées, utilisez :

```shell
composer test
composer analyse
```

Il n'existe pas encore de commande formatter ou code-style. Lors d'une modification du runtime ou de la base de données, décrivez la vérification effectuée.

## Pull Request

Expliquez ce qui change, pourquoi et comment le vérifier. Limitez un PR à une tâche. Mettez à jour la documentation concernée si l'installation ou le comportement public change ; synchronisez les traductions si les politiques changent.

## Liste finale et hygiène de sécurité

- Aucun secret, `.env` de production, token, cookie ou donnée personnelle.
- `composer check` passe.
- Ajoutez ou mettez à jour les tests de régression du paquet si le comportement change.
- Le but MVC maison et la petite architecture restent intentionnels.
- Décrivez la vérification runtime/base de données si nécessaire.
- Mettez à jour documentation et traductions de politiques si nécessaire.
