# Domaine Activities

Activités (un ensemble d'items sur une compétence, dans un univers, avec un format et une difficulté) et leur cycle de vie (§6.3). Il lit `Curriculum`, `Children` et `Identity`, sans y écrire.

## Règles

- **Identifiants** : ULID pour `activities` et `activity_items` (non devinables dans les URL).
- **Statuts** : Enum `ActivityStatus`, transitions dans `allowedTransitions()` / `canTransitionTo()`. Le statut ne change **que** par `ChangeActivityStatus`, qui refuse toute transition interdite (`ActivityRuleViolation`) et la journalise.

  | De | Vers |
  |---|---|
  | `generating` | `pending_review`, `generation_failed` |
  | `generation_failed` | `generating` (régénérer) |
  | `pending_review` | `approved`, `draft` (modifier), `generating` (régénérer) |
  | `draft` | `pending_review`, `approved` (l'adulte enregistre ce qu'il a écrit ou modifié) |
  | `approved` | `archived` |
  | `archived` | `approved` |

- **Validation** : une activité sans item ne passe ni en `pending_review` ni en `approved`.
- **Création manuelle** (`CreateActivity`) : brouillon, source `manual` ; la matière suit toujours la compétence ; compétence en vigueur et hors domaine ; durée 5, 10, 15 ou 20 min ; l'enfant visé appartient à l'auteur. Sans enfant, l'activité est un modèle de bibliothèque.
- **Duplication** : copie des items, en brouillon, source `duplicate`, à l'adulte qui duplique, sans enfant ni relecture. Impossible tant que l'activité n'a pas de contenu (`generating`, `generation_failed`).
- **Archivage** : `approved` ↔ `archived` (`ArchiveActivity`, `UnarchiveActivity`).
- **Corbeille** : `DeleteActivity` (soft delete, tout statut) ; `RestoreActivity` la rend avec son statut pendant 30 jours ; ensuite `model:prune` (planifié chaque jour) l'efface avec ses items.
- **Autorisations** (`ActivityPolicy`) : parents et professionnels créent ; seul l'auteur voit, modifie, duplique, archive, supprime, restaure.
- **Journal** `activities` : `created`, `duplicated`, `status_changed` (`from`, `to`), `deleted`, `restored`, avec l'auteur. L'ULID est dans les propriétés (`activity`), car `subject_id` du journal est un entier. Jamais de titre ni de contenu : ils peuvent citer l'enfant.
- **Types de réponse** : Enum `AnswerType` (§15.3) ; la forme de `expected_answer` sera fixée avec l'éditeur (PR 5) et le moteur d'exercice (étape 6).

## Bibliothèque

- Routes `parent.library` et `pro.library` (`LibraryController`, page `Library/Index`) ; actions communes sous `activities.*` (`destroy`, `duplicate`, `archive`, `unarchive`, `restore` lié avec `withTrashed`), réservées à l'auteur.
- `LibraryQuery` : activités de l'adulte, filtrées (matière, compétence, niveau, durée, difficulté, thème, statut), les plus récentes d'abord, 24 par page. Sans statut : tout sauf les archives ; statut `deleted` : la corbeille, avec la date d'effacement définitif.
- Filtres dans l'URL (`LibraryRequest`) ; une valeur inconnue est ignorée. Espace parent : sans `grade` dans l'URL, le niveau est celui de l'enfant courant ; `grade=` l'efface.
- Filtre « Compétence » : seulement les compétences des activités de l'adulte (dans la matière choisie).
- Une action devenue impossible (`ActivityRuleViolation`) revient avec l'erreur `activity` (`lang/fr/activities.php`), affichée en toast.
- Suppression et archivage proposent « Annuler » dans le toast (8 s). « Modifier » (éditeur, PR 5), « Recommander » (étape 6) et « Sauvegarder » (étape 9) n'apparaissent pas encore. Recherche plein texte : PR 4.

## Actions

| Action | Rôle |
|---|---|
| `CreateActivity` | Crée une activité manuelle en brouillon, avec ses items. |
| `DuplicateActivity` | Copie une activité et ses items en brouillon. |
| `ChangeActivityStatus` | Seul point de changement de statut : vérifie la transition, journalise. |
| `ArchiveActivity` | `approved` → `archived`. |
| `UnarchiveActivity` | `archived` → `approved` (refuse tout autre statut de départ). |
| `DeleteActivity` | Met à la corbeille. |
| `RestoreActivity` | Sort de la corbeille (moins de 30 jours). |

## Tests

- `tests/Unit/Activities/ActivityStatusTest.php` : les 36 couples de statuts.
- `tests/Feature/Activities/LibraryTest.php` : accès par espace, contenu, corbeille, pagination, chaque filtre, niveau pré-rempli, actions et refus.
- `tests/Browser/library.spec.ts` : filtres, suppression annulée depuis le toast, menu au clavier, axe.
- `tests/Feature/Activities/ActivityActionsTest.php` : chaque transition interdite refusée sans effet, création, duplication, archivage, corbeille et effacement à 30 jours, autorisations, journal sans donnée personnelle.
