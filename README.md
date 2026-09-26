# Xylou

Plateforme d'accompagnement scolaire personnalisé par IA, avec trois espaces connectés : enfant, parent et professionnel.

- Spécifications et plan de réalisation : [`docs/SPECIFICATIONS.md`](docs/SPECIFICATIONS.md)
- Décisions d'architecture : [`docs/adr/`](docs/adr/)
- Consignes pour Claude Code : [`CLAUDE.md`](CLAUDE.md)

Stack : Laravel 12 (PHP 8.4) · PostgreSQL 18 · Inertia 2 · Vue 3 + TypeScript · Tailwind · Pest · Larastan · Vitest · Playwright + axe.

## Démarrage

Deux modes, décrits dans l'[ADR 0008](docs/adr/0008-environnement-local-hybride.md).

### Mode hybride (Windows) : PHP et PostgreSQL natifs

Prérequis : PHP ≥ 8.3 dans le `PATH` (avec `pdo_pgsql`, `intl`, `zip`), Composer, Node 24, PostgreSQL ≥ 16, Docker Desktop.

```bash
# 1. Base de données (une seule fois)
psql -U postgres -p 5433 -c "CREATE USER xylou WITH PASSWORD 'xylou';"
psql -U postgres -p 5433 -c "CREATE DATABASE xylou OWNER xylou;"

# 2. Services Docker : Redis, Meilisearch, Mailpit
composer services

# 3. Dépendances, .env, clé, migrations, build
composer setup

# 4. Serveur, file d'attente et Vite
composer dev
```

L'application tourne sur http://127.0.0.1:8000. Les e-mails (vérification d'adresse, mot de passe oublié) arrivent dans Mailpit : http://localhost:8025. Comptes de démonstration après `php artisan db:seed` : `parent@example.com` et `pro@example.com`, mot de passe `password`.

Les logs sont dans `storage/logs/laravel.log`. `composer logs` les affiche en direct avec Pail, qui exige l'extension `pcntl` : indisponible sous Windows, elle fonctionne sous Linux, macOS et WSL.

Le `.env.example` pointe sur PostgreSQL au port **5433** ; adapter `DB_PORT` si votre instance écoute ailleurs.

### Mode Sail (Linux, macOS, WSL)

Dans `.env`, remplacer les hôtes locaux par ceux des conteneurs : `DB_HOST=pgsql`, `DB_PORT=5432`, `REDIS_HOST=redis`, `MAIL_HOST=mailpit`. Puis :

```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer setup
```

## Commandes

| Besoin | Commande |
|---|---|
| Tests PHP (architecture, unitaires, fonctionnels) | `composer test` |
| Un test précis | `php artisan test --filter="profile page is displayed"` |
| Formatage PHP (vérifier / corriger) | `composer lint` / `composer format` |
| Analyse statique (Larastan niveau 8) | `composer analyse` |
| Rector (vérifier / appliquer) | `composer refactor:check` / `composer refactor` |
| Lint front (vérifier / corriger) | `npm run lint` / `npm run lint:fix` |
| Formatage front (vérifier / corriger) | `npm run format:check` / `npm run format` |
| Vérification des types | `npm run typecheck` |
| Tests front (Vitest) | `npm run test` |
| Tests E2E + accessibilité | `npm run test:e2e` |
| Catalogue des composants (Storybook) | `npm run storybook` (http://localhost:6006) / `npm run build-storybook` |

Les tests E2E démarrent `php artisan serve` si aucun serveur ne tourne. Ils demandent des assets construits (`npm run build`) et une base migrée. À la première utilisation, installer le navigateur avec `npx playwright install chromium`.

La CI (`.github/workflows/ci.yml`) lance toutes ces vérifications à chaque pull request, avec les tests PHP sur PostgreSQL.

## Organisation du code

- `app/Domain/<Domaine>` : code métier (Actions, modèles, DTO, enums, événements), un dossier par domaine.
- `app/Http` : contrôleurs minces, qui valident, autorisent, appellent une Action et répondent avec Inertia.
- `resources/js/Pages` : une page Vue par écran.
- `tests/Architecture` : règles vérifiées automatiquement (`strict_types`, pas de `dd`/`dump`, isolation des domaines).
- `tests/Browser` : parcours Playwright avec contrôle axe.

## Dépendances ajoutées hors spécification

| Paquet | Pourquoi | Licence |
|---|---|---|
| `laravel-lang/common` (dev) | Fournit les traductions françaises de Laravel (`lang/fr`) sans les maintenir à la main. Paquet de référence, très utilisé et maintenu. | MIT |
| `driftingly/rector-laravel` (dev) | Règles Rector propres à Laravel, pour les montées de version. Extension de référence pour Laravel, référencée dans la documentation de Rector. | MIT |
| `@lucide/vue` | Icônes des composants, dans le style de la maquette (trait 2 px, bouts arrondis). Bibliothèque d'icônes très utilisée et maintenue ; seules les icônes importées sont embarquées. Voir l'[ADR 0009](docs/adr/0009-storybook-et-icones-lucide.md). | ISC |
| `axe-core` (dev) | Contrôle d'accessibilité dans les tests Vitest des composants. Moteur de référence, déjà utilisé par `@axe-core/playwright` et l'addon a11y de Storybook. | MPL-2.0 |
