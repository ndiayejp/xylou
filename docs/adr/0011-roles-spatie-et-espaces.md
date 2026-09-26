# ADR 0011 — Rôles avec spatie/laravel-permission, un espace par rôle

- Statut : accepté
- Date : 2026-09-26

## Contexte

Le modèle de données (§6.2) prévoit une colonne `users.role` et la stack (§3.1) retient spatie/laravel-permission. Garder les deux oblige à les synchroniser. Les routes sont groupées par espace (§7.3) : parent, professionnel, enfant. Les professionnels n'arrivent que par invitation d'un parent (§11.3, étape 9).

## Décision

- **Une seule source de vérité : les rôles spatie.** Pas de colonne `users.role`. Les noms sont dans l'Enum `App\Domain\Identity\Enums\Role` (`parent`, `professional`, `admin`) ; une migration crée ces rôles, présents dans tous les environnements, tests compris.
- Le modèle `User` passe dans `App\Domain\Identity\Models` (ADR 0002) avec `HasRoles`. Le preset d'architecture Laravel de Pest ignore `App\Domain`, qui range lui-même ses Models et Enums.
- **Un espace par rôle** : `parent.*` (préfixe `/parent`) derrière `role:parent`, `pro.*` (`/pro`) derrière `role:professional`, tous deux avec `auth` et `verified`. L'espace enfant (garde `kid`) viendra avec la session enfant.
- La route `dashboard` de Breeze reste la cible après connexion : `HomeController` redirige vers l'espace du compte (`User::homeRouteName()`), 403 pour un compte sans espace (admin, en attendant le back-office).
- L'inscription publique (`RegisterParent`) ne crée que des comptes parent. Les comptes pro sont créés par factory et seeder jusqu'à l'étape 9.
- Front : `ParentSpace`, `ProSpace` et `AccountSpace` alimentent les layouts depuis la session ; `useParentNavigation` / `useProNavigation` listent toute la navigation de la maquette et n'affichent que les entrées dont la route existe (`route().has`).

## Conséquences

- Positives : aucune désynchronisation possible ; le back-office admin pourra s'appuyer sur les permissions fines de spatie. La navigation se complète d'elle-même à chaque étape.
- Négatives : un rôle se lit par jointure (`hasRole`) plutôt que par colonne ; l'écart avec §6.2 doit être connu (d'où cet ADR). Un compte a en pratique un seul rôle, ce que spatie n'impose pas : les Actions de création s'en chargent.

## Alternatives envisagées

- Colonne `users.role` + spatie synchronisés : fidèle au §6.2, mais deux sources à tenir alignées.
- Colonne seule, sans spatie : plus simple aujourd'hui, mais il faudrait réintroduire des permissions pour le back-office.
- spatie/laravel-permission : MIT, ~6,4 millions de téléchargements par mois, standard de fait de l'écosystème Laravel.
