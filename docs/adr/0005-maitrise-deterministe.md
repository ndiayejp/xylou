# ADR 0005 — Maîtrise calculée de façon déterministe (moyenne mobile exponentielle)

- Statut : accepté
- Date : 2026-09-25

## Contexte

Parents et professionnels doivent comprendre pourquoi une compétence est « En bonne voie » ou « À consolider ». Le produit exclut les notes et toute comparaison entre enfants.

## Décision

- Score de 0 à 100 par compétence, calculé par moyenne mobile exponentielle des tentatives (α = 0,3, dans `config/progress.php`).
- Poids : réussite au 1er essai = 1,0 ; après indice = 0,7 ; au 2e essai = 0,6 ; « Pas encore » = 0 ; « Je ne sais pas » n'entre pas dans le score.
- Seul le niveau de maîtrise est affiché, jamais le score : `mastered` (≥ 90), `on_track` (70–89), `consolidating` (40–69), `discovering` (< 40 ou moins de 3 tentatives).
- Le recalcul se fait sur l'événement `AttemptRecorded`. L'IA ne calcule jamais la progression.

## Conséquences

- Positives : explicable, reproductible, entièrement testable ; les seuils sont alignés sur le design.
- Négatives : modèle simple, qui ne tient pas compte de l'oubli dans le temps ni de la difficulté propre à chaque item ; tout changement de paramètres modifie les niveaux affichés.

## Alternatives envisagées

- Modèles probabilistes (Bayesian Knowledge Tracing, IRT) : plus fins, mais difficiles à expliquer et à calibrer sans données.
- Estimation par IA : non reproductible, non explicable.
