# ADR 0016 — Référentiel pédagogique en fichiers YAML (domaine Curriculum)

- Statut : accepté
- Date : 2026-09-28

## Contexte

L'étape 4 demande un domaine `Curriculum` (matières, compétences, univers, intérêts) sur lequel reposent la bibliothèque, puis la génération d'activités et la maîtrise. La spécification (§6.4) veut un référentiel **donnée** versionné dans `database/data/curriculum/*.yaml`, que l'équipe pédagogique fait évoluer par pull request, et dont l'IA ne décide jamais. Choix de l'utilisateur (2026-09-26) : un socle maths + français, du CP à la 3e, 6 à 10 compétences par classe, d'après les programmes officiels, à valider par l'équipe pédagogique.

## Décision

- **Un fichier YAML par matière** : la matière (clé, jeton de couleur, icône, position), puis des **domaines** (« Nombres et calculs ») qui regroupent des **compétences** (code stable `MATIÈRE.NIVEAU.DOMAINE.CLÉ`, libellé, niveaux « ce2 » ou « ce1-ce2 »). Un domaine est une ligne `skills` sans parent ni niveau (`parent_skill_id` de la spécification).
- **Chargement par une Action idempotente** `SyncCurriculum`, lancée par `php artisan curriculum:sync` (à chaque déploiement) et par `db:seed`. Tout le dossier est validé avant la moindre écriture ; une erreur nomme le fichier et le code fautifs. Mise à jour par clé ou par code.
- **Une compétence n'est jamais supprimée** : absente des fichiers, elle est marquée `retired_at` (les activités et la maîtrise y feront référence) ; elle revient si on la remet. Un code ne doit jamais être réutilisé pour une autre compétence.
- **Libellés** : ceux des compétences et des domaines viennent du YAML (contenu pédagogique, trop volumineux pour vue-i18n, relu par l'équipe pédagogique). Les petites listes fixes gardent une clé et un libellé vue-i18n : matières (`subjects.<clé>`), univers (`universes.<clé>`), passions (`interests.<clé>`).
- **Univers** (`space`, `football`, `forest`, ceux de `XUniverseScene`) : créés par migration, comme les passions, auxquelles ils sont rattachés (`interests.universe_id`).
- **Déplacements** : l'Enum `Grade` (avec `rank()`, `cycle()`, `range()`) et le modèle `Interest` passent de `Children` à `Curriculum`. Le référentiel ne dépend d'aucun autre domaine (règle d'architecture) ; `Children` le lit.
- **Identifiants** : entiers pour ces données de référence, qui n'apparaissent pas dans des adresses privées. Les ULID de la spécification (§6.1) arriveront avec les activités.
- **Dépendance** : `symfony/yaml` (déjà présent en dev, désormais en production) : composant Symfony, licence MIT, maintenu par l'équipe Symfony, utilisé par Laravel lui-même en outillage.

## Conséquences

- Positives : l'équipe pédagogique relit et corrige le référentiel par pull request, avec un diff lisible ; aucune donnée perdue à la synchronisation ; des tests vérifient la forme (6 à 10 compétences par classe et matière, codes cohérents).
- Négatives : le contenu de la version 1 n'est pas validé par des enseignants (à faire avant production) ; le cycle n'est pas stocké mais déduit du niveau (`Grade::cycle()`).

## Alternatives envisagées

- Référentiel saisi dans une interface d'administration : pas d'historique ni de relecture, et une interface à construire.
- Suppression des compétences retirées : casserait les activités et l'historique de maîtrise qui y font référence.
- Libellés des compétences dans vue-i18n : des centaines de clés sans relecture pédagogique possible dans le même fichier.
