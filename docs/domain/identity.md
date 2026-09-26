# Domaine Identity

Comptes adultes, rôles et espaces. L'enfant n'a pas de compte : il utilise une session ouverte par un parent (à venir).

## Règles

- Un compte a un rôle : `parent`, `professional` ou `admin` (Enum `Role`, stocké par spatie/laravel-permission, ADR 0011).
- L'inscription publique crée toujours un **parent** (`RegisterParent`). Un professionnel ne s'inscrit que sur invitation d'un parent (étape 9).
- Chaque rôle n'accède qu'à son espace : `/parent` (`parent.*`), `/pro` (`pro.*`). Toute autre combinaison renvoie 403 ; un visiteur est renvoyé vers la connexion ; une adresse non vérifiée vers la vérification.
- Après connexion, `dashboard` redirige vers l'espace du compte (`User::homeRouteName()`). Un admin n'a pas encore d'espace (403).

## Actions

| Action | Rôle |
|---|---|
| `RegisterParent` | Crée le compte, lui donne le rôle parent, déclenche `Registered` (mail de vérification). |

## Comptes de démonstration (`db:seed`)

| E-mail | Rôle | Mot de passe |
|---|---|---|
| `parent@example.com` | parent | `password` |
| `pro@example.com` | professional | `password` |

## Tests

- `tests/Feature/Identity/SpaceAccessTest.php` : chaque rôle contre chaque espace (autorisé et refusé), visiteur, adresse non vérifiée, aiguillage de `dashboard`.
- `tests/Browser/spaces.spec.ts` : connexion parent et pro, refus de l'espace pro pour un parent, déconnexion, axe.
