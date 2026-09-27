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

- **Validation** : une activité ne passe en `pending_review` ou `approved` que si elle a au moins une question et que toutes sont complètes (`Activity::ensureReady()`, `ActivityItem::isComplete()` : énoncé, explication, réponse attendue cohérente avec son type).
- **Formes stockées** (éditeur) : nombre `{value, unit}` + `tolerance` ; texte `{accepted: [...]}` ; choix unique `{index}` / multiple `{indexes: [...]}` avec `prompt_payload {choices: [...]}`. Ordonner, associer et texte à trous : avec le moteur d'exercice (étape 6).
- **Modification** (`UpdateActivity`) : brouillon ou activité validée seulement (`ActivityStatus::isEditable()`, aussi dans `ActivityPolicy::update`) ; champs et questions remplacés, `version` + 1, journal `updated`. Une activité validée reste validée, donc complète.
- **Création manuelle** (`CreateActivity`) : brouillon, source `manual` ; la matière suit toujours la compétence ; compétence en vigueur et hors domaine ; durée 5, 10, 15 ou 20 min ; l'enfant visé appartient à l'auteur. Sans enfant, l'activité est un modèle de bibliothèque.
- **Duplication** : copie des items, en brouillon, source `duplicate`, à l'adulte qui duplique, sans enfant ni relecture. Impossible tant que l'activité n'a pas de contenu (`generating`, `generation_failed`).
- **Archivage** : `approved` ↔ `archived` (`ArchiveActivity`, `UnarchiveActivity`).
- **Corbeille** : `DeleteActivity` (soft delete, tout statut) ; `RestoreActivity` la rend avec son statut pendant 30 jours ; ensuite `model:prune` (planifié chaque jour) l'efface avec ses items.
- **Autorisations** (`ActivityPolicy`) : parents et professionnels créent ; seul l'auteur voit, modifie, duplique, archive, supprime, restaure.
- **Journal** `activities` : `created`, `duplicated`, `status_changed` (`from`, `to`), `deleted`, `restored`, avec l'auteur. L'ULID est dans les propriétés (`activity`), car `subject_id` du journal est un entier. Jamais de titre ni de contenu : ils peuvent citer l'enfant.
- **Types de réponse** : Enum `AnswerType` (§15.3) ; la forme de `expected_answer` sera fixée avec l'éditeur (PR 5) et le moteur d'exercice (étape 6).

## Éditeur

- Routes `activities.create` / `store` et `activities.edit` / `update` (`ActivityController`, page `Activities/Edit`), communes aux deux espaces ; retour vers la bibliothèque de l'espace.
- `ActivityRequest` : `intent` = `draft` (questions incomplètes permises) ou `approve` (tout est vérifié, chaque manque signalé sur son champ, d'un coup) ; une activité déjà validée est toujours vérifiée comme `approve`. Questions « à plat » (`value`, `unit`, `tolerance`, `accepted`, `choices`, `correct`, `correct_many`), transformées en formes stockées.
- Pas de champ « Pour » : l'activité manuelle est un modèle de bibliothèque ; le niveau part de la classe de l'enfant courant pour filtrer les compétences.

## Bibliothèque

- Routes `parent.library` et `pro.library` (`LibraryController`, page `Library/Index`) ; actions communes sous `activities.*` (`destroy`, `duplicate`, `archive`, `unarchive`, `restore` lié avec `withTrashed`), réservées à l'auteur.
- `LibraryQuery` : activités de l'adulte, filtrées (matière, compétence, niveau, durée, difficulté, thème, statut), les plus récentes d'abord, 24 par page. Sans statut : tout sauf les archives ; statut `deleted` : la corbeille, avec la date d'effacement définitif.
- Filtres dans l'URL (`LibraryRequest`) ; une valeur inconnue est ignorée. Espace parent : sans `grade` dans l'URL, le niveau est celui de l'enfant courant ; `grade=` l'efface.
- Filtre « Compétence » : seulement les compétences des activités de l'adulte (dans la matière choisie).
- Une action devenue impossible (`ActivityRuleViolation`) revient avec l'erreur `activity` (`lang/fr/activities.php`), affichée en toast.
- États : chargement (squelettes), vide, aucun résultat, corbeille vide, erreur de chargement avec « Réessayer » (`useFailedVisit`, filtres conservés) ; une action en échec affiche un toast.
- Suppression et archivage proposent « Annuler » dans le toast (8 s). « Recommander » (étape 6) et « Sauvegarder » (étape 9) n'apparaissent pas encore.
- **Recherche** (ADR 0017) : `q` = plein texte (Scout + Meilisearch, index `activities` sans rien sur l'enfant, corbeille comprise) ; `ask` = phrase interprétée par `InterpretLibrarySearch` (matière, classe, prénom d'un enfant → sa classe, durée `short` ≤ 10 min / `long` ≥ 15 min, difficulté, univers ; le reste devient `q`), puis redirection vers l'adresse filtrée avec « Compris comme … » (session flash `library.understood`).

## Actions

| Action | Rôle |
|---|---|
| `CreateActivity` | Crée une activité manuelle en brouillon, avec ses items ; `approve` la valide dans la même transaction. |
| `UpdateActivity` | Modifie un brouillon ou une activité validée ; `approve` valide un brouillon. |
| `DuplicateActivity` | Copie une activité et ses items en brouillon. |
| `ChangeActivityStatus` | Seul point de changement de statut : vérifie la transition, journalise. |
| `ArchiveActivity` | `approved` → `archived`. |
| `UnarchiveActivity` | `archived` → `approved` (refuse tout autre statut de départ). |
| `DeleteActivity` | Met à la corbeille. |
| `RestoreActivity` | Sort de la corbeille (moins de 30 jours). |

## Tests

- `tests/Unit/Activities/ActivityStatusTest.php` : les 36 couples de statuts.
- `tests/Unit/Activities/InterpretLibrarySearchTest.php` : mots compris, accents, classes, durées, difficultés, texte restant.
- `tests/Feature/Activities/LibraryTest.php` : accès par espace, contenu, corbeille, pagination, chaque filtre, niveau pré-rempli, recherche et phrase interprétée, actions et refus.
- `tests/Feature/Activities/ActivityEditorTest.php` : page, quatre types de réponse, brouillon incomplet, manques signalés, compétence, modification, version, droits.
- `tests/Browser/activity-editor.spec.ts` : écrire, valider, modifier, supprimer, axe.
- `tests/Browser/library.spec.ts` : filtres, suppression annulée depuis le toast, menu au clavier, axe.
- `tests/Feature/Activities/ActivityActionsTest.php` : chaque transition interdite refusée sans effet, création, duplication, archivage, corbeille et effacement à 30 jours, autorisations, journal sans donnée personnelle.
