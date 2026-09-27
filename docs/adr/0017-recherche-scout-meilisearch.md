# ADR 0017 — Recherche de la bibliothèque : Scout, Meilisearch et mots-clés compris

- Statut : accepté
- Date : 2026-09-28

## Contexte

L'étape 4 demande une recherche dans la bibliothèque, tolérante aux fautes, et une « recherche en langage naturel simple (analyse des mots-clés → filtres) ». La spécification (§3) retient Laravel Scout et Meilisearch ; Meilisearch tourne déjà en local (`composer services`). Les filtres (matière, niveau, durée…) existent en base depuis la PR 3.

## Décision

- **Plein texte par Scout + Meilisearch** : le modèle `Activity` est `Searchable`. L'index `activities` contient le titre, la compétence, l'objectif, la matière, l'univers et l'auteur (`owner_id`, filtrable) ; rien sur l'enfant. `soft_delete` actif : la corbeille reste cherchable, et l'effacement définitif (`model:prune`) retire le document.
- **Deux temps** : Meilisearch renvoie les identifiants pertinents (500 au plus) de l'auteur ; la requête Eloquent (`LibraryQuery`) applique ensuite les filtres et garde l'ordre de pertinence. Les filtres restent en base, une seule source de vérité.
- **Mots-clés compris, sans IA** : `InterpretLibrarySearch` reconnaît des mots (sans accents ni casse) pour la matière, la classe, le prénom d'un enfant du parent (→ sa classe), la durée (« court » ≤ 10 min, « long » ≥ 15 min, « 15 min »), la difficulté et l'univers ; le reste, sans mots vides, devient le texte cherché. La phrase (`ask`) redirige vers l'adresse filtrée : les pastilles restent la référence et s'enlèvent une à une ; la page affiche « Compris comme … » une fois.
- **Tests** : driver `collection` de Scout dans `phpunit.xml` (sans service) ; Meilisearch réel dans le job E2E de la CI et en local (`php artisan scout:sync-index-settings`, puis `scout:import`).
- **Dépendances** :
  - `laravel/scout` (MIT, maintenu par l'équipe Laravel) : indexation automatique des modèles, moteur remplaçable.
  - `meilisearch/meilisearch-php` (MIT, client officiel de Meilisearch) : requis par le moteur Meilisearch de Scout.
  - `http-interop/http-factory-guzzle` (MIT) : fabriques PSR-17 pour Guzzle, déjà présent via Laravel ; le client Meilisearch en a besoin pour son transport HTTP.

## Conséquences

- Positives : recherche tolérante aux fautes et aux accents (« fusee », « capitain ») ; le moteur se change par configuration ; l'interprétation est prévisible, testée mot par mot, et corrigeable par l'utilisateur.
- Négatives : un service de plus à exploiter (index à synchroniser au déploiement) ; le driver `collection` des tests ne reproduit pas la pertinence de Meilisearch ; le vocabulaire compris est une liste à enrichir.

## Alternatives envisagées

- `ILIKE` en PostgreSQL : pas de tolérance aux fautes, pas de pertinence.
- Recherche plein texte PostgreSQL (`tsvector`) : bonne option sans service de plus, mais sans tolérance aux fautes et loin du choix de la spécification.
- Interprétation par l'IA : coût et latence à chaque recherche, résultats moins prévisibles ; inutile pour une liste de mots-clés.
- Thème compris comme simple priorité de tri (maquette) : moins lisible que la pastille ; écarté par l'utilisateur.
