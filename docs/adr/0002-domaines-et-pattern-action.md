# ADR 0002 — Organisation par domaines et pattern Action

- Statut : accepté
- Date : 2026-09-25

## Contexte

Les règles métier (validation adulte des activités, calcul de maîtrise, partage limité dans le temps…) sont nombreuses et sensibles. Rangées par type technique, elles se dispersent entre contrôleurs, modèles et composants.

## Décision

- Le code métier vit dans `app/Domain/<Domaine>` (`Models`, `Actions`, `Data`, `Enums`, `Events`, `Jobs`, `Policies`, `Queries`).
- Toute opération métier est une classe `Action` invocable, nommée par un verbe (`ApproveActivity`).
- Les contrôleurs sont minces : valider → autoriser → appeler une Action → répondre avec Inertia.
- Un domaine n'écrit pas dans les modèles d'un autre : il passe par une Action publique de ce domaine ou réagit à un Event.
- Ces règles sont vérifiées par des tests d'architecture Pest (`tests/Architecture`).

## Conséquences

- Positives : une règle = un fichier, testable isolément ; la liste des Actions décrit ce que fait un domaine.
- Négatives : plus de fichiers et un peu de cérémonie pour les opérations simples.

## Alternatives envisagées

- Structure Laravel par défaut (logique dans les contrôleurs et les modèles) : ne tient pas à l'échelle d'une équipe.
- Services « fourre-tout » par domaine : classes qui grossissent sans limite, dépendances floues.
