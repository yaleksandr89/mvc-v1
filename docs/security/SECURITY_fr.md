# Sécurité

## Choisir une langue

| Русский | English | Español | 中文 | Français | Deutsch |
|---|---|---|---|---|---|
| [Русский](../../.github/SECURITY.md) | [English](SECURITY_en.md) | [Español](SECURITY_es.md) | [中文](SECURITY_zh.md) | **Français** | [Deutsch](SECURITY_de.md) |

Veuillez signaler les vulnérabilités potentielles de manière responsable. `mvc-v1` est un projet MVC pédagogique, mais les problèmes de sécurité dans le routing, le CRUD, l'accès PostgreSQL, le traitement des entrées utilisateur et le rendu des données sont traités aussi sérieusement que dans tout dépôt public.

## Ce qu'il vaut mieux signaler en privé

- Un contournement de la protection CSRF lors de la création, modification ou suppression d'un article.
- Une injection SQL ou la possibilité d'influencer des identifiants SQL ou paramètres de requête.
- XSS stockée ou réfléchie, contournement de l'échappement HTML ou sortie non sûre de données utilisateur.
- La possibilité de remplacer un paramètre route/path par query string ou d'agir sur une autre entité.
- La divulgation de chemins internes, exceptions, configuration, contenu `.env`, identifiants PostgreSQL ou autre information sensible.
- Une faille de session, token CSRF ou méthode HTTP permettant un changement d'état indésirable.
- Un problème de dépendance exploitable affectant matériellement ce projet.
- La compromission du code source, de CI, de la chaîne de dépendances ou d'un autre élément de supply chain.

## Ce qui peut être publié dans les Issues

- Une erreur de routing sans impact de sécurité.
- Un comportement CRUD ou une erreur de validation sans impact de sécurité.
- Un problème de pagination, rendu ou interface.
- Un problème de compatibilité PHP ou PostgreSQL sans divulgation de données sensibles.
- Une erreur de documentation.
- Une demande de fonctionnalité ou d'amélioration.

En cas de doute sur l'impact sécurité, utilisez d'abord le canal privé.

## Comment signaler

- Si la section Security du dépôt propose un formulaire privé, utilisez-le en premier.
- Ne publiez pas de code d'exploit, secrets réels, contenu `.env` de production, mots de passe PostgreSQL, identifiants de session ou autres données sensibles dans les Issues, Discussions, Pull Requests ou logs.
- Si le formulaire privé est temporairement indisponible, créez une Issue publique minimale sans détails d'exploitation et demandez un canal de contact privé.
- Ne divulguez pas publiquement les détails techniques d'exploitation avant accord avec le mainteneur et disponibilité d'un correctif.

## Éléments à inclure

Indiquez si possible :

- la version affectée ou SHA du commit ;
- la version de PHP ;
- la version de PostgreSQL si le problème concerne la base de données ;
- la zone affectée : routing, controller, model/PDO, validation, view/output, session/CSRF, configuration ou dependency chain ;
- l'impact ;
- une reproduction minimale ;
- un extrait assaini de requête, réponse ou log si utile.

Utilisez uniquement des données synthétiques. N'incluez jamais de vrais mots de passe, tokens, cookies, IDs de session, contenu `.env` de production ou autres secrets.

## Suite donnée

- Le projet a un seul mainteneur ; aucun SLA n'est garanti.
- Le mainteneur essaiera d'accuser réception, reproduire le problème, évaluer l'impact et préparer un correctif.
- Aucun programme de récompense n'est promis.
- Coordonnez la divulgation publique jusqu'à disponibilité d'un correctif.
