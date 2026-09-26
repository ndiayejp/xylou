# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue

Toutes les interactions avec l'utilisateur se font en français.

## Contexte

Xylou : plateforme EdTech d'accompagnement scolaire personnalisé par IA (espaces enfant / parent / professionnel).
Spécifications complètes et plan de réalisation : `docs/SPECIFICATIONS.md` — les lire avant toute tâche structurante.
Les maquettes UX/UI (PDF) seront ajoutées dans `docs/` au moment de travailler sur les composants et templates.

## État actuel vs cible

Étape 0 (§13) terminée : Laravel 12 + Breeze (Inertia 2 + Vue 3 **TypeScript**), `strict_types` partout (règle Pint + test d'architecture), PHP 8.4, Tailwind 3 via PostCSS, Pest 3, Larastan niveau 8, Rector, ESLint/Prettier, Vitest, Playwright + axe, `app/Domain/*` (dossiers vides), `lang/fr` (via `laravel-lang/common`), CI GitHub Actions, ADR 0001–0005, 0008 et 0009. CI verte sur GitHub (dépôt public `ndiayejp/xylou`).

**Pas encore en place** (arrive avec l'étape qui en a besoin, chaque dépendance justifiée) : spatie/laravel-data et typescript-transformer (donc pas de `generated.d.ts` ni de `typescript:transform`), Horizon, Reverb, Scout/Meilisearch. Quand une règle ci-dessous dépend d'un outil absent, le signaler plutôt que l'ignorer en silence.

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
composer dev              # artisan serve + queue:listen + vite en parallèle
composer logs             # Pail (logs en direct) ; exige pcntl, donc pas sous Windows : lire storage/logs/laravel.log

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
npx vitest run resources/js/Components/ui/__tests__/XButton.spec.ts   # un fichier Vitest
npm run test:e2e          # Playwright + axe (tests/Browser) ; lance artisan serve, exige un build et une base migrée et peuplée (db:seed) ; un seul worker car artisan serve traite une requête à la fois
npx playwright test tests/Browser/login.spec.ts                       # un fichier E2E
npm run build             # vue-tsc puis vite build
npm run storybook         # catalogue des composants et layouts (http://localhost:6006) · npm run build-storybook
```

Avant de conclure une tâche : `composer lint && composer analyse && composer refactor:check && composer test && npm run lint && npm run format:check && npm run typecheck && npm run test` (et `npm run test:e2e` si l'UI est touchée).

## Architecture actuelle (Breeze)

- **Inertia :** contrôleurs et closures renvoient `Inertia::render('Nom', props)` ; `resources/js/app.ts` résout `Nom` vers `resources/js/Pages/Nom.vue` (sous-dossiers possibles : `Auth/Login`, `Profile/Edit`). Pas de routeur client ni d'API JSON pour le web — les données arrivent en props.
- **Props partagées :** `app/Http/Middleware/HandleInertiaRequests.php` (ex. `auth.user`).
- **Routes côté Vue :** helper Ziggy `route()` disponible partout (enregistré dans `app.ts`, typé dans `resources/js/types/global.d.ts`) — utiliser les routes nommées, jamais d'URL en dur.
- **Rôles et espaces (ADR 0011) :** rôles spatie/laravel-permission uniquement (Enum `App\Domain\Identity\Enums\Role`, pas de colonne `users.role`), créés par migration. `User` est dans `App\Domain\Identity\Models`. Espaces : `parent.*` (`/parent`, middleware `role:parent`) et `pro.*` (`/pro`, `role:professional`), avec `auth` + `verified`. `dashboard` redirige vers l'espace du compte (`User::homeRouteName()`, 403 sinon). L'inscription publique ne crée que des parents (`RegisterParent`) ; pros par factory (`User::factory()->professional()`) jusqu'à l'étape 9. Comptes de démo (`db:seed`) : `parent@example.com` et `pro@example.com`, mot de passe `password`. Règles du domaine : `docs/domain/identity.md`.
- **Pages publiques :** landing `Pages/Public/Home.vue` (sections dans `Pages/Public/Partials/`, layout branché `PublicSpace`), pages légales `Pages/Public/Legal.vue` (route `legal.notice|privacy|terms|accessibility`, contenu dans `i18n/fr/legal.ts`, mentions « [À compléter] » à valider par un juriste), `sitemap.xml`, `public/robots.txt`, `SeoHead` (titre, description, canonique, Open Graph). Textes de la landing dans `i18n/fr/landing.ts` ; listes de messages lues avec `useMessageList()` (`tm` + `rt`). Dans vue-i18n, `@` et `|` sont spéciaux : passer les e-mails en paramètre. Adresse de contact : `config/xylou.php` (`XYLOU_CONTACT_EMAIL`).
- **Onboarding (ADR 0015) :** écran 1 = inscription (`register`, `Onboarding\AccountController`, consentements du domaine `Privacy`), écrans 2 à 7 sous `/onboarding/{onboarding}` (`onboarding.child`, `onboarding.step`), layout `OnboardingLayout`. Progression dans `onboardings` (`reached_step`, `Onboarding::allows()`), adresses via `App\Http\Navigation\OnboardingRoute`. `dashboard` reprend l’onboarding en cours avant d’exiger l’adresse vérifiée ; l’onboarding n’exige pas `verified`. Chaque écran a sa route (`onboarding.child`, `.interests`, `.goals`, `.difficulties`, `.preferences`, table `ROUTES` de `OnboardingRoute`) et son contrôleur héritant d’`OnboardingStepController` (garde, enfant, passage à l’écran suivant) ; les écrans pas encore construits passent par `Onboarding/Pending`. Dans les tests, `walkOnboarding()` (`OnboardingTest.php`) traverse le parcours par les vraies routes : l’y compléter à chaque nouvel écran. Un contrôleur n’a que des méthodes de ressource (règle Pest `preset()->laravel()`), et une classe hors contrôleur ne vit pas dans `Http/Controllers`.
- **Enfants :** domaine `Children` (`ChildProfile`, Enum `Grade`, `ChildProfilePolicy` via `#[UsePolicy]` sur le modèle, scope `ownedBy`). Les Policies vivent dans leur domaine (règle d’architecture). Props partagées `parent` (enfants + enfant courant, `null` hors espace parent) lues par `useChildren()`. Règles : `docs/domain/children.md`. En français, « de » s’élide devant une voyelle ou un h : `elides()` de `resources/js/i18n/elision.ts` choisit entre deux messages (`…week` / `…weekElided`).
- **Session enfant (ADR 0013) :** garde `kid` (`ChildProfile` authentifiable), ouverte par `POST parent.children.kid-session` (la session parent se ferme). Espace `/enfant` (`kid.*`, middleware `kid.session`), layout branché `KidSpace`, props partagées `kid` (prénom seul). Le middleware global `KeepKidInKidSpace` renvoie toute route hors `kid.*` vers `kid.home` : toute nouvelle route est couverte, et le test générique de `KidSessionTest` le vérifie. Sortie : code parent (PIN `users.parent_pin` sinon mot de passe, 5 essais/min par enfant). Dans les tests : `auth('kid')`, et `assertGuest('web')` pendant la session enfant.
- **Journal d’audit (ADR 0014) :** spatie/laravel-activitylog, journaux `security` (Enum `AuditEvent`, écrit par `AuditSubscriber` à partir des événements Laravel, Fortify et métier : `KidSessionOpened`, `ParentPinChanged`…) et `children` (`LogsActivity` sans attribut). Jamais d’e-mail, nom, prénom, code ni IP dans le journal : tout nouvel événement sensible passe par un événement + une entrée de l’abonné + un test dans `AuditLogTest` (helper `expectNoPersonalData`). Rétention 1 an (`activitylog:clean` planifié).
- **2FA (ADR 0012) :** Fortify, fonctionnalité 2FA seule, routes Fortify ignorées et redéclarées dans `routes/auth.php`. La connexion Breeze (`LoginRequest::validateCredentials()`) délègue le code à Fortify via la session `login.id` / `login.remember`. Page `settings.security` (derrière `password.confirm`). Pros : middleware `two-factor.required`, désactivation réservée aux parents. En test : `User::factory()->withTwoFactor()` (secret `JBSWY3DPEHPK3PXP`, codes de secours `code-secours-1` et `-2`) et `Google2FA::getCurrentOtp()` ; un pro de test doit avoir la 2FA pour entrer dans son espace.
- **Routes :** `routes/web.php` (espaces, profil) et `routes/auth.php` (auth Breeze), contrôleurs par espace dans `app/Http/Controllers/{Parent,Pro}/` (importer avec un alias : `Parent` est un nom réservé en PHP), validation dans `app/Http/Requests/`.
- **Jetons de design :** source unique `resources/js/design/tokens.ts` (couleurs, matières, niveaux de maîtrise, typographie, rayons, mouvement), lue par `tailwind.config.ts` et vérifiée par `tokens.spec.ts` (contrastes AA, texte enfant ≥ 18 px). Classes : `bg-primary`, `text-primary-text`, `text-muted`, `bg-subject-maths-bg`, `bg-mastery-mastered-bar`, `text-h1`, `font-kid text-kid-body`, `rounded-card`, `rounded-kid-card`, `shadow-lift`, `duration-hover`, `animate-grow`… Polices auto-hébergées (`@fontsource-variable`), importées dans `app.css`, qui coupe aussi toutes les animations si `prefers-reduced-motion`.
- **Composants `ui` (ADR 0009) :** `resources/js/Components/ui/X*.vue`, préfixe `X`, sans texte en dur (libellés en slot ou en prop), icônes Lucide passées en prop (`:icon="Check"`, toujours `aria-hidden`). Les composants ne connaissent pas le métier : un statut d’activité s’affiche avec `XTag` et un ton, la page choisit le libellé. Les champs passent par `XField` (libellé, aide, erreur, ids `aria-describedby`) et `field.ts` (classes communes). Chaque composant est couvert par une story (`*.stories.ts` à côté, parfois regroupée par famille : `XFormFields.stories.ts`, `XTags.stories.ts`) et par un test dans `__tests__/` (même regroupement possible, ex. `XTags.spec.ts`) qui appelle `expectNoAxeViolations` de `@/test/axe`.
- **Textes (ADR 0010) :** vue-i18n, messages typés dans `resources/js/i18n/fr.ts` (une clé inexistante casse `vue-tsc`), plugin installé dans `app.ts`, Storybook et Vitest (`test/setup.ts`). Les messages serveur restent dans `lang/fr`. Seuls layouts et pages appellent `$t`, jamais les composants `ui`.
- **Layouts (ADR 0010) :** `KidLayout` (rail tablette, barre mobile), `ParentLayout` (barre latérale, rail, barre mobile + « Plus »), `ProLayout`, `PublicLayout`. Liens reçus en props (`NavItem` de `Layouts/navigation.ts` : clé i18n, `href`, icône, `current`), aucune route ni appel serveur ; stories dans `Layouts/Layouts.stories.ts`. Toasts via `useToasts()` (`Composables/`) affichés par `ToastRegion` dans chaque layout. Les pages utilisent les versions branchées `ParentSpace`, `ProSpace` ou `AccountSpace` (profil : espace selon le rôle), qui lisent `auth.user` et la navigation de `useParentNavigation` / `useProNavigation` (entrées affichées seulement si leur route existe). Les pages d’authentification utilisent `AuthLayout` (carte centrée, titre, statut). Chaque layout inclut `NavigationProgress`, qui remplace la barre d’Inertia (`progress: false` dans `app.ts`, car elle porte `role="bar"`, rôle ARIA invalide). Liens dans un texte : classe `link` (`app.css`).
- **Front :** composants Breeze dans `resources/js/Components/`, alias `@/` → `resources/js/`. Vue racine unique : `resources/views/app.blade.php`.
- **Utilisateur connecté :** l'injecter avec `#[CurrentUser] User $user` plutôt que `$request->user()` (non nullable pour Larastan).
- **Tests :** `tests/Pest.php` applique `Tests\TestCase` + `RefreshDatabase` à `tests/Feature` ; les tests Unit ne démarrent pas le framework ; `tests/Architecture` contient les règles `arch()` (à enrichir à chaque nouveau domaine). Côté Vitest, `resources/js/test/setup.ts` installe vue-i18n et un faux `$headManager` (pour `<Head>` d'Inertia) ; `route()` de Ziggy se simule avec `vi.stubGlobal` (voir `useSpaceNavigation.spec.ts`).
- **ESLint :** `vue/multi-word-component-names` est désactivée pour `Pages/` ; les composants Breeze `Checkbox`, `Dropdown` et `Modal` en sont exemptés jusqu'à leur remplacement par `Components/ui`.
