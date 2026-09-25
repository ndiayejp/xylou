# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue

Toutes les interactions avec l'utilisateur se font en français.

## Contexte

Xylou : plateforme EdTech d'accompagnement scolaire personnalisé par IA (espaces enfant / parent / professionnel).
Spécifications complètes et plan de réalisation : `docs/SPECIFICATIONS.md` — les lire avant toute tâche structurante.
Les maquettes UX/UI (PDF) seront ajoutées dans `docs/` au moment de travailler sur les composants et templates.

## État actuel vs cible

Étape 0 (§13) réalisée localement : Laravel 12 + Breeze (Inertia 2 + Vue 3 **TypeScript**), `strict_types` partout (règle Pint + test d'architecture), PHP 8.4, Tailwind 3 via PostCSS, Pest 3, Larastan niveau 8, Rector, ESLint/Prettier, Vitest, Playwright + axe, `app/Domain/*` (dossiers vides), `lang/fr` (via `laravel-lang/common`), CI GitHub Actions, ADR 0001–0005 et 0008. Reste à valider : la CI n'a jamais tourné, faute de dépôt GitHub distant.

**Pas encore en place** (arrive avec l'étape qui en a besoin, chaque dépendance justifiée) : spatie/laravel-data et typescript-transformer (donc pas de `generated.d.ts` ni de `typescript:transform`), spatie/laravel-permission, vue-i18n, Horizon, Reverb, Scout/Meilisearch, jetons Tailwind Xylou, `Components/ui`. Quand une règle ci-dessous dépend d'un outil absent, le signaler plutôt que l'ignorer en silence.

Environnement local hybride (ADR 0008) :
- PHP 8.4 est dans `C:\php84`. Le `php` du PATH peut encore être celui de XAMPP (8.2), qui échoue sur le contrôle de plateforme de Composer. Préfixer si besoin : `export PATH=/c/php84:$PATH` (Git Bash).
- PostgreSQL 18 natif Windows : service `postgresql-x64-18`, port **5433**, base, utilisateur et mot de passe `xylou`. Un PostgreSQL 17 sans rapport occupe le port 5432.
- Redis, Meilisearch et Mailpit tournent dans Docker via `composer services` ; Mailpit reçoit les mails (SMTP 1025, interface http://localhost:8025). Le `compose.yaml` reste un fichier Sail complet ; son PostgreSQL écoute sur le port 5434.
- Sessions, cache et files d'attente utilisent encore le driver `database`.
- `User` implémente `MustVerifyEmail` : l'inscription envoie un mail de vérification, et `dashboard` exige une adresse vérifiée.
- Tests PHP en local sur SQLite en mémoire (`phpunit.xml`), en CI sur PostgreSQL (les variables d'environnement de la CI prennent le pas sur `phpunit.xml`). Tout SQL propre à PostgreSQL passe donc en CI mais pas en local.

## Stack cible

Laravel (PHP ≥ 8.3, `strict_types`) · PostgreSQL · Redis/Horizon · Reverb · Inertia · Vue 3 + TypeScript (`<script setup lang="ts">`) · Tailwind (jetons Xylou) · Pest · Larastan · Vitest · Playwright + axe.

## Règles d'architecture (obligatoires)

- Code métier dans `app/Domain/<Domaine>/Actions` ; contrôleurs minces (valider → autoriser → Action → Inertia).
- Un domaine n'écrit pas dans les modèles d'un autre domaine : passer par une Action ou un Event.
- DTO avec spatie/laravel-data ; types TS générés (ne jamais éditer `resources/js/types/generated.d.ts`).
- Statuts = Enums PHP ; transitions d'activité uniquement via Actions.
- Services externes (IA, PDF, paiement) derrière une interface ; utiliser les implémentations Fake en test.
- Composants `resources/js/Components/ui` : aucun appel serveur, aucune logique métier.
- Aucune chaîne en dur : `lang/fr` et vue-i18n.

## Invariants produit (ne jamais enfreindre)

- Aucun classement/comparaison entre enfants. Pas de note : niveaux de maîtrise uniquement.
- Aucune activité générée par IA visible par l'enfant sans approbation adulte.
- Côté enfant, jamais « Faux » : « Pas encore » + explication + nouvel essai.
- Aucune donnée identifiante (nom, e-mail, observations) envoyée au fournisseur IA.
- Accès professionnel : vérifier partage actif, non expiré, non révoqué, scope correct.

## Façon de travailler

1. Travailler étape par étape selon `docs/SPECIFICATIONS.md` §13 ; annoncer l'étape et la tâche. Ne pas commencer une étape avant que la précédente respecte la Definition of Done.
2. Avant de coder : lire les fichiers concernés, proposer un plan court.
3. Écrire les tests en même temps que le code (Pest / Vitest / Playwright selon le cas).
4. Avant de conclure : lancer toutes les vérifications disponibles (voir Commandes) ; tout doit passer.
5. Respecter la Definition of Done (§12.2), y compris les états d'interface (§10.4).
6. Ne pas ajouter de dépendance sans justification écrite (maintenance, popularité, licence).
7. Toute décision structurante → nouvel ADR dans `docs/adr/` (modèle en §15).
8. Commits au format Conventional Commits, message court sur une ligne, sans signature (`Co-Authored-By` ou autre) ; PR petites et ciblées.
9. Commentaires dans le code au plus simple : seulement quand c'est utile, pas de docblocks superflus.
10. En cas d'ambiguïté fonctionnelle : demander plutôt que supposer.

## Commandes

```bash
composer services         # Redis, Meilisearch, Mailpit (Docker)
composer setup            # dépendances, .env, key:generate, migrate, npm install + build
composer dev              # artisan serve + queue:listen + pail (logs) + vite en parallèle

composer test             # config:clear puis php artisan test (suites Architecture, Unit, Feature)
php artisan test --testsuite=Architecture
php artisan test --filter="profile page is displayed"  # un test Pest par description
vendor/bin/pest tests/Feature/ProfileTest.php          # un fichier

composer lint             # pint --test          · composer format → pint
composer analyse          # Larastan niveau 8 (app, config, database, routes ; pas tests/)
composer refactor:check   # rector --dry-run     · composer refactor → applique

npm run lint              # ESLint               · npm run lint:fix
npm run format:check      # Prettier             · npm run format
npm run typecheck         # vue-tsc --noEmit
npm run test              # Vitest (resources/js/**/*.spec.ts)
npm run test:e2e          # Playwright + axe (tests/Browser) ; lance artisan serve, exige un build et une base migrée
npm run build             # vue-tsc puis vite build
```

Avant de conclure une tâche : `composer lint && composer analyse && composer refactor:check && composer test && npm run lint && npm run format:check && npm run typecheck && npm run test` (et `npm run test:e2e` si l'UI est touchée).

## Architecture actuelle (Breeze)

- **Inertia :** contrôleurs et closures renvoient `Inertia::render('Nom', props)` ; `resources/js/app.ts` résout `Nom` vers `resources/js/Pages/Nom.vue` (sous-dossiers possibles : `Auth/Login`, `Profile/Edit`). Pas de routeur client ni d'API JSON pour le web — les données arrivent en props.
- **Props partagées :** `app/Http/Middleware/HandleInertiaRequests.php` (ex. `auth.user`).
- **Routes côté Vue :** helper Ziggy `route()` disponible partout (enregistré dans `app.ts`, typé dans `resources/js/types/global.d.ts`) — utiliser les routes nommées, jamais d'URL en dur.
- **Routes :** `routes/web.php` (pages ; `dashboard` exige `auth` + `verified`) et `routes/auth.php` (auth Breeze), contrôleurs dans `app/Http/Controllers/Auth/`, validation dans `app/Http/Requests/`.
- **Front :** layouts `Layouts/AuthenticatedLayout.vue` et `Layouts/GuestLayout.vue`, composants Breeze dans `resources/js/Components/`, alias `@/` → `resources/js/`. Vue racine unique : `resources/views/app.blade.php`.
- **Utilisateur connecté :** l'injecter avec `#[CurrentUser] User $user` plutôt que `$request->user()` (non nullable pour Larastan).
- **Tests :** `tests/Pest.php` applique `Tests\TestCase` + `RefreshDatabase` à `tests/Feature` ; les tests Unit ne démarrent pas le framework ; `tests/Architecture` contient les règles `arch()` (à enrichir à chaque nouveau domaine).
- **ESLint :** `vue/multi-word-component-names` est désactivée pour `Pages/` ; les composants Breeze `Checkbox`, `Dropdown` et `Modal` en sont exemptés jusqu'à leur remplacement par `Components/ui`.
