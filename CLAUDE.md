# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue

Toutes les interactions avec l'utilisateur se font en français.

## Contexte

Xylou : plateforme EdTech d'accompagnement scolaire personnalisé par IA (espaces enfant / parent / professionnel).
Spécifications complètes et plan de réalisation : `docs/SPECIFICATIONS.md` — les lire avant toute tâche structurante.
Les maquettes UX/UI (PDF) seront ajoutées dans `docs/` au moment de travailler sur les composants et templates.

## État actuel vs cible

Le projet sort tout juste de l'installation : Laravel 12 + Breeze (Inertia 2 + Vue 3 en **JavaScript**), Tailwind 3 via PostCSS, Pest 3, Pint. Base de données : PostgreSQL 18 natif Windows (service `postgresql-x64-18`, port **5433**, base/utilisateur/mot de passe `xylou`) ; un PostgreSQL 17 sans rapport occupe le port 5432. Tests sur SQLite en mémoire (`phpunit.xml`) — éviter le SQL propre à Postgres dans ce que les tests exécutent, ou basculer les tests sur Postgres.

L'étape 0 du plan (§13) reste à faire. **Pas encore en place** : TypeScript, `strict_types`, PHP ≥ 8.3 (`composer.json` accepte `^8.2`), Sail, Redis/Horizon, Reverb, Larastan, Rector, spatie/laravel-data et typescript-transformer, spatie/laravel-permission, vue-i18n, dossier `lang/` (à publier via `php artisan lang:publish`), ESLint/Prettier, Vitest, Playwright + axe, `app/Domain/*`, CI GitHub Actions, `docs/adr/`. Les scripts `composer lint`, `composer analyse`, `npm run lint`, `npm run typecheck`, `npm run test`, `npm run test:e2e` n'existent pas encore. Quand une règle ci-dessous dépend d'un outil absent, le signaler plutôt que l'ignorer en silence.

`@tailwindcss/vite` v4 figure dans `package.json` mais n'est pas branché dans `vite.config.js`.

Environnement local : le PHP CLI est celui de XAMPP (`C:\xampp\php`, **8.2.12**) — passer à PHP ≥ 8.3 imposera de changer d'interpréteur, pas seulement `composer.json`. Sessions, cache et files d'attente utilisent le driver `database` (d'où `queue:listen` dans `composer dev`), les mails partent dans les logs (`MAIL_MAILER=log`). `.env.example` est encore en `APP_LOCALE=en` : à passer en `fr`.

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

Disponibles aujourd'hui :

```bash
composer setup            # dépendances, .env, key:generate, migrate, npm install + build
composer dev              # artisan serve + queue:listen + pail (logs) + vite en parallèle
npm run dev               # serveur Vite seul
npm run build             # build des assets

composer test             # config:clear puis php artisan test
php artisan test --filter=ProfileTest                  # un fichier/une classe
php artisan test --filter="profile page is displayed"  # un test Pest par description
vendor/bin/pest tests/Feature/Auth/LoginTest.php       # Pest directement sur un fichier

vendor/bin/pint           # formatage PHP
vendor/bin/pint --dirty   # uniquement les fichiers modifiés
```

Cibles (après l'étape 0) :

- Démarrer : `./vendor/bin/sail up -d && ./vendor/bin/sail composer setup`
- Tests PHP : `sail pest` · Front : `npm run test` · E2E : `npm run test:e2e`
- Qualité : `sail composer lint && sail composer analyse && npm run lint && npm run typecheck`
- Types TS : `sail artisan typescript:transform`

## Architecture actuelle (Breeze)

- **Inertia :** contrôleurs et closures renvoient `Inertia::render('Nom', props)` ; `resources/js/app.js` résout `Nom` vers `resources/js/Pages/Nom.vue` (sous-dossiers possibles : `Auth/Login`, `Profile/Edit`). Pas de routeur client ni d'API JSON pour le web — les données arrivent en props.
- **Props partagées :** `app/Http/Middleware/HandleInertiaRequests.php` (ex. `auth.user`).
- **Routes côté Vue :** helper Ziggy `route()` disponible partout (enregistré dans `app.js`) — utiliser les routes nommées, jamais d'URL en dur.
- **Routes :** `routes/web.php` (pages ; `dashboard` exige `auth` + `verified`) et `routes/auth.php` (auth Breeze), contrôleurs dans `app/Http/Controllers/Auth/`, validation dans `app/Http/Requests/`.
- **Front :** layouts `Layouts/AuthenticatedLayout.vue` et `Layouts/GuestLayout.vue`, composants Breeze dans `resources/js/Components/`, alias `@/` → `resources/js/`. Vue racine unique : `resources/views/app.blade.php`.
- **Tests :** `tests/Pest.php` applique `Tests\TestCase` + `RefreshDatabase` à `tests/Feature` ; les tests Unit ne démarrent pas le framework.
