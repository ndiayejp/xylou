# ADR 0003 — PostgreSQL

- Statut : accepté
- Date : 2026-09-25

## Contexte

Les contenus d'activité sont semi-structurés (items, indices, explications selon le format) et la progression demande des requêtes analytiques par compétence et par période.

## Décision

PostgreSQL (version ≥ 16 ; 18 en local et en CI) est la base de données de l'application.

## Conséquences

- Positives : colonnes `jsonb` indexables pour les contenus, contraintes solides, fonctions analytiques et fenêtrées pour la progression.
- Négatives : les tests locaux tournent sur SQLite en mémoire pour la rapidité, alors que la CI les exécute sur PostgreSQL. Tout SQL propre à PostgreSQL doit donc être couvert par la CI, ou les tests locaux basculés sur PostgreSQL.

## Alternatives envisagées

- MySQL/MariaDB : viable, mais JSON et analytique moins adaptés aux contenus d'activité et à la progression.
