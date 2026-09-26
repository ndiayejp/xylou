# Domaine Identity

Comptes adultes, rôles et espaces. L'enfant n'a pas de compte : il utilise une session ouverte par un parent (à venir).

## Règles

- Un compte a un rôle : `parent`, `professional` ou `admin` (Enum `Role`, stocké par spatie/laravel-permission, ADR 0011).
- L'inscription publique crée toujours un **parent** (`RegisterParent`). Un professionnel ne s'inscrit que sur invitation d'un parent (étape 9).
- Chaque rôle n'accède qu'à son espace : `/parent` (`parent.*`), `/pro` (`pro.*`). Toute autre combinaison renvoie 403 ; un visiteur est renvoyé vers la connexion ; une adresse non vérifiée vers la vérification.
- Mot de passe : 12 caractères minimum ; en production, refus des mots de passe présents dans des fuites connues (`Password::defaults`, `AppServiceProvider`).
- Connexion limitée à 5 échecs par couple e-mail + IP (`LoginRequest`), message en français.
- Double authentification (TOTP, ADR 0012) : facultative pour un parent, **obligatoire pour un professionnel**, qui est renvoyé vers la page Sécurité tant qu'elle n'est pas confirmée et ne peut pas la désactiver. À la connexion, le code (ou un code de secours, à usage unique) est demandé avant d'ouvrir la session ; 5 essais par minute.
- **Session enfant** (ADR 0013) : un parent ouvre l'espace de son enfant sur son appareil ; sa propre session se ferme. Pendant la session enfant, toute page hors de l'espace enfant renvoie vers l'accueil enfant. Le retour à l'espace parent exige le code parent : PIN de 4 à 6 chiffres s'il est défini (page Sécurité), sinon le mot de passe ; 5 essais par minute.
- Après connexion, `dashboard` redirige vers l'espace du compte (`User::homeRouteName()`). Un admin n'a pas encore d'espace (403).

## Actions

| Action | Rôle |
|---|---|
| `RegisterParent` | Crée le compte, lui donne le rôle parent, déclenche `Registered` (mail de vérification). |
| `SetParentPin` | Définit (haché) ou retire le PIN parent. |

## Comptes de démonstration (`db:seed`)

| E-mail | Rôle | Mot de passe |
|---|---|---|
| `parent@example.com` | parent | `password` |
| `pro@example.com` | professional (sans 2FA : dirigé vers son activation) | `password` |

## Tests

- `tests/Feature/Identity/SpaceAccessTest.php` : chaque rôle contre chaque espace (autorisé et refusé), visiteur, adresse non vérifiée, aiguillage de `dashboard`.
- `tests/Feature/Identity/TwoFactorTest.php` : code demandé à la connexion, code valide / faux / de secours, 2FA obligatoire des pros, page Sécurité (confirmation du mot de passe, activation, désactivation, régénération).
- `tests/Feature/Identity/KidSessionTest.php` : ouverture (autorisée, refusée), invariant sur toutes les routes, sortie par PIN ou mot de passe, code faux, blocage après 5 essais, gestion du PIN.
- `tests/Browser/spaces.spec.ts` : connexion parent et pro, refus de l'espace pro pour un parent, pro guidé vers l'activation de la 2FA, déconnexion, axe.
