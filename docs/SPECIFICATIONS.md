# Xylou — Spécifications techniques & plan de réalisation

> **À l'attention de Claude Code et de l'équipe de développement.**
> Ce document est la source de vérité technique du projet Xylou. Il décrit **quoi** construire, **avec quoi**, **pourquoi** ces choix ont été faits, et **dans quel ordre** avancer.
> Il est conçu pour être copié à la racine du dépôt (`docs/SPECIFICATIONS.md`) ; la section 14 contient le texte à placer dans `CLAUDE.md`.

---

## Sommaire

1. [Vision produit (rappel)](#1-vision-produit-rappel)
2. [Principes d'ingénierie](#2-principes-dingénierie)
3. [Stack technique et justification des choix](#3-stack-technique-et-justification-des-choix)
4. [Architecture applicative](#4-architecture-applicative)
5. [Structure du dépôt](#5-structure-du-dépôt)
6. [Modèle de données](#6-modèle-de-données)
7. [Rôles, authentification et autorisations](#7-rôles-authentification-et-autorisations)
8. [Module IA : génération d'activités](#8-module-ia--génération-dactivités)
9. [Moteur d'exercice et calcul de la progression](#9-moteur-dexercice-et-calcul-de-la-progression)
10. [Front-end : design system, conventions Vue](#10-front-end--design-system-conventions-vue)
11. [Sécurité, confidentialité et RGPD](#11-sécurité-confidentialité-et-rgpd)
12. [Qualité, tests, CI/CD et exploitation](#12-qualité-tests-cicd-et-exploitation)
13. [Plan de réalisation étape par étape](#13-plan-de-réalisation-étape-par-étape)
14. [Directives pour Claude Code (`CLAUDE.md`)](#14-directives-pour-claude-code-claudemd)
15. [Annexes : routes, écrans, glossaire, ADR](#15-annexes)

---

## 1. Vision produit (rappel)

**Xylou** est une plateforme d'accompagnement scolaire personnalisée par IA pour les **enfants**, leurs **parents** et les **professionnels de l'éducation**.

> « Au lieu de demander à l'enfant de s'adapter aux exercices, nous adaptons les exercices à son univers. »

L'IA adapte **le contexte, la formulation et l'illustration** d'une activité aux passions de l'enfant (football, espace, animaux…). **La compétence pédagogique, le niveau et la réponse attendue ne changent jamais.**

### Trois expériences connectées

| Espace | Objectif | Support prioritaire | Ton de l'interface |
|---|---|---|---|
| Enfant | Apprendre sans se sentir jugé | Tablette → mobile → desktop | Ludique, jamais infantilisant, une action par écran |
| Parent | Comprendre, décider, garder le contrôle | Desktop → tablette → mobile | Clair, rassurant, données expliquées en phrases |
| Professionnel | Observer, analyser, recommander | Desktop → tablette → mobile | Sobre, dense, orienté observation |

### Invariants produit (ne jamais les enfreindre dans le code)

1. **Aucun classement ni comparaison entre enfants.** Aucune requête, API ou écran ne doit exposer un rang.
2. **Pas de note** : la progression s'exprime en **niveaux de maîtrise** (voir §9).
3. **L'adulte reste dans la boucle** : aucune activité générée par l'IA n'atteint l'enfant sans approbation d'un adulte autorisé.
4. **Un feedback d'erreur n'affiche jamais « Faux »** ; il affiche « Pas encore », une explication et une nouvelle tentative.
5. **L'IA est transparente** : tout contenu généré porte une mention visible.
6. **Les données de l'enfant appartiennent au parent** : export, partage et suppression lui sont toujours accessibles.

### Référence design

Les maquettes (43 écrans, design system, états d'interface) se trouvent dans le canvas **« Xylou — UX/UI complète »**. Les jetons de design indispensables sont recopiés au §10 pour que ce document se suffise à lui-même.

---

## 2. Principes d'ingénierie

La **maintenabilité par une équipe** est une exigence de premier rang. Chaque décision technique ci-dessous en découle.

| Principe | Traduction concrète |
|---|---|
| **Ennuyeux mais éprouvé** | Outils standards, largement documentés, faciles à recruter (Laravel, Vue, PostgreSQL). Pas de framework exotique. |
| **Un seul endroit pour chaque règle** | Les règles métier vivent dans des classes `Action` côté PHP, jamais dans les contrôleurs ni dans les composants Vue. |
| **Organisation par domaine métier** | Le code est rangé par domaine (`Activities`, `Progress`, `Sharing`…) et non par type technique, pour qu'un développeur trouve tout ce qui concerne une fonctionnalité au même endroit. |
| **Typage de bout en bout** | PHP typé strictement + DTO ; TypeScript côté Vue ; les types TS sont **générés** depuis les DTO PHP (aucune duplication manuelle). |
| **Tout est testé automatiquement** | Aucune fonctionnalité n'est « terminée » sans tests. La CI bloque la fusion si un test, le lint ou l'analyse statique échoue. |
| **Décisions tracées** | Toute décision structurante est écrite dans un ADR (`docs/adr/`). Un nouvel arrivant comprend le *pourquoi*. |
| **Dépendances maîtrisées** | Chaque nouvelle dépendance doit être justifiée (maintenue, populaire, licence compatible). Moins de dépendances = moins de maintenance. |
| **Remplaçabilité des services externes** | Fournisseur IA, PDF, stockage, paiement : toujours derrière une interface (`Contract`) avec une implémentation factice pour les tests. |
| **Accessibilité non négociable** | WCAG 2.2 niveau AA, vérifiée automatiquement en CI. |

---

## 3. Stack technique et justification des choix

> **Versions** : utiliser la **dernière version stable** de chaque outil au démarrage du projet, puis figer les versions (`composer.lock`, `package-lock.json`). Minimums indiqués ci-dessous.

### 3.1 Back-end

| Brique | Choix | Pourquoi |
|---|---|---|
| Langage | **PHP ≥ 8.3** (`declare(strict_types=1)` partout) | Enums natifs, propriétés `readonly`, typage fort : code plus sûr et plus lisible. |
| Framework | **Laravel** (dernière version majeure stable) | Écosystème le plus complet en PHP (files d'attente, notifications, planificateur, auth, tests). Énorme communauté → recrutement et maintenance facilités. |
| Base de données | **PostgreSQL ≥ 16** | Colonnes `jsonb` indexables pour les contenus d'activités, contraintes solides, excellent support des requêtes analytiques (progression). |
| Cache, files, sessions | **Redis** | Standard Laravel ; indispensable pour les tâches longues (génération IA, PDF). |
| Supervision des files | **Laravel Horizon** | Tableau de bord des jobs, relances, métriques ; évite les « jobs perdus ». |
| Temps réel | **Laravel Reverb** (WebSockets) + Laravel Echo côté Vue | Premier-parti Laravel, auto-hébergé, pas de service tiers. Utilisé pour « activité générée prête à valider ». |
| DTO & validation | **spatie/laravel-data** | Un seul objet sert de DTO, de validation et de source des types TypeScript. |
| Génération des types TS | **spatie/laravel-typescript-transformer** | Les types Vue sont générés depuis PHP → aucune désynchronisation front/back. |
| Rôles & permissions | **spatie/laravel-permission** + **Policies** Laravel | Standard de fait ; les Policies portent les règles fines (ex. accès pro limité dans le temps). |
| Journal d'audit | **spatie/laravel-activitylog** | Traçabilité RGPD (qui a consulté/partagé/supprimé quoi). |
| Recherche | **Laravel Scout** + **Meilisearch** | Recherche tolérante aux fautes pour la bibliothèque, auto-hébergeable. Scout permet de changer de moteur sans réécrire le code. |
| PDF (bilans) | **spatie/laravel-pdf** (moteur Browsershot / Chromium) | Le PDF est rendu à partir d'un template HTML/Tailwind → fidèle au design, maintenable par un dev front. |
| Paiement | **Laravel Cashier (Stripe)** | Abonnements, factures, portail client : ne pas réinventer la facturation. |
| Médias | **spatie/laravel-medialibrary** + stockage S3 compatible **hébergé dans l'UE** | Gestion propre des illustrations, avatars et exports. |
| Feature flags | **Laravel Pennant** | Déployer progressivement (ex. nouveau format d'activité) sans branches longues. |
| Client HTTP IA | **Client HTTP Laravel** encapsulé derrière `AiProvider` (voir §8) | Permet de changer de fournisseur ou de modèle sans toucher au métier. |

### 3.2 Front-end

| Brique | Choix | Pourquoi |
|---|---|---|
| Framework | **Vue 3** (Composition API, `<script setup lang="ts">`) | Demandé ; lisible, réactif, facile à former. |
| Liaison Laravel ↔ Vue | **Inertia.js** (adaptateur Vue) | Pas d'API interne à maintenir pour le web : le routage, l'auth et les autorisations restent dans Laravel. Réduit fortement le volume de code et les bugs de synchronisation. |
| Langage | **TypeScript** (mode `strict`) | Refactorisations sûres en équipe. |
| Build | **Vite** | Standard Laravel/Vue, rapide. |
| Styles | **Tailwind CSS** configuré avec les **jetons Xylou** (§10) | Cohérence visuelle garantie par la configuration ; pas de CSS dispersé. |
| Composants accessibles | **Reka UI** (ex-Radix Vue) ou **Headless UI** | Menus, dialogues, onglets, listbox accessibles au clavier sans réécrire l'ARIA. Style 100 % Tailwind. |
| État global | **Pinia** (usage limité) | Uniquement pour l'état transverse côté client (session d'exercice, enfant actif). Les données serveur passent par Inertia. |
| Utilitaires | **VueUse** | Composables éprouvés (media queries, réseau en ligne/hors ligne, etc.). |
| Graphiques | **Chart.js** via vue-chartjs, ou SVG maison pour les barres | Les visualisations Xylou sont simples ; pas besoin d'une librairie lourde. |
| Traductions | **vue-i18n** + fichiers de langue Laravel | Toutes les chaînes sont externalisées dès le départ (français par défaut). |
| Routes typées | **Ziggy** (ou Wayfinder) | Appeler les routes Laravel nommées depuis Vue sans URL en dur. |
| Hors ligne / PWA | **vite-plugin-pwa** (Workbox) | Mode hors ligne de l'interface enfant (voir §9.5). |
| Mobile natif (phase finale) | **Capacitor** | Réutilise le même code Vue pour iOS/Android. |

### 3.3 Qualité & outillage

| Outil | Rôle |
|---|---|
| **Pest** | Tests PHP (unitaires, fonctionnels, architecture). |
| **Larastan** (PHPStan) niveau ≥ 8 | Analyse statique PHP. |
| **Laravel Pint** | Formatage PHP (preset Laravel). |
| **Rector** | Mises à jour automatisées du code PHP lors des montées de version. |
| **Vitest** + **Vue Test Utils** | Tests des composants et composables. |
| **Playwright** + **@axe-core/playwright** | Tests de bout en bout et d'accessibilité. |
| **ESLint** (config Vue + TS) + **Prettier** | Lint et formatage front. |
| **Laravel Sail** (Docker) | Environnement local identique pour tous. |
| **GitHub Actions** | CI/CD. |
| **Sentry** | Suivi des erreurs PHP et JS. |
| **Laravel Pulse** | Santé applicative (requêtes lentes, jobs, utilisation). |

### 3.4 Alternatives écartées (et pourquoi)

| Alternative | Raison de l'écarter |
|---|---|
| SPA Vue séparée + API REST pour le web | Double la surface à maintenir (API, auth par jetons, gestion d'état serveur côté client). L'API REST sera ajoutée **uniquement** pour le mobile natif. |
| Nuxt | Ajoute un serveur Node en plus de PHP : deux runtimes à exploiter. Inertia suffit. |
| Livewire | Excellent, mais le besoin exprimé est Vue, et l'interface enfant (animations, hors ligne) bénéficie d'un vrai framework front. |
| MySQL | Viable, mais `jsonb` et les fonctions analytiques de PostgreSQL servent directement les contenus d'activités et la progression. |
| Microservices | Inutile à cette échelle ; un **monolithe modulaire** est plus simple à maintenir et peut être découpé plus tard si besoin. |

---

## 4. Architecture applicative

### 4.1 Vue d'ensemble

```
┌──────────────────────────────────────────────────────────────────┐
│                         Navigateur / PWA                         │
│  Vue 3 + Inertia  ·  Espaces : Kid / Parent / Pro / Public       │
└───────────────▲──────────────────────────────▲───────────────────┘
                │ HTTP (Inertia)               │ WebSocket (Reverb)
┌───────────────┴──────────────────────────────┴───────────────────┐
│                     Laravel — monolithe modulaire                │
│  Http (contrôleurs minces) → Actions (métier) → Models / DTO     │
│  Domaines : Identity · Children · Curriculum · Activities ·      │
│             Ai · Learning · Progress · Rewards · Reports ·       │
│             Sharing · Notifications · Privacy · Billing          │
└──────┬───────────────┬───────────────┬───────────────┬───────────┘
       │               │               │               │
  PostgreSQL        Redis          Meilisearch     Stockage S3 (UE)
                 (cache/files)     (bibliothèque)   (médias, PDF)
                       │
                 Workers Horizon ──► Fournisseur IA (via AiProvider)
                                 ──► Chromium (PDF)
```

### 4.2 Le monolithe modulaire

Le code métier est découpé en **domaines** sous `app/Domain/<Domaine>`. Règles :

- Un domaine contient ses `Models`, `Actions`, `Data` (DTO), `Enums`, `Events`, `Jobs`, `Policies`, `Queries`.
- Un domaine **n'appelle pas directement les modèles d'un autre domaine en écriture** : il passe par une `Action` publique de ce domaine ou réagit à un `Event`.
- La couche `app/Http` (contrôleurs, requêtes, middleware) est **mince** : elle valide, autorise, appelle une Action et retourne une réponse Inertia.
- Ces règles sont **vérifiées automatiquement** par des tests d'architecture Pest (§12).

### 4.3 Le pattern « Action »

Toute opération métier est une classe invocable, testable isolément :

```php
// app/Domain/Activities/Actions/ApproveActivity.php
final class ApproveActivity
{
    public function __construct(private readonly AssignActivity $assign) {}

    public function __invoke(Activity $activity, User $reviewer, bool $sendNow): Activity
    {
        // 1. vérifie les invariants (statut = PendingReview)
        // 2. passe le statut à Approved, trace reviewer + date
        // 3. si $sendNow : délègue à AssignActivity
        // 4. déclenche l'événement ActivityApproved
    }
}
```

**Pourquoi** : une règle métier = un fichier, nommé par un verbe. Un nouveau développeur lit la liste des Actions d'un domaine et comprend ce que le domaine *fait*.

### 4.4 Flux de requête type

1. Route nommée → `Controller` (1 méthode = 1 écran ou 1 commande).
2. `FormRequest` ou `Data` : validation.
3. `authorize()` via **Policy**.
4. Appel d'une **Action**.
5. Réponse `Inertia::render('Parent/Dashboard/Index', DashboardData::from(...))`.

---

## 5. Structure du dépôt

```
xylou/
├── app/
│   ├── Domain/
│   │   ├── Identity/         # utilisateurs, rôles, session enfant
│   │   ├── Children/         # profils enfant, intérêts, objectifs, difficultés, préférences
│   │   ├── Curriculum/       # matières, compétences (référentiel), univers
│   │   ├── Activities/       # activités, items, statuts, bibliothèque, validation
│   │   ├── Ai/               # AiProvider, prompts, génération, vérifications
│   │   ├── Learning/         # affectations, tentatives, indices, reprise
│   │   ├── Progress/         # maîtrise par compétence, résumés, recommandations
│   │   ├── Rewards/          # réussites non compétitives
│   │   ├── Reports/          # bilans, export PDF
│   │   ├── Sharing/          # partage parent ↔ pro, observations
│   │   ├── Notifications/    # centre de notifications, préférences
│   │   ├── Privacy/          # consentements, export, suppression, rétention
│   │   └── Billing/          # abonnements (Cashier)
│   ├── Http/
│   │   ├── Controllers/{Public,Kid,Parent,Pro,Settings}/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Support/              # utilitaires transverses (sans logique métier)
│   └── Providers/
├── config/
├── database/{migrations,factories,seeders}/
├── docs/
│   ├── SPECIFICATIONS.md     # ce document
│   ├── adr/                  # décisions d'architecture (0001-…md)
│   ├── domain/               # une page par domaine (règles, glossaire)
│   └── runbooks/             # exploitation : incidents, restauration, rotation des clés
├── lang/fr/
├── resources/
│   ├── js/
│   │   ├── app.ts
│   │   ├── Pages/{Public,Onboarding,Kid,Parent,Pro,Settings}/
│   │   ├── Layouts/{PublicLayout,KidLayout,ParentLayout,ProLayout}.vue
│   │   ├── Components/
│   │   │   ├── ui/           # design system : XButton, XCard, XChip, XProgress…
│   │   │   ├── kid/          # composants propres à l'enfant
│   │   │   ├── adult/        # composants partagés parent/pro
│   │   │   └── illustrations/# scènes d'univers (SVG) + mascotte « Xy »
│   │   ├── Composables/      # useExerciseSession, useOnline, useChildSwitcher…
│   │   ├── Stores/           # Pinia (minimal)
│   │   ├── types/generated.d.ts   # généré depuis PHP — ne jamais éditer
│   │   └── i18n/
│   ├── css/app.css           # Tailwind + variables CSS des jetons
│   ├── prompts/              # templates de prompts IA versionnés (v1/, v2/…)
│   └── views/pdf/            # templates HTML des bilans PDF
├── routes/{web.php,kid.php,parent.php,pro.php,api.php,channels.php}
├── tests/
│   ├── Architecture/         # règles de dépendances entre couches
│   ├── Unit/
│   ├── Feature/
│   └── Browser/              # Playwright
├── .github/workflows/
├── CLAUDE.md
└── README.md
```

---

## 6. Modèle de données

### 6.1 Conventions

- Clés primaires : **ULID** (`HasUlids`) — non devinables dans les URL, triables.
- Horodatages `created_at` / `updated_at` partout ; `SoftDeletes` sur les entités restaurables (activités, profils enfant pendant le délai de grâce).
- Les statuts sont des **Enums PHP** stockés en `string`.
- Données sensibles (difficultés observées, observations, notes libres) : **cast `encrypted`**.
- Noms de tables en anglais, au pluriel, `snake_case`. Libellés affichés en français via `lang/fr`.

### 6.2 Entités principales

```
users                    id, name, email, password, role(enum: parent|professional|admin),
                         locale, two_factor_*, email_verified_at
child_profiles           id, owner_id→users, first_name, birth_year, school_level(enum),
                         grade(enum: CP…3e), language, avatar_key, comfort_settings(jsonb:
                         read_aloud, dyslexia_font, no_timer, animations), pin_hash(nullable),
                         deleted_at
interests                id, key, label, category(enum), icon_key, universe_id(nullable)
child_interest           child_profile_id, interest_id, rank
custom_interests         id, child_profile_id, label (ex. « son club de foot »)
child_goals              id, child_profile_id, goal(enum), is_primary, target_date(nullable), note
child_difficulties       id, child_profile_id, skill_id(nullable), label, observation(encrypted)
learning_preferences     child_profile_id, visual, concrete_examples, small_steps, repetition,
                         short_explanations, interactive, session_minutes(5|10|15|20)

subjects                 id, key, label, color_token, icon_key
skills                   id, subject_id, code (ex. MATH.CE2.PROB.2STEPS), label,
                         cycle, grade_min, grade_max, parent_skill_id(nullable), description
universes                id, key (space|football|forest…), label, illustration_set

activities               id, owner_id→users, child_profile_id(nullable = modèle de bibliothèque),
                         subject_id, skill_id, universe_id, title, format(enum), difficulty(enum:
                         discovery|practice|consolidation|challenge), duration_minutes,
                         status(enum, voir 6.3), source(enum: ai|manual|duplicate|pro_reco),
                         ai_generation_id(nullable), learning_objective, context_summary,
                         reviewed_by, reviewed_at, archived_at, deleted_at, version
activity_items           id, activity_id, position, prompt, prompt_payload(jsonb: données
                         structurées, ex. {distance:240, minutes:3}), answer_type(enum),
                         expected_answer(jsonb), tolerance, hint, explanation,
                         common_errors(jsonb: [{answer:720, message:"Tu as multiplié…"}])
ai_generations           id, requested_by, child_profile_id, prompt_version, model, input(jsonb,
                         sans données identifiantes), output(jsonb), status(enum), error,
                         checks(jsonb), tokens_in, tokens_out, cost_cents, duration_ms

assignments              id, activity_id, child_profile_id, assigned_by, status(enum:
                         assigned|in_progress|interrupted|completed|abandoned),
                         current_item_position, started_at, completed_at, last_seen_at
attempts                 id, assignment_id, activity_item_id, answer(jsonb), is_correct,
                         error_code(nullable), hints_used, said_dont_know, duration_ms,
                         client_uuid (idempotence hors ligne), created_at

skill_masteries          child_profile_id, skill_id, score(0–100), level(enum), attempts_count,
                         last_practiced_at, trend(jsonb : historique hebdomadaire)
weekly_summaries         id, child_profile_id, week_start, stats(jsonb), summary_text,
                         highlights(jsonb), generated_by_ai(bool), edited_by(nullable)
recommendations          id, child_profile_id, skill_id, kind, rationale, status
                         (pending|accepted|dismissed|done), source(system|professional)

badges                   id, key, label, description, icon_key, rule(jsonb)
child_badges             child_profile_id, badge_id, earned_at, context(jsonb)

professional_profiles    user_id, profession(enum: teacher|educator|remedial|other), organization
shares                   id, child_profile_id, professional_id→users, scopes(jsonb: progress,
                         activities, reports, observations), expires_at, revoked_at, invited_email,
                         status(enum: invited|active|expired|revoked)
observations             id, child_profile_id, author_id, body(encrypted), visibility
                         (private|shared_with_parent), observed_at
reports                  id, child_profile_id, author_id, period_start, period_end, audience
                         (parent|teacher|professional), sections(jsonb), content(jsonb),
                         status(draft|final), pdf_media_id

notifications            (table Laravel standard) + notification_preferences
                         (user_id, type, channel(app|mail), enabled, quiet_hours)
consents                 id, user_id, child_profile_id, kind(enum), granted, version,
                         granted_at, revoked_at, ip_hash
data_requests            id, user_id, child_profile_id, kind(export|deletion), status,
                         scheduled_for, completed_at
activity_log             (spatie) audit de tous les accès sensibles
subscriptions            (Cashier)
```

### 6.3 Machine à états d'une activité

```
          ┌──────────── regenerate ───────────┐
          ▼                                   │
 [generating] ──ok──► [pending_review] ──approve──► [approved] ──assign──► [assigned*]
      │                    │   ▲                        │
    fail                 edit  │                     archive
      ▼                    ▼   │                        ▼
 [generation_failed]    [draft]┘                   [archived] ──restore──► [approved]
                                 (* l'état « assigned » vit dans `assignments`)
 Toute activité peut passer en soft-delete (restaurable 30 jours).
```

- Implémentation : Enum `ActivityStatus` avec une méthode `canTransitionTo()` ; **toute transition passe par une Action** et est journalisée.
- Invariant testé : une `assignment` ne peut être créée que pour une activité `approved`.

### 6.4 Référentiel de compétences

- Le référentiel (matières, compétences par cycle/classe) est **une donnée**, chargée par seeder depuis des fichiers versionnés `database/data/curriculum/*.yaml`.
- **Pourquoi** : l'équipe pédagogique peut le faire évoluer par pull request sans toucher au code, et l'IA ne décide jamais du niveau.

---

## 7. Rôles, authentification et autorisations

### 7.1 Rôles

| Rôle | Compte | Accès |
|---|---|---|
| `parent` | E-mail + mot de passe (+ 2FA optionnelle) | Ses enfants uniquement ; tout le périmètre parent. |
| `professional` | E-mail + mot de passe + **2FA obligatoire** | Uniquement les profils partagés, selon les `scopes`, tant que le partage est actif. |
| `child` (session) | **Pas de compte, pas d'e-mail** | Session enfant ouverte depuis l'appareil du parent (voir 7.2). |
| `admin` | Interne | Back-office (référentiel, modération), jamais les contenus sensibles en clair sans journalisation. |

### 7.2 Session enfant

- Le parent choisit « Ouvrir l'espace de Emma » sur l'appareil ; une **session enfant** est créée (garde Laravel dédiée `kid`) liée à `child_profile_id`.
- Changement d'enfant ou retour à l'espace parent : **code parent** (PIN à 4–6 chiffres ou mot de passe).
- Option : l'enfant peut ouvrir sa session avec un **code image** (choisir 3 pictos) — jamais d'e-mail ni de mot de passe classique.
- **Pourquoi** : minimise les données collectées sur un mineur, simplifie l'usage à la maison et empêche l'enfant d'accéder à l'espace parent.

### 7.3 Autorisations

- **Policies** pour chaque modèle (`ChildProfilePolicy`, `ActivityPolicy`, `ReportPolicy`, `ObservationPolicy`…).
- Accès pro : `ShareGate::allows($pro, $child, Scope::Progress)` vérifie `status = active`, `expires_at > now()`, `revoked_at IS NULL` et le scope.
- **Tests obligatoires** : pour chaque Policy, un test « accès autorisé » **et** des tests « accès refusé » (autre parent, pro sans partage, partage expiré, partage révoqué, scope manquant).
- Les routes sont groupées par espace avec middleware `role:parent`, `role:professional`, `kid.session`.

---

## 8. Module IA : génération d'activités

### 8.1 Principe fondamental

> L'IA **habille** une compétence ; elle ne **choisit** ni la compétence, ni le niveau, ni la réponse.

Le système fixe : compétence, niveau, difficulté, format, nombre d'items, **structure mathématique** (ex. `a ÷ b` avec `a=240, b=3`). L'IA produit : titre, mise en contexte, formulation, indice, explication, messages d'erreurs fréquentes, dans l'univers demandé.

### 8.2 Abstraction du fournisseur

```php
interface AiProvider
{
    /** @param array<string,mixed> $jsonSchema */
    public function generateStructured(Prompt $prompt, array $jsonSchema, AiOptions $options): AiResult;
}
```

- Implémentations : `ClaudeProvider` (ou autre fournisseur), `FakeAiProvider` (tests, démo, dev hors ligne).
- Le fournisseur et le modèle sont définis dans `config/ai.php` via variables d'environnement.
- **Pourquoi** : changer de modèle ou de fournisseur = changer une configuration, pas le métier.

### 8.3 Prompts versionnés

- Stockés dans `resources/prompts/<version>/<format>.md` (ex. `v1/problem.md`), avec variables explicites.
- `ai_generations.prompt_version` trace la version utilisée.
- Toute modification de prompt = nouvelle version + tests de non-régression sur un jeu d'exemples (`tests/Fixtures/ai/`).

### 8.4 Pipeline de génération (job en file d'attente)

```
GenerateActivityJob
 1. BuildGenerationInput     → profil anonymisé : niveau, compétence, univers, préférences
                               (JAMAIS : prénom, nom, e-mail, observations, difficultés en texte libre)
 2. PlanItems                → le système calcule la structure et les réponses attendues
 3. AiProvider::generateStructured (sortie JSON contrainte par schéma)
 4. ValidateSchema           → rejet si le JSON ne respecte pas le schéma
 5. VerifyAnswers            → recalcul côté PHP des réponses (maths) ; incohérence = rejet
 6. CheckContent             → longueur/lisibilité selon le niveau, vocabulaire, filtre de sécurité
 7. Persist                  → activity(status=pending_review) + items + checks
 8. Broadcast                → ActivityReadyForReview (Reverb) + notification
 En cas d'échec : 2 nouvelles tentatives avec backoff, puis status=generation_failed
 et message clair à l'utilisateur (« Vos réglages sont conservés, réessayez »).
```

### 8.5 Garde-fous

- Limites d'usage par compte (Laravel `RateLimiter`) et quota par offre d'abonnement.
- Suivi des coûts (`tokens_*`, `cost_cents`) → tableau de bord interne.
- Délai maximal par appel + annulation possible par l'utilisateur.
- Contrat de sous-traitance (DPA) avec le fournisseur IA ; aucune utilisation des données pour l'entraînement du modèle si l'option existe.
- Chaque activité affiche « Générée par l'IA » ; l'écran de validation montre les vérifications automatiques.

### 8.6 Résumés « Ce qu'il faut retenir »

- Calculés **d'abord de façon déterministe** (statistiques de la semaine, compétences en hausse/baisse, erreurs fréquentes), puis l'IA **reformule** ces faits en 2–3 phrases.
- L'IA ne reçoit que des faits agrégés, jamais les réponses brutes.
- Le parent peut modifier le texte ; la version modifiée est conservée.

---

## 9. Moteur d'exercice et calcul de la progression

### 9.1 Session d'exercice (côté enfant)

- Composable `useExerciseSession(assignmentId)` : question courante, réponse, indice, envoi, reprise.
- Chaque réponse est envoyée avec un `client_uuid` → **idempotence** (aucune double comptabilisation en cas de réseau instable).
- La correction est faite **côté serveur** (`EvaluateAttempt`), qui renvoie : `correct | not_yet`, message adapté (erreur fréquente détectée ou message générique), indice, explication.
- `assignments.current_item_position` est mis à jour à chaque réponse → **reprise exacte** après interruption.

### 9.2 Feedback

| Situation | Affichage |
|---|---|
| Réponse correcte | Animation légère, « Bravo, c'est ça ! », explication courte, « Continuer ». |
| Réponse incorrecte | « Pas encore », explication de l'erreur (depuis `common_errors` si reconnue), indice, « Réessayer ». Jamais « Faux ». |
| « Je ne sais pas » | Explication pas à pas, puis question similaire. Non pénalisant pour la maîtrise (compté comme demande d'aide). |

### 9.3 Calcul de la maîtrise (déterministe, documenté, testé)

- Score par compétence de 0 à 100, calculé par **moyenne mobile exponentielle** des tentatives récentes :
  - réussite au 1er essai = 1,0 ; réussite après indice = 0,7 ; réussite au 2e essai = 0,6 ; « Pas encore » = 0 ; « Je ne sais pas » = non compté dans le score, compté dans l'aide.
  - facteur de lissage α = 0,3 (constante dans `config/progress.php`).
- Niveaux (mêmes seuils que le design) :

| Score | Niveau | Libellé |
|---|---|---|
| ≥ 90 | `mastered` | Maîtrisé |
| 70–89 | `on_track` | En bonne voie |
| 40–69 | `consolidating` | À consolider |
| < 40 ou < 3 tentatives | `discovering` | À découvrir |

- Recalcul par listener sur l'événement `AttemptRecorded` (synchronement léger ou via job).
- **Pourquoi déterministe** : explicable aux parents et aux professionnels, reproductible, testable. L'IA ne calcule jamais la progression.

### 9.4 Réussites non compétitives

- Règles déclaratives en base (`badges.rule`) évaluées par `EvaluateBadges` après chaque session : première mission, 5 activités, persévérance (≥ 3 essais puis réussite), nouvelle compétence, défi relevé, 7 jours d'apprentissage (non consécutifs).
- **Interdit** : points cumulés affichés, séries punitives, classements.

### 9.5 Hors ligne (PWA)

- L'activité en cours et ses items sont mis en cache à l'ouverture.
- Les tentatives hors ligne sont stockées dans IndexedDB puis rejouées (idempotence par `client_uuid`).
- La correction hors ligne utilise `expected_answer` + `tolerance` embarqués ; le serveur **revalide** à la synchronisation (le serveur fait foi).

---

## 10. Front-end : design system, conventions Vue

### 10.1 Jetons de design (à reproduire dans `tailwind.config` et `app.css`)

**Couleurs de marque**

| Jeton | Valeur | Usage |
|---|---|---|
| `primary` | `#5B5CE2` | Actions, marque |
| `primary-text` | `#4546C4` | Texte indigo sur fond clair (contraste AA) |
| `primary-strong` | `#34359E` | Texte sur fonds teintés |
| `secondary` | `#7C83FD` | Illustrations, surfaces douces |
| `accent` | `#FFB84D` | Récompenses, **anneau de focus clavier** — jamais en texte sur blanc |
| `accent-text` | `#8A5300` | Texte sur fond ambré |
| `success` | `#55C98A` | Barres « Maîtrisé » |
| `success-text` | `#1C7A4C` | Texte de validation |
| `bg` | `#F7F8FC` | Fond d'application |
| `text` | `#202238` | Texte principal |
| `muted` | `#5A5C75` | Texte secondaire |
| `line` | `#E6E7F2` | Bordures |
| `tint` | `#EEEEFF` | Fonds indigo doux |
| `danger` | `#C23B3B` | Erreurs (adulte uniquement ; l'enfant voit l'ambre « Pas encore ») |

**Couleurs des matières** (texte / fond) : Mathématiques `#4546C4/#EEEEFF` · Français `#B23A26/#FDECE8` · Sciences `#16775C/#E3F6EF` · Histoire `#8A560B/#FBF0DC` · Géographie `#1F6AAF/#E4F0FB` · Langues `#9C3470/#FAE8F2`. Toujours accompagnées d'une **icône et d'un libellé**.

**Typographie**
- Adulte : **Plus Jakarta Sans** (400–800). Échelle : 48 / 32 / 24 / 18 / 15 / 13 px.
- Enfant : **Nunito** (600–900). Échelle : 44 / 30 / 26 / 20 px — **jamais sous 18 px** pour l'enfant.
- Polices auto-hébergées (`@fontsource`) pour la performance et la confidentialité (pas d'appel à un CDN tiers).

**Formes et espacements** : grille de 4 px (4, 8, 12, 16, 24, 32, 48, 64). Rayons : 8 (tags), 12 (champs), 16 (boutons), 20 (cartes adulte), 28 (cartes enfant), `full` (pastilles). Zones tactiles ≥ 44 px (adulte) et ≥ 64 px (boutons principaux enfant).

**Mouvement** : survol 160 ms ; remplissage des barres 900 ms ; validation 500 ms ; transition entre questions 280 ms. **Tout est désactivé si `prefers-reduced-motion`.**

### 10.2 Bibliothèque de composants `Components/ui`

À construire **avant** les écrans (étape 1) et documenter dans **Histoire** (ou Storybook) :

`XButton` (primary, secondary, soft, ghost, accent, success, danger ; tailles sm/md/lg/kid ; états loading/disabled), `XIconButton` (aria-label obligatoire via prop requise), `XCard`, `XChip`, `XSubjectTag`, `XLevelBadge`, `XProgressBar` (avec `role="progressbar"` et valeur textuelle), `XStepProgress`, `XInput`, `XSelect`, `XTextarea`, `XToggle`, `XSegmented`, `XSelectCard`, `XCallout` (ai/info/warn/ok/err), `XToast`, `XEmptyState`, `XSkeleton`, `XAvatar`, `XChildSwitcher`, `XAiBadge`, `XRewardBadge`, `XMascot` (humeurs : happy/think/cheer), `XUniverseScene` (space/football/forest…).

Règles :
- Un composant `ui` **ne connaît pas le métier** (pas d'appel serveur, pas de modèle Xylou).
- Props typées, `defineProps<…>()` + valeurs par défaut ; événements typés `defineEmits`.
- Chaque composant a un test Vitest (rendu, accessibilité de base, états).

### 10.3 Conventions Vue

- `<script setup lang="ts">` uniquement. Pas d'Options API.
- Pages Inertia : `Pages/<Espace>/<Fonctionnalité>/<Index|Show|Create|Edit>.vue`.
- Les types des props de page viennent de `types/generated.d.ts` (générés depuis les DTO PHP).
- Logique réutilisable → `Composables/useXxx.ts`, testée avec Vitest.
- Aucune chaîne en dur : `t('kid.exercise.not_yet')`.
- Illustrations : un seul composant `XUniverseScene` avec une prop `universe` ; **la structure de l'écran ne change jamais selon l'univers**.

### 10.4 États d'interface obligatoires

Chaque écran qui charge des données implémente : **chargement** (squelette), **vide**, **erreur** (avec action « Réessayer »), et selon le cas **hors ligne**, **première utilisation**, **génération IA**, **échec de génération**, **validation requise**, **élément supprimé** (toast avec « Annuler » 8 s), **élément archivé**. Le design de chacun est dans la planche « Tous les états ».

---

## 11. Sécurité, confidentialité et RGPD

> ⚠️ Xylou traite des données de mineurs. Les points ci-dessous sont des exigences techniques ; leur conformité juridique doit être validée par un DPO ou un juriste spécialisé avant la mise en production.

### 11.1 Minimisation et consentement

- Seuls le **prénom** et le **niveau** sont obligatoires pour un profil enfant ; pas de photo, pas de date de naissance complète (année seulement).
- Consentement parental explicite, **horodaté et versionné** (`consents`), réaffiché si la politique change.
- Paramètres de confidentialité par enfant (voir l'écran « Confidentialité ») : historique détaillé, amélioration anonymisée (désactivée par défaut), validation adulte obligatoire.

### 11.2 Droits des personnes

- **Export** : job `ExportChildData` → archive ZIP (JSON + PDF des bilans) disponible 7 jours via lien signé.
- **Suppression** : soft-delete immédiat (profil masqué, partages révoqués), **purge définitive à J+30** par tâche planifiée, y compris médias et index de recherche. Journalisée.
- **Rétention** : tentatives détaillées conservées 90 jours si l'option « historique détaillé » est désactivée ; agrégats conservés.

### 11.3 Partage avec les professionnels

- Invitation par e-mail → le pro crée son compte (2FA) → accès limité aux `scopes` choisis et à une **durée** (défaut 3 mois).
- Révocation immédiate par le parent ; le pro conserve **uniquement ses propres observations**.
- Chaque consultation d'un profil par un pro est journalisée et visible par le parent.

### 11.4 Sécurité applicative

- HTTPS partout, en-têtes de sécurité (CSP stricte, HSTS, `X-Frame-Options`), cookies `Secure`/`HttpOnly`/`SameSite=Lax`.
- Chiffrement des champs sensibles (`encrypted` cast) ; rotation de `APP_KEY` documentée dans un runbook.
- Limitation de débit sur connexion, génération IA, invitations.
- Dépendances surveillées (Dependabot + `composer audit` + `npm audit` en CI).
- Hébergement et sauvegardes **dans l'UE** ; sauvegardes chiffrées quotidiennes, test de restauration mensuel.
- Aucune donnée personnelle dans les logs ni dans Sentry (scrubbing configuré).

---

## 12. Qualité, tests, CI/CD et exploitation

### 12.1 Stratégie de tests

| Niveau | Outil | Ce qu'on teste | Cible |
|---|---|---|---|
| Architecture | Pest `arch()` | Les contrôleurs n'accèdent pas aux modèles d'autres domaines ; `ui` n'importe rien du métier ; `strict_types` partout ; pas de `dd/dump` | 100 % des règles |
| Unitaire | Pest / Vitest | Actions, calcul de maîtrise, règles de badges, vérificateurs IA, composables | Couverture ≥ 80 % sur `app/Domain` |
| Fonctionnel | Pest (HTTP + Inertia assertions) | Chaque route : autorisé / refusé / validation | Toutes les routes |
| Bout en bout | Playwright | Parcours critiques (voir ci-dessous) | 100 % des parcours critiques |
| Accessibilité | axe-core dans Playwright | Aucune violation « serious » ou « critical » | 0 violation |

**Parcours critiques E2E** : inscription + onboarding complet ; génération → validation → envoi → exercice enfant (bonne réponse, « Pas encore », indice, « Je ne sais pas ») → activité terminée ; reprise d'activité interrompue ; partage avec un pro puis révocation ; génération et export d'un bilan ; suppression d'un profil enfant.

**IA en test** : toujours `FakeAiProvider` (réponses fixtures). Un job de CI séparé, manuel, teste le vrai fournisseur sur un jeu d'exemples.

### 12.2 Définition de « terminé » (Definition of Done)

Une fonctionnalité est terminée si et seulement si :
- [ ] Le code respecte la structure par domaine et le pattern Action.
- [ ] Tests unitaires et fonctionnels écrits et verts ; parcours E2E mis à jour si concerné.
- [ ] Pint, ESLint, Prettier, Larastan (niveau configuré), `vue-tsc` passent.
- [ ] Tous les états d'interface (§10.4) sont implémentés.
- [ ] Accessibilité : navigation clavier vérifiée, axe sans violation grave.
- [ ] Chaînes traduites dans `lang/fr` / `i18n`.
- [ ] Autorisations couvertes par des tests « refusé ».
- [ ] Documentation mise à jour (`docs/domain/<domaine>.md`, ADR si décision structurante).
- [ ] Revue de code approuvée par au moins un autre développeur.

### 12.3 Git et revue

- Branche principale `main` protégée ; branches `feat/…`, `fix/…`, `chore/…`.
- **Conventional Commits** (`feat(activities): approve and send to child`).
- Pull requests petites (idéalement < 400 lignes modifiées), avec description, captures d'écran pour l'UI, lien vers l'écran de maquette.
- Modèle de PR et `CODEOWNERS` par domaine.

### 12.4 CI (GitHub Actions)

À chaque PR : installation (cache) → Pint (test) → ESLint/Prettier → Larastan → `vue-tsc` → Pest (PostgreSQL + Redis en services) → Vitest → build Vite → Playwright + axe (sur l'application construite) → `composer audit` / `npm audit`.
Sur `main` : déploiement automatique en **préproduction** ; production déclenchée manuellement (tag de version).

### 12.5 Environnements & exploitation

- `local` (Sail) · `staging` (données fictives uniquement) · `production`.
- Déploiement sans interruption (Laravel Forge/Envoyer, Laravel Cloud, ou conteneurs) sur hébergeur UE.
- Workers Horizon supervisés ; planificateur (`schedule:run`) pour : purge RGPD, résumés hebdomadaires, rappels doux, expiration des partages.
- Observabilité : Sentry (erreurs), Pulse (performances), alertes sur échecs de jobs IA et PDF.
- Runbooks dans `docs/runbooks/` : incident IA indisponible, restauration de sauvegarde, rotation de clés, demande RGPD manuelle.

---

## 13. Plan de réalisation étape par étape

> Chaque étape se termine par : tests verts, démo fonctionnelle, documentation à jour. **Ne pas commencer une étape avant que la précédente respecte la Definition of Done.**

### Étape 0 — Fondations du projet
**Tâches**
1. Créer le projet Laravel avec le starter kit **Vue + Inertia + TypeScript**. Configurer Sail (PostgreSQL, Redis, Meilisearch, Mailpit).
2. Installer et configurer : Pint, Larastan, Rector, Pest (+ plugin arch), ESLint, Prettier, Vitest, Playwright, Husky/lint-staged (optionnel).
3. Créer la structure `app/Domain/*` vide + tests d'architecture initiaux.
4. Configurer la CI GitHub Actions complète (§12.4).
5. Rédiger `README.md` (démarrage en < 10 min), `CLAUDE.md` (§14), ADR 0001 à 0005 (§15.4).

**Critères d'acceptation** : `sail up` + une commande de setup lancent l'app ; la CI est verte sur une PR vide.

### Étape 1 — Design system et layouts
1. Configurer Tailwind avec les jetons (§10.1), polices auto-hébergées.
2. Construire les composants `Components/ui` (§10.2) + page de documentation (Histoire).
3. Construire `XMascot` et `XUniverseScene` (SVG) pour les univers espace, football, forêt.
4. Construire les layouts : `PublicLayout`, `KidLayout` (rail tablette + barre mobile), `ParentLayout` (barre latérale desktop, rail tablette, barre mobile), `ProLayout` (sobre).
5. Tests Vitest de chaque composant.

**Critères** : chaque composant a ses variantes et états documentés ; axe sans violation ; rendu conforme aux planches « Composants » et « Fondations ».

### Étape 2 — Identité, rôles, sessions
1. Authentification parent et pro (inscription, connexion, vérification e-mail, mot de passe oublié, 2FA pro obligatoire).
2. spatie/permission : rôles, middleware par espace, Policies de base.
3. Session enfant (garde `kid`), code parent pour sortir, code image optionnel.
4. Journal d'audit branché.

**Critères** : tests « refusé » pour chaque combinaison de rôles ; un enfant ne peut jamais atteindre une route parent.

### Étape 3 — Landing page et onboarding parent
1. Landing (toutes les sections de la maquette), responsive, SEO de base, pages légales.
2. Onboarding en 7 étapes (compte → profil enfant → univers → objectifs → difficultés → préférences → résumé) avec **sauvegarde à chaque étape** (reprise possible).
3. Domaine `Children` : Actions `CreateChildProfile`, `UpdateInterests`, `SetGoals`, `RecordDifficulties`, `SetLearningPreferences`.
4. Écran résumé « L'univers de Lucas » avec édition par section.
5. Consentement parental enregistré à l'étape 1.

**Critères** : parcours E2E complet ; données persistées ; retour arrière sans perte.

### Étape 4 — Référentiel et bibliothèque
1. Domaine `Curriculum` : matières, compétences (YAML → seeder), univers, intérêts.
2. Domaine `Activities` : modèles, Enum de statuts + transitions, Actions `CreateActivity`, `DuplicateActivity`, `ArchiveActivity`, `DeleteActivity` (soft), `RestoreActivity`.
3. Bibliothèque : filtres (matière, compétence, niveau, durée, difficulté, thème, statut), Scout + Meilisearch, recherche en langage naturel simple (analyse des mots-clés → filtres), actions sauvegarder/dupliquer/modifier/archiver/recommander.
4. Création manuelle d'une activité (éditeur d'items).

**Critères** : toutes les transitions interdites sont rejetées et testées ; états vide/archivé/supprimé (toast « Annuler ») implémentés.

### Étape 5 — Génération IA et validation adulte
1. Domaine `Ai` : `AiProvider`, `FakeAiProvider`, fournisseur réel, `config/ai.php`.
2. Prompts v1 par format (exercice, quiz, problème, histoire interactive, flashcards, défi).
3. `GenerateActivityJob` et pipeline complet (§8.4) : plan, génération, schéma, vérification des réponses, contrôles, persistance.
4. Formulaire de génération (matière, compétence, niveau auto, univers, difficulté, durée, format, consigne libre).
5. Écran d'attente calme + diffusion Reverb « prête à valider ».
6. Écran « Activité générée » : objectif, niveau, compétence, items, réponses attendues, explications, contexte utilisé, difficulté estimée, vérifications ; actions **Modifier / Régénérer (tout ou une question) / Approuver / Enregistrer / Envoyer à l'enfant**.
7. Écran d'échec de génération.

**Critères** : aucune activité `pending_review` n'est visible côté enfant (test) ; aucune donnée identifiante dans `ai_generations.input` (test) ; réponses mathématiques recalculées et cohérentes (test).

### Étape 6 — Expérience enfant et moteur d'exercice
1. Accueil enfant (tablette puis mobile) : salutation, activité recommandée, « À reprendre », aventures, progression, réussites.
2. Écran d'exercice immersif : progression, question, illustration, réponse, indice, « Je ne sais pas », valider.
3. Domaine `Learning` : `StartAssignment`, `RecordAttempt` (idempotent), `EvaluateAttempt`, `InterruptAssignment`, `ResumeAssignment`, `CompleteAssignment`.
4. Feedback « Bravo » / « Pas encore » / explication pas à pas ; écran « Activité terminée » ; écran de reprise.
5. Lecture à voix haute (Web Speech API), options de confort (police, animations).
6. Écrans « Ma progression », « Mes réussites », « Profil / univers ».

**Critères** : parcours E2E enfant complet ; utilisable entièrement au clavier et à l'écran tactile ; aucun texte enfant < 18 px ; jamais le mot « Faux ».

### Étape 7 — Progression, réussites, dashboards
1. Domaine `Progress` : calcul de maîtrise (§9.3), historique hebdomadaire, tendances.
2. Domaine `Rewards` : règles de badges, attribution, écran de célébration.
3. Résumés hebdomadaires (faits déterministes + reformulation IA modifiable).
4. Recommandations (règles simples : compétence « à consolider » + dernière pratique > 5 jours → proposer 2 activités courtes).
5. Dashboard parent (desktop, tablette, mobile), profil pédagogique, page Progression par compétence, sélecteur multi-enfants.

**Critères** : jeux de tests couvrant les seuils de niveaux ; dashboard lisible sans graphique (phrases + barres avec valeurs textuelles).

### Étape 8 — Bilans
1. Domaine `Reports` : paramètres (enfant, période, destinataire, sections), génération du contenu (faits + reformulation), édition section par section, observations de l'adulte.
2. Export PDF (template HTML dédié), impression, partage (lien signé à durée limitée ou partage à un pro).

**Critères** : le PDF correspond au texte relu ; lien partagé expirant testé.

### Étape 9 — Espace professionnel et partage
1. Invitations, acceptation, scopes, expiration, révocation, demande de prolongation.
2. Dashboard pro (profils partagés, points d'attention, demandes), fiche enfant (synthèse, compétences, difficultés récurrentes, historique, observations, recommandation d'activité → validée par le parent).
3. Journal des consultations visible par le parent.

**Critères** : tests d'accès exhaustifs (expiré, révoqué, mauvais scope) ; état « Accès expiré » implémenté.

### Étape 10 — Notifications
1. Types : nouvelle activité, recommandation, progression, objectif atteint, rappel doux, rapport disponible.
2. Canaux : application (temps réel) et e-mail ; préférences par type et canal ; mode calme ; résumé hebdomadaire par e-mail.
3. Centre de notifications (filtres par enfant, non lues, tout marquer comme lu).

### Étape 11 — Confidentialité, paramètres, abonnement
1. Écran Confidentialité : consentements, paramètres par enfant, partages, transparence IA, export, suppression.
2. Jobs d'export et de purge J+30 ; rétention configurable.
3. Abonnement : offres Découverte / Famille / Professionnel via Cashier ([PRIX] à définir), quotas IA liés à l'offre, portail de facturation.
4. Paramètres compte, notifications, contrôle parental, accessibilité.

### Étape 12 — PWA et hors ligne
1. vite-plugin-pwa, manifest, icônes, installation sur tablette.
2. Cache de l'activité en cours, file IndexedDB des tentatives, synchronisation, bannière « Hors ligne — tes réponses sont gardées ».

**Critères** : test E2E en mode hors ligne simulé (Playwright) sans perte de réponse.

### Étape 13 — Durcissement avant production
1. Audit d'accessibilité manuel (lecteur d'écran, clavier) sur les parcours critiques.
2. Tests de charge légers sur la génération IA et les dashboards ; index PostgreSQL vérifiés (`EXPLAIN`).
3. Revue de sécurité (en-têtes, CSP, limites de débit, permissions), test de restauration de sauvegarde.
4. Validation juridique (RGPD, mineurs, DPA fournisseurs, mentions légales).
5. Budget de performance : LCP < 2,5 s sur tablette milieu de gamme, JS initial de l'espace enfant < 200 Ko gzip.

### Étape 14 — Applications mobiles natives (optionnel)
1. Ajouter l'API REST versionnée (`/api/v1`, Sanctum, API Resources réutilisant les DTO).
2. Emballer l'interface Vue avec Capacitor ; notifications push ; publication sur les stores.

---

## 14. Directives pour Claude Code (`CLAUDE.md`)

> Copier le bloc suivant dans `CLAUDE.md` à la racine du dépôt.

```markdown
# CLAUDE.md — Xylou

## Contexte
Plateforme EdTech d'accompagnement scolaire personnalisé par IA (enfant / parent / professionnel).
Spécifications complètes : docs/SPECIFICATIONS.md — les lire avant toute tâche structurante.

## Stack
Laravel (PHP ≥ 8.3, strict_types) · PostgreSQL · Redis/Horizon · Reverb · Inertia · Vue 3 + TypeScript
(script setup) · Tailwind (jetons Xylou) · Pest · Larastan · Vitest · Playwright + axe.

## Règles d'architecture (obligatoires)
- Code métier dans app/Domain/<Domaine>/Actions ; contrôleurs minces (valider → autoriser → Action → Inertia).
- Un domaine n'écrit pas dans les modèles d'un autre domaine : passer par une Action ou un Event.
- DTO avec spatie/laravel-data ; types TS générés (ne jamais éditer resources/js/types/generated.d.ts).
- Statuts = Enums PHP ; transitions d'activité uniquement via Actions.
- Services externes (IA, PDF, paiement) derrière une interface ; utiliser les implémentations Fake en test.
- Composants resources/js/Components/ui : aucun appel serveur, aucune logique métier.
- Aucune chaîne en dur : lang/fr et vue-i18n.

## Invariants produit (ne jamais enfreindre)
- Aucun classement/comparaison entre enfants. Pas de note : niveaux de maîtrise uniquement.
- Aucune activité générée par IA visible par l'enfant sans approbation adulte.
- Côté enfant, jamais « Faux » : « Pas encore » + explication + nouvel essai.
- Aucune donnée identifiante (nom, e-mail, observations) envoyée au fournisseur IA.
- Accès professionnel : vérifier partage actif, non expiré, non révoqué, scope correct.

## Façon de travailler
1. Travailler étape par étape selon docs/SPECIFICATIONS.md §13 ; annoncer l'étape et la tâche.
2. Avant de coder : lire les fichiers concernés, proposer un plan court.
3. Écrire les tests en même temps que le code (Pest / Vitest / Playwright selon le cas).
4. Avant de conclure : lancer `composer lint`, `composer analyse`, `composer test`, `npm run lint`,
   `npm run typecheck`, `npm run test` ; tout doit passer.
5. Respecter la Definition of Done (§12.2), y compris les états d'interface (§10.4).
6. Ne pas ajouter de dépendance sans justification écrite (maintenance, popularité, licence).
7. Toute décision structurante → nouvel ADR dans docs/adr/.
8. Commits au format Conventional Commits ; PR petites et ciblées.
9. En cas d'ambiguïté fonctionnelle : demander plutôt que supposer.

## Commandes
- Démarrer : `./vendor/bin/sail up -d && ./vendor/bin/sail composer setup`
- Tests PHP : `sail pest` · Front : `npm run test` · E2E : `npm run test:e2e`
- Qualité : `sail composer lint && sail composer analyse && npm run lint && npm run typecheck`
- Types TS : `sail artisan typescript:transform`
```

> **Conseil d'utilisation avec Claude Code** : lui confier **une étape du §13 à la fois** (ex. « Réalise l'étape 1 — Design system et layouts »), lui demander d'abord un plan, relire, puis valider. Joindre des captures des écrans correspondants du canvas pour les étapes d'interface.

---

## 15. Annexes

### 15.1 Correspondance écrans ↔ pages Vue

| Écran (maquette) | Page Vue | Route nommée |
|---|---|---|
| Landing desktop/mobile | `Public/Home.vue` | `home` |
| Onboarding étapes 1–7 | `Onboarding/Step{1..7}.vue` (ou `Onboarding/Wizard.vue`) | `onboarding.step` |
| Accueil enfant | `Kid/Home/Index.vue` | `kid.home` |
| Exercice / indice / feedback | `Kid/Exercise/Play.vue` | `kid.exercise.play` |
| Activité terminée | `Kid/Exercise/Completed.vue` | `kid.exercise.completed` |
| Reprise d'activité | modal dans `Kid/Home/Index.vue` | — |
| Ma progression | `Kid/Progress/Index.vue` | `kid.progress` |
| Mes réussites | `Kid/Rewards/Index.vue` | `kid.rewards` |
| Profil & univers enfant | `Kid/Profile/Index.vue` | `kid.profile` |
| Dashboard parent | `Parent/Dashboard/Index.vue` | `parent.dashboard` |
| Enfants (multi-profils) | `Parent/Children/Index.vue` | `parent.children.index` |
| Profil pédagogique | `Parent/Children/Show.vue` | `parent.children.show` |
| Progression par compétence | `Parent/Progress/Index.vue` | `parent.progress` |
| Générer une activité | `Parent/Activities/Generate.vue` | `parent.activities.generate` |
| Génération en cours | état de `Generate.vue` | — |
| Validation adulte | `Parent/Activities/Review.vue` | `parent.activities.review` |
| Bibliothèque | `Parent/Library/Index.vue` | `parent.library` |
| Bilans | `Parent/Reports/{Index,Edit}.vue` | `parent.reports.*` |
| Notifications | `Parent/Notifications/Index.vue` | `parent.notifications` |
| Confidentialité | `Settings/Privacy.vue` | `settings.privacy` |
| Abonnement | `Settings/Billing.vue` | `settings.billing` |
| Dashboard pro | `Pro/Dashboard/Index.vue` | `pro.dashboard` |
| Fiche enfant pro | `Pro/Children/Show.vue` | `pro.children.show` |

### 15.2 Glossaire

| Terme | Définition |
|---|---|
| Univers | Thème de contextualisation (espace, football, animaux…) issu des passions de l'enfant. |
| Compétence | Unité du référentiel pédagogique (ex. « Problèmes en 2 étapes, CE2 »). Invariante lors de la personnalisation. |
| Activité | Ensemble d'items (questions) sur une compétence, dans un univers, avec un format et une difficulté. |
| Affectation (assignment) | Envoi d'une activité approuvée à un enfant ; porte l'avancement et la reprise. |
| Tentative (attempt) | Une réponse de l'enfant à un item. |
| Maîtrise | Score 0–100 par compétence, traduit en 4 niveaux. |
| Partage | Accès d'un professionnel à un profil, limité en portée et en durée. |
| Validation adulte | Approbation obligatoire d'une activité générée avant envoi à l'enfant. |

### 15.3 Formats d'activité et types de réponse

- **Formats** : `exercise`, `quiz`, `problem`, `interactive_story`, `flashcards`, `challenge`.
- **Types de réponse** : `number` (avec tolérance et unité), `text` (normalisation accents/casse), `single_choice`, `multiple_choice`, `ordering`, `matching`, `fill_blank`.
- **Difficultés** : `discovery`, `practice`, `consolidation`, `challenge`.
- **Durées** : 5, 10, 15, 20 minutes (≈ 1 item par 2–3 minutes selon le format).

### 15.4 ADR initiaux à rédiger (étape 0)

| N° | Décision | Résumé du pourquoi |
|---|---|---|
| 0001 | Monolithe modulaire Laravel + Inertia + Vue 3 | Une seule base de code, pas d'API interne, maintenance simplifiée. |
| 0002 | Organisation par domaines + pattern Action | Règles métier localisées, testables, découvrables. |
| 0003 | PostgreSQL | `jsonb`, contraintes, requêtes analytiques pour la progression. |
| 0004 | IA derrière `AiProvider`, sorties structurées, vérification déterministe | Remplaçabilité, sûreté pédagogique, testabilité. |
| 0005 | Maîtrise calculée de façon déterministe (moyenne mobile exponentielle) | Explicable, reproductible, sans note ni classement. |
| 0006 | Session enfant sans compte propre | Minimisation des données d'un mineur, sécurité de l'espace parent. |
| 0007 | PWA d'abord, Capacitor ensuite | Valeur rapide sur tablette, un seul code front. |

### 15.5 Modèle d'ADR

```markdown
# ADR 000X — Titre
- Statut : proposé | accepté | remplacé par 000Y
- Date : AAAA-MM-JJ
## Contexte
## Décision
## Conséquences (positives / négatives)
## Alternatives envisagées
```

---

*Document vivant : toute évolution de ce document passe par une pull request relue par au moins un membre de l'équipe.*
