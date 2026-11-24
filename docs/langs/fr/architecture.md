# Architecture

`mvc-v1` est une application PHP pédagogique côté serveur, dotée d'un petit noyau MVC maison. L'architecture reste volontairement directe : le routeur choisit le scénario, le contrôleur coordonne la requête, le modèle traite les données, la vue construit la page et la réponse HTTP prête à l'emploi est envoyée depuis un point unique.

Le projet n'a pas vocation à être un framework généraliste et ne cherche pas à reproduire toutes les fonctionnalités de Symfony, Laravel ou d'autres solutions destinées à la production.

## Parcours global d'une requête

```text
Requête HTTP
    ↓
Router
    ↓
Track
    ↓
Dispatcher
    ↓
méthode du contrôleur
    ↓
Page | Response
    ↓
Page → View → Response
    ↓
public/index.php
    ↓
code de statut + en-têtes + corps
```

Le script d'entrée [`public/index.php`](../../../public/index.php) charge la configuration et les routes, normalise l'URI, récupère un `Track` auprès de `Router` et le transmet à `Dispatcher`.

Si le contrôleur renvoie une `Page`, elle est transmise à `View` pour générer le HTML. S'il a déjà renvoyé une `Response` ou une `RedirectResponse`, la vue n'est pas appelée. Dans les deux cas, le script d'entrée reçoit une `Response` prête à l'emploi avant d'envoyer le code de statut, les en-têtes et le corps.

## Router et Track

[`Router`](../../../core/src/Router.php) fait correspondre le chemin de la requête aux routes de [`config/routes.php`](../../../config/routes.php).

Lors de l'analyse de l'URI :

- les paramètres après `?` n'interviennent pas dans le choix de la route ;
- les segments nommés de la route deviennent des paramètres ;
- le nom du contrôleur, le nom de la méthode et les paramètres sont regroupés dans un `Track` ;
- si aucune route ne correspond, un `Track` est créé pour `ErrorController::notFound()`.

`Router` ne crée pas de contrôleurs et n'envoie rien lui-même au client.

## Dispatcher

[`Dispatcher`](../../../core/src/Dispatcher.php) assure le passage d'un `Track` à la méthode appropriée du contrôleur.

Il :

1. vérifie le format autorisé des noms du contrôleur et de la méthode ;
2. vérifie `APP_NAMESPACE` ;
3. inclut le fichier du contrôleur ;
4. s'assure que la classe et la méthode existent et sont accessibles ;
5. appelle la méthode avec les paramètres de la route ;
6. n'accepte qu'une `Page` ou une `Response`.

Un résultat HTTP normal n'est pas représenté par une exception. Le contrôleur renvoie une page, une réponse ordinaire ou une `RedirectResponse`.

`Dispatcher` consigne les erreurs d'inclusion du contrôleur, les méthodes inaccessibles et les autres défaillances réelles dans `logs/dispatcher-errors.txt`, puis renvoie à l'utilisateur une `Response` sûre de statut `500`.

## Page, Response et RedirectResponse

[`Page`](../../../core/src/Page.php) décrit une page qui reste à rendre. Elle contient :

- le nom du layout ;
- les métadonnées ;
- le nom de la vue ;
- les données de la vue ;
- le code de statut HTTP de la page finale.

Par exemple, `ErrorController` renvoie une `Page` ordinaire de statut `404` sans modifier le code de réponse global depuis le contrôleur.

[`Response`](../../../core/src/Response.php) représente une réponse HTTP prête à l'emploi : corps, code de statut et ensemble d'en-têtes. La classe vérifie la plage de codes autorisés et valide les en-têtes, notamment contre les caractères de contrôle et les noms en double sans distinction de casse.

[`RedirectResponse`](../../../core/src/RedirectResponse.php) spécialise `Response` pour les redirections : elle contient un en-tête `Location`, un corps vide et autorise les codes `301`, `302`, `303`, `307` et `308`.

Ainsi, les redirections, `403 Forbidden` et `405 Method Not Allowed` restent des résultats ordinaires de l'exécution d'une méthode ; les exceptions sont réservées aux erreurs réelles.

## Contrôleurs

Le [`Controller`](../../../core/src/Controller.php) de base facilite la création d'une `Page` via `render()` et contient la logique commune de pagination.

L'exemple principal se trouve dans [`ArticleController`](../../../app/Controllers/ArticleController.php) :

- `all()` et `show()` renvoient une `Page` ;
- `create()`, `edit()` et `delete()` renvoient `Page|Response` ;
- les opérations POST réussies renvoient une `RedirectResponse` ;
- un jeton CSRF invalide entraîne `Response('Forbidden', 403)` ;
- tenter de supprimer un article avec une méthode autre que POST renvoie `405` avec l'en-tête `Allow: POST` ;
- un article absent entraîne une `Page` de statut `404`.

Le contrôleur n'envoie pas directement les en-têtes HTTP et ne termine pas le processus PHP avec `exit`.

## Modèle et PostgreSQL

Le [`Model`](../../../core/src/Model.php) de base ouvre une connexion PDO à PostgreSQL à partir des variables d'environnement.

La connexion est configurée comme suit :

- les erreurs PDO sont converties en exceptions ;
- les résultats sont renvoyés par défaut sous forme de tableaux associatifs ;
- l'émulation des requêtes préparées est désactivée.

Les requêtes sont exécutées via les requêtes préparées de PDO. Le CRUD des articles est implémenté dans [`ArticleModal`](../../../app/Models/ArticleModal.php).

En cas de `PDOException`, les informations techniques sont consignées dans `logs/database-errors.txt`, puis une `DatabaseException` conservant l'exception d'origine est levée. `Dispatcher` sait que l'erreur de base de données est déjà journalisée : il ne la consigne pas une seconde fois et renvoie à l'utilisateur une réponse `500` sûre.

## View et envoi de la réponse

[`View`](../../../core/src/View.php) reçoit une `Page`, rend la vue et le layout au moyen de tampons de sortie, puis renvoie une `Response` prête à l'emploi.

Si une erreur survient pendant la construction de la page :

- `View` nettoie uniquement les tampons de sortie qu'elle a ouverts ;
- la cause technique est journalisée ;
- l'utilisateur reçoit une réponse `500` sûre.

L'envoi normal de la réponse HTTP s'effectue uniquement dans [`public/index.php`](../../../public/index.php) :

```text
http_response_code()
→ header()
→ echo
```

Autrement dit, la construction de la réponse est séparée de son envoi effectif au client.

## Validation, CSRF et affichage des données

[`ArticleValidate`](../../../app/Validations/ArticleValidate.php) valide les données de l'article côté serveur. En cas d'échec, le contrôleur conserve les valeurs saisies et les messages dans la session, puis renvoie une redirection.

[`SecurityHelper`](../../../app/Helper/SecurityHelper.php) crée et valide le jeton CSRF. Les opérations de modification `create`, `edit` et `delete` effectuent cette vérification avant toute écriture dans PostgreSQL.

Les valeurs dynamiques sont échappées à l'affichage. Le contenu des articles est stocké sous forme de texte : les sauts de ligne sont convertis à l'affichage et le HTML arbitraire fourni par l'utilisateur n'est pas exécuté.

## Limites de gestion des erreurs

Le projet distingue les réponses HTTP normales des erreurs réelles :

```text
redirection / 403 / 405
    → Response ordinaire
    → sans entrée dans le journal d'erreurs

erreur PostgreSQL / PDO
    → entrée dans le journal de la base de données
    → DatabaseException
    → réponse 500 sûre

erreur du contrôleur ou de Dispatcher
    → entrée dans le journal de Dispatcher
    → réponse 500 sûre

erreur de la vue ou du layout
    → entrée dans le journal de View
    → réponse 500 sûre
```

Les messages techniques des exceptions ne doivent pas figurer dans la réponse HTML envoyée à l'utilisateur.

## Ce qui reste volontairement simple

Le projet reste modeste : il n'introduit donc pas de couches supplémentaires uniquement pour la symétrie architecturale :

- pas de conteneur de dépendances ;
- pas d'ORM ;
- pas de couche de repository distincte au-dessus de chaque modèle ;
- pas de chaîne de middleware ;
- pas de bus d'événements ;
- pas de tâches en arrière-plan ;
- pas d'API REST ni de SPA distinctes.

Dans une application plus importante, certaines de ces limites pourraient être utiles. Ici, elles ne feraient que masquer le flux MVC principal derrière une infrastructure supplémentaire.

Pour les commandes de démarrage, la base de test, la couverture et la CI, consultez le [guide de développement](development.md).
