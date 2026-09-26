# Domaine Curriculum

Référentiel pédagogique (ADR 0016) : matières, compétences, niveaux, univers et passions. C'est une donnée de base : les autres domaines le lisent, lui ne dépend d'aucun autre.

## Règles

- **Source** : un fichier YAML par matière dans `database/data/curriculum/` (maths, français). Version 1 rédigée d'après les programmes officiels : **à valider par l'équipe pédagogique avant production**.
- **Chargement** : `php artisan curriculum:sync` (à chaque déploiement, et après toute modification des fichiers) ; aussi lancé par `db:seed`. Idempotent ; tout est validé avant écriture.
- **Compétences** : code stable `MATIÈRE.NIVEAU.DOMAINE.CLÉ` (ex. `MATH.CE2.PROB.2STEPS`), jamais réutilisé ; niveaux « ce2 » ou « ce1-ce2 ». Les domaines (« Nombres et calculs ») sont des compétences sans parent ni niveau. Une compétence retirée du fichier est marquée `retired_at`, jamais supprimée.
- **Portées utiles** : `Skill::active()` (en vigueur, hors domaines), `Skill::forGrade($grade)` (plage de niveaux qui contient la classe).
- **Niveaux** : Enum `Grade` (CP à 3e), `rank()`, `cycle()` (2, 3 ou 4, déduit), `Grade::range($de, $à)`.
- **Univers** : `space`, `football`, `forest` (scènes de `XUniverseScene`), créés par migration et rattachés à des passions (`interests.universe_id`).
- **Libellés** : compétences et domaines dans le YAML ; matières, univers et passions dans vue-i18n (`subjects.*`, `universes.*`, `interests.*`).

## Actions

| Action | Rôle |
|---|---|
| `SyncCurriculum` | Charge les fichiers YAML : crée ou met à jour matières, domaines, compétences ; retire les compétences disparues. |

## Tests

- `tests/Feature/Curriculum/SyncCurriculumTest.php` : référentiel du dépôt (6 à 10 compétences par classe et matière, codes cohérents, idempotence), mise à jour, retrait et retour, plages de niveaux, fichiers invalides refusés sans écriture, commande, univers.
- `tests/Unit/Curriculum/GradeTest.php` : niveaux, rang, cycle, plages.
