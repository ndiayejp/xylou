# ADR 0012 — Laravel Fortify pour la double authentification, et seulement pour elle

- Statut : accepté
- Date : 2026-09-26

## Contexte

La 2FA est obligatoire pour les professionnels et facultative pour les parents (§7.1). Breeze gère déjà l'inscription, la connexion, la vérification d'e-mail et les mots de passe, avec des pages Inertia refaites à l'étape 2. Fortify, paquet officiel Laravel, apporte une 2FA TOTP complète (activation, confirmation, codes de secours, code à la connexion), mais aussi ses propres routes d'authentification, qui feraient doublon.

## Décision

- **Fortify 1.40, fonctionnalité `twoFactorAuthentication` seule** (`confirm` et `confirmPassword` activés). Toutes ses autres fonctionnalités sont retirées de `config/fortify.php`. `Fortify::ignoreRoutes()` : les routes utiles sont déclarées dans `routes/auth.php` avec les contrôleurs de Fortify (activation, confirmation, désactivation, régénération des codes, challenge à la connexion), derrière `auth` + `password.confirm`.
- **Connexion** : `LoginRequest::validateCredentials()` vérifie les identifiants sans ouvrir la session. Si la 2FA est confirmée, la session reçoit `login.id` / `login.remember` et l'utilisateur passe par `two-factor.login` ; Fortify ouvre la session après un code valide (ou un code de secours, consommé). Le challenge est limité à 5 essais par minute. Une connexion réussie marque le mot de passe comme confirmé.
- **Page « Sécurité »** (`settings.security`, derrière `password.confirm`) : le QR code, la clé et les codes de secours arrivent en props Inertia (pas d'appel AJAX) ; le QR code est affiché en image avec un texte alternatif.
- **Professionnels** : le middleware `two-factor.required` (`EnsureTwoFactorIsEnabled`) renvoie un pro sans 2FA confirmée vers la page Sécurité ; la désactivation est réservée aux parents (`role:parent`).
- Traductions françaises des messages de Fortify via `laravel-lang` (`lang/fr.json`).

## Conséquences

- Positives : code TOTP éprouvé et maintenu par l'équipe Laravel, sans le réécrire ; aucune route Fortify inutile exposée.
- Négatives : Fortify 1.40 impose `laravel/passkeys` (non activé, migration non publiée). La connexion Breeze a été adaptée à la main pour déléguer le code à Fortify : une montée de version de Fortify doit vérifier le contrat de session `login.id` / `login.remember`.

## Alternatives envisagées

- pragmarx/google2fa directement : plus léger, mais activation, codes de secours, challenge et leurs tests à écrire et maintenir.
- Fortify complet à la place de Breeze : une seule brique, mais les pages et contrôleurs Breeze déjà refaits seraient à reprendre.
- laravel/fortify : MIT, paquet officiel Laravel.
