# ADR 0010 — vue-i18n pour les textes de l'interface, layouts pilotés par props

- Statut : accepté
- Date : 2026-09-26

## Contexte

Les layouts (étape 1, tâche 4) sont le premier code d'interface qui porte ses propres textes : libellés de navigation, « Aller au contenu », « Notifications, 3 non lues »… La spécification interdit toute chaîne en dur (§10.3) et prévoit vue-i18n (§4). Les routes des espaces (`kid.*`, `parent.*`, `pro.*`) n'existeront qu'à l'étape 2.

## Décision

- **vue-i18n 11** en mode Composition (`legacy: false`), locale `fr`. Messages dans `resources/js/i18n/fr.ts`, typés : `types/i18n.d.ts` étend `DefineLocaleMessage`, donc une clé inexistante est une erreur `vue-tsc`. Les messages du serveur (validation, mails) restent dans `lang/fr`.
- Le plugin est installé dans `app.ts`, dans Storybook (`preview.ts`) et dans Vitest (`test/setup.ts`).
- Les composants `ui` restent sans texte (ADR 0009) ; seuls les layouts et les pages appellent `$t`.
- Les layouts (`KidLayout`, `ParentLayout`, `ProLayout`, `PublicLayout`) reçoivent leurs liens en props (`NavItem` : clé de libellé, `href`, icône, page courante) et les données de l'espace (utilisateur, enfants, notifications). Ils ne connaissent ni les routes ni le serveur ; les pages construiront ces listes à partir des routes nommées quand elles existeront.
- Côté enfant, aucun texte sous 18 px, navigation comprise. La barre mobile ne peut pas afficher cinq libellés à 18 px en 390 px : seul l'onglet courant montre le sien, les autres le gardent en `sr-only`. Le rail tablette passe de 112 à 136 px pour des libellés à 18 px.
- Les toasts passent par `useToasts()` (file partagée, 5 s, 8 s avec action) et une zone `ToastRegion` présente dans chaque layout.

## Conséquences

- Positives : les textes sont externalisés dès le premier écran, avec vérification des clés au typage. Les layouts sont testables et documentés dans Storybook sans routes ni serveur.
- Négatives : une dépendance de plus (~50 ko minifiés) et une compilation des messages à l'exécution (pas de précompilation par plugin Vite pour l'instant). Les pages devront construire leurs listes de liens ; un composable par espace le fera à l'étape 2.

## Alternatives envisagées

- Textes des layouts passés en props, i18n repoussé à l'étape 2 : aucune dépendance, mais des layouts à dizaines de props et une migration à faire ensuite.
- laravel-vue-i18n (fichiers `lang/` partagés avec le front) : une seule source, mais moins répandu et sans typage des clés ; la spécification retient vue-i18n.
- vue-i18n : MIT, maintenu par l'équipe intlify, ~3,4 millions de téléchargements par semaine.
