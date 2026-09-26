# Domaine Privacy

Consentements, et plus tard export, suppression et rétention (§11).

## Règles

- À l'inscription (écran 1 de l'onboarding), deux consentements sont obligatoires : être le parent ou le responsable légal de l'enfant (`parental_authority`), accepter les conditions d'utilisation et la politique de confidentialité (`terms_privacy`).
- Chaque consentement est **horodaté et versionné** (`config('xylou.consent_version')`). Une nouvelle version de la politique devra être réacceptée.
- L'IP n'est jamais conservée en clair : seulement un HMAC-SHA256 avec la clé de l'application (preuve sans donnée personnelle).

## Actions

| Action | Rôle |
|---|---|
| `RecordConsent` | Enregistre un consentement accordé, sa version et l'IP pseudonymisée. |

## Tests

- `tests/Feature/Auth/RegistrationTest.php` : consentements enregistrés, versionnés, IP hachée ; sans consentement, pas de compte.
