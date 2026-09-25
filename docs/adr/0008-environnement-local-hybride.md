# ADR 0008 — Environnement local hybride (PHP et PostgreSQL natifs, services Docker)

- Statut : accepté
- Date : 2026-09-25

## Contexte

La spécification prévoit Laravel Sail (tout sous Docker). Le poste de développement principal est sous Windows, sans distribution WSL ; Sail y est lent (fichiers montés depuis Windows) et PostgreSQL 18 y est déjà installé nativement.

## Décision

- Le `compose.yaml` fourni est un fichier Sail complet (application PHP 8.4, PostgreSQL 18, Redis, Meilisearch, Mailpit) : `./vendor/bin/sail up -d` reste possible sous Linux, macOS ou WSL.
- En mode hybride, PHP 8.4 et PostgreSQL 18 tournent nativement, et seuls Redis, Meilisearch et Mailpit sont lancés dans Docker (`composer services`).
- Le PostgreSQL de Sail écoute sur le port 5434 de l'hôte, pour ne pas entrer en conflit avec les instances natives (5432 et 5433).
- La CI (GitHub Actions) exécute les tests sur PostgreSQL et Redis, ce qui garantit la parité quel que soit le poste.

## Conséquences

- Positives : bonnes performances sous Windows, pas de réinstallation de PostgreSQL ; les autres développeurs peuvent utiliser Sail.
- Négatives : deux façons de démarrer, à documenter dans le README ; les versions de PHP et PostgreSQL natives doivent rester alignées sur celles de Sail et de la CI.

## Alternatives envisagées

- Sail complet sous WSL : fidèle à la spécification, mais demande d'installer une distribution Linux et d'y déplacer le projet.
- Tout natif (Redis, Meilisearch, Mailpit installés sous Windows) : installations manuelles, écart plus grand avec la CI.
