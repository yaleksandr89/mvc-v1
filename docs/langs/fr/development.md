# Développement

## Prérequis

Pour le scénario recommandé avec Docker Compose, l'hôte doit disposer de :

- Git;
- Make;
- Docker avec prise en charge de Compose.

PHP, Composer, PHPUnit, PHPStan et PHP CS Fixer s'exécutent dans le conteneur PHP. Il n'est pas nécessaire de les installer séparément sur l'hôte.

Docker n'est pas obligatoire : le projet peut aussi fonctionner dans un environnement classique avec PHP, Composer, PostgreSQL et un serveur web. Cette option est décrite séparément dans [Exécution sans Docker](#exécution-sans-docker).

## Premier démarrage

| Commande | Action | Remarque |
|---|---|---|
| `make init` | Crée `.env.docker`, `logs/` et `runtime/cache/` | Crée `.env.docker` uniquement s'il est absent |
| `make build` | Construit l'image Docker PHP | |
| `make up` | Démarre Nginx, PHP-FPM et PostgreSQL | Attend que les services soient prêts |
| `make composer-install` | Installe les dépendances depuis `composer.lock` | S'exécute dans le conteneur PHP |
| `make demo-data` | Charge 50 articles de démonstration | Uniquement dans une table `blog_posts` vide |
| `make db-check` | Affiche la version de PostgreSQL et l'état de `blog_posts` | Vérification utile après le premier démarrage |

Après le démarrage, l'application est accessible par défaut à l'adresse `http://localhost:8080`.

Pour arrêter l'environnement, utilisez `make down` : les conteneurs sont arrêtés et le volume PostgreSQL est conservé.

## Docker Compose

[`compose.yaml`](../../../compose.yaml) démarre trois services :

- `php` — PHP 8.5 FPM et les outils de développement ;
- `nginx` — Nginx 1.30.4;
- `postgres` — PostgreSQL 18.4.

Le conteneur PHP est construit à partir de [`docker/php/Dockerfile`](../../../docker/php/Dockerfile). L'utilisateur `app` reçoit l'UID/GID de l'utilisateur de l'hôte, de sorte que `vendor`, les rapports et les autres fichiers créés dans le conteneur ne devraient pas appartenir à `root`.

La liste complète des commandes de gestion de l'environnement figure dans [Toutes les commandes Make](#toutes-les-commandes-make).

## Variables d'environnement

Docker utilise `.env.docker`, créé à partir de [`.env.docker.example`](../../../.env.docker.example).

Principaux groupes de paramètres :

- `HOST_UID`, `HOST_GID` — UID/GID de l'utilisateur de l'hôte ;
- `APP_PORT` — port HTTP externe, `8080` par défaut ;
- `DB_FORWARD_PORT` — port PostgreSQL sur l'hôte ;
- `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` — paramètres PostgreSQL locaux ;
- `APP_ENV`, `APP_NAMESPACE`, `APP_TIMEZONE` — paramètres de l'application ;
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — connexion de l'application à la base de données.

Les valeurs du fichier d'exemple sont réservées au développement local. Ne placez pas de vrais secrets dans Git.

Pour un démarrage manuel sans Docker, créez un `.env` à la racine à partir de [`.env.example`](../../../.env.example). L'application charge elle-même ce fichier, sans bibliothèque dotenv tierce. Les variables d'environnement du processus déjà définies et les valeurs de `$_ENV` sont prioritaires et ne sont pas écrasées par celles de `.env`.

Le `.env` à la racine est prévu pour le format simple `KEY=VALUE` ; le projet n'utilise pas la syntaxe étendue des bibliothèques dotenv.

## PostgreSQL et données de démonstration

Au premier démarrage d'un nouveau volume PostgreSQL, le schéma est créé à partir de [`docs/db_schema_only.sql`](../../db_schema_only.sql). Pour la démonstration, vous pouvez charger 50 articles depuis [`docs/db_demo_data.sql`](../../db_demo_data.sql).

| Commande | Action | Remarque |
|---|---|---|
| `make db-check` | Vérifie la version de PostgreSQL et l'état de `blog_posts` | |
| `make demo-data` | Charge 50 articles de démonstration | Fonctionne uniquement avec une table `blog_posts` vide |
| `make postgres-reinit CONFIRM=postgres18` | Recrée entièrement le volume PostgreSQL local | Supprime les données locales actuelles |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Recrée PostgreSQL et charge immédiatement 50 articles | Supprime les données locales actuelles |

> [!IMPORTANT]
> `make demo-data` n'écrase pas les articles existants. Si la table contient déjà des données, la commande échoue sans les modifier. La réinitialisation complète n'est possible qu'avec `postgres-reinit` et une confirmation explicite.

## Base de test séparée

PHPUnit ne travaille pas sur la base principale `mvc_v1`. Les tests utilisent la base séparée `mvc_v1_test`.

| Commande | Action | Remarque |
|---|---|---|
| `make test-db-create` | Crée `mvc_v1_test` si elle n'existe pas encore | Ne supprime pas la base existante |
| `make test-db-reset` | Recrée le schéma et charge trois articles de test | Utilisé automatiquement avant les tests |
| `make test-db-check` | Vérifie le nom de la base et l'état des données de test | 3 articles attendus, avec des identifiants de 1 à 3 |
| `make test-db-drop CONFIRM=mvc_v1_test` | Supprime uniquement `mvc_v1_test` | Nécessite une confirmation explicite |

`make test`, `make test-dox`, `make coverage`, `make coverage-html` et `make check` réinitialisent eux-mêmes la base de test avant leur exécution.

## Tests, analyse et couverture

| Commande | Action | Remarque |
|---|---|---|
| `make test` | Lance PHPUnit | |
| `make test-dox` | Lance PHPUnit avec des noms de scénarios lisibles | |
| `make cs-check` | Vérifie le style PHP et affiche un diff en cas de non-conformité | Ne modifie pas les sources et ne réinitialise pas la base de test |
| `make cs-fix` | Formate le code PHP | Modifie les sources ; ne réinitialise pas la base de test |
| `make analyse` | Lance PHPStan | |
| `make check` | Exécute la validation Composer, l'audit Composer, la vérification du style avec PHP CS Fixer, PHPStan et PHPUnit | Vérification globale principale ; réinitialise d'abord la base de test |
| `make coverage` | Affiche la couverture et crée un fichier Clover XML | `runtime/coverage.xml` |
| `make coverage-html` | Crée un rapport HTML de couverture | `runtime/coverage/index.html` |
| `make smoke` | Effectue une vérification HTTP de bout en bout | Nécessite 50 articles de démonstration |

PHP CS Fixer est installé comme dépendance de développement. Les règles et le périmètre de formatage sont définis dans [`.php-cs-fixer.dist.php`](../../../.php-cs-fixer.dist.php) : le code PHP de `app/`, `config/`, `core/src/`, `tests/`, `views/`, le fichier `public/index.php` et le fichier de configuration lui-même. Les règles risquées sont désactivées ; les ressources frontend tierces ne sont pas formatées.

La référence de revue est PER Coding Style 3.1 et PSR-1 ; le formateur applique l'ensemble de règles configuré `@PER-CS` et des règles supplémentaires. L'exception convenue pour les templates mixtes PHP/HTML et les autres exigences sont décrites dans [`AGENTS.md`](../../../AGENTS.md).

Sans Docker, utilisez `composer cs:check` pour vérifier le style et `composer cs:fix` pour le corriger. Avec Make, ces commandes s'exécutent dans le conteneur PHP sous l'utilisateur `app`. Le cache du formateur est stocké dans `runtime/cache/php-cs-fixer.cache` et n'est pas suivi par Git. Les commandes indépendantes de vérification et de formatage ne nécessitent pas de connexion à la base de données.

La couverture sert d'outil de diagnostic, et non d'indicateur public de qualité. La CI transmet Clover à Codecov via OIDC. Il n'existe pas d'exigence distincte d'atteindre 100 % : les tests doivent protéger le comportement réel du projet, et non exécuter artificiellement des branches inaccessibles pour un pourcentage.

## Vérification HTTP de bout en bout

`make smoke` exécute un scénario HTTP réel via l'environnement Docker démarré et vérifie :

- `200` pour les pages ordinaires ;
- `403` pour un jeton CSRF invalide ;
- les redirections `302` et l'en-tête `Location` ;
- `405 Method Not Allowed` et l'en-tête `Allow: POST` ;
- `404` après la suppression d'un article ;
- le rétablissement du nombre initial de lignes de démonstration.

Cette vérification complète PHPUnit : les tests vérifient le code et l'intégration dans le processus PHP, tandis que `make smoke` parcourt toute la chaîne Nginx → PHP-FPM → application → PostgreSQL.

## GitHub Actions

Le workflow [`.github/workflows/ci.yml`](../../../.github/workflows/ci.yml) s'exécute pour les pull requests vers `master`, lors des pushes sur `master` et manuellement via Actions → CI → Run workflow.

Dans un environnement GitHub Actions vierge, il :

1. crée l'environnement local ;
2. vérifie la configuration Docker Compose ;
3. construit et démarre les conteneurs ;
4. installe les dépendances depuis `composer.lock` ;
5. valide le fichier Composer du noyau MVC intégré ;
6. vérifie la syntaxe PHP ;
7. lance `make check` : validation stricte de Composer, audit des dépendances, vérification du style, PHPStan et PHPUnit ;
8. crée un rapport de couverture et transmet Clover à Codecov ;
9. charge les données de démonstration ;
10. vérifie PostgreSQL ;
11. lance `make smoke` ;
12. supprime la base de test et arrête les conteneurs.

Ce workflow ne déploie pas l'application.

## Toutes les commandes Make

`make` sans argument affiche l'aide intégrée. Voici toutes les commandes définies par le [`Makefile`](../../../Makefile) actuel.

| Commande | Action | Remarque |
|---|---|---|
| `make` / `make help` | Affiche l'aide des commandes | |
| `make init` | Crée `.env.docker`, `logs/` et `runtime/cache/` | N'écrase pas un `.env.docker` existant |
| `make check-env` | Vérifie la présence de `.env.docker` et de Docker Compose | |
| `make config` | Vérifie la configuration Docker Compose effective | |
| `make build` | Construit l'image Docker PHP | |
| `make up` | Démarre l'environnement et attend que les services soient prêts | |
| `make down` | Arrête l'environnement | Le volume PostgreSQL est conservé |
| `make restart <service>` | Redémarre le service sélectionné | `php`, `nginx` ou `postgres` |
| `make ps` | Affiche l'état des conteneurs du projet | |
| `make log <service>` | Affiche le journal du service sélectionné en temps réel | `php`, `nginx` ou `postgres` |
| `make log-all` | Affiche les journaux de tous les services en temps réel | |
| `make in <service>` | Ouvre un shell dans le conteneur sélectionné | `php`, `nginx` ou `postgres` |
| `make php CMD="..."` | Exécute une commande PHP arbitraire sous l'utilisateur `app` | Par exemple : `make php CMD="-v"` |
| `make composer CMD="..."` | Exécute une commande Composer arbitraire | Par exemple : `make composer CMD="validate"` |
| `make composer-install` | Installe les dépendances depuis `composer.lock` | |
| `make test` | Lance PHPUnit | Réinitialise d'abord la base de test |
| `make test-dox` | Lance PHPUnit au format TestDox | Réinitialise d'abord la base de test |
| `make coverage` | Affiche la couverture et crée un fichier Clover XML | Réinitialise d'abord la base de test |
| `make coverage-html` | Crée un rapport HTML de couverture | Réinitialise d'abord la base de test |
| `make cs-check` | Vérifie le style PHP et affiche un diff en cas de non-conformité | Ne modifie pas les sources et ne réinitialise pas la base de test |
| `make cs-fix` | Formate le code PHP | Modifie les sources ; ne réinitialise pas la base de test |
| `make analyse` | Lance PHPStan | |
| `make check` | Exécute la validation Composer, l'audit Composer, la vérification du style avec PHP CS Fixer, PHPStan et PHPUnit | Réinitialise d'abord la base de test |
| `make smoke` | Lance une vérification CRUD/CSRF de bout en bout via HTTP | La base principale doit contenir 50 articles de démonstration |
| `make db-check` | Affiche la version de PostgreSQL et l'état de `blog_posts` | |
| `make demo-data` | Charge 50 articles de démonstration | Uniquement dans une table `blog_posts` vide |
| `make test-db-create` | Crée `mvc_v1_test` si elle est absente | |
| `make test-db-reset` | Recrée le schéma et les données de test | Supprime uniquement le schéma dans `mvc_v1_test` |
| `make test-db-check` | Vérifie la base de test | |
| `make test-db-drop CONFIRM=mvc_v1_test` | Supprime `mvc_v1_test` | Nécessite une confirmation exacte |
| `make postgres-reinit CONFIRM=postgres18` | Recrée le volume PostgreSQL local | Supprime les données locales de la base principale |
| `make postgres-reinit CONFIRM=postgres18 WITH_DEMO_DATA=1` | Recrée PostgreSQL et charge 50 articles | Supprime les données locales de la base principale |

## Droits sur les fichiers

Les commandes PHP et Composer qui créent des fichiers s'exécutent dans le conteneur sous l'utilisateur `app`, associé à l'UID/GID de l'utilisateur de l'hôte via `.env.docker`.

Le travail normal via Make ne devrait donc pas créer de fichiers appartenant à `root` dans la copie de travail. Les opérations massives de `chmod`, de `chown` et de suppression de répertoires ne sont pas nécessaires au cycle de développement habituel.

## Exécution sans Docker

Docker Compose reste la méthode de démarrage recommandée et la plus reproductible. Mais si Docker ne convient pas pour une raison quelconque, vous pouvez aussi démarrer le projet manuellement.

Ce guide ne propose pas de traité distinct sur l'installation de PHP-FPM et PostgreSQL et la configuration de Nginx : puisque vous avez choisi la voie manuelle, je pense que vous saurez configurer ces services de base. Pour ne pas partir entièrement de zéro, le dépôt contient un exemple de configuration Nginx, et la configuration Docker indique les extensions PHP et les paramètres d'environnement utilisés.

Vous aurez besoin de :

- PHP 8.5 avec `mbstring`, `pdo` et `pdo_pgsql` ;
- Composer;
- PostgreSQL;
- Nginx ou un autre serveur web dont la racine pointe vers `public/`.

Configurez le `.env` à la racine selon le modèle [`.env.example`](../../../.env.example). Si nécessaire, les valeurs peuvent être définies via l'environnement système ou PHP-FPM : elles sont prioritaires sur `.env`.

Le schéma et les données de démonstration se trouvent dans :

- [`docs/db_schema_only.sql`](../../db_schema_only.sql);
- [`docs/db_demo_data.sql`](../../db_demo_data.sql).

Exemple de configuration minimale Nginx + PHP-FPM : [`docs/conf/nginx-configuration.conf`](../../conf/nginx-configuration.conf). Avant de l'utiliser, adaptez `server_name`, `root` et `fastcgi_pass` à votre système.

Cet exemple ne suffit pas pour un déploiement en production : TLS, gestion des processus, secrets, sauvegardes et politiques d'exploitation sortent du périmètre du projet pédagogique.
