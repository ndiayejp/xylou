# ADR 0013 — Session enfant : garde dédiée, verrou global, sortie par code parent

- Statut : accepté
- Date : 2026-09-26

## Contexte

L'enfant n'a ni compte ni e-mail (§7.1). Le parent ouvre l'espace de son enfant sur son propre appareil (§7.2) ; revenir à l'espace parent exige un code parent. Invariant de l'étape 2 : un enfant ne peut jamais atteindre une route parent.

## Décision

- **Garde `kid`** (session, fournisseur `children`) : `ChildProfile` implémente `Authenticatable`, sans mot de passe ni jeton « se souvenir de moi ».
- **Ouverture** (`POST parent.children.kid-session`, capacité `openKidSession` de la Policy) : la session parent (`web`) est fermée, la session est régénérée, l'enfant est connecté sur `kid` et l'identifiant du parent est gardé en session (`kid.parent_id`).
- **Verrou global** : le middleware `KeepKidInKidSpace`, ajouté à tout le groupe `web`, renvoie vers `kid.home` toute requête hors `kid.*` tant qu'une session enfant est ouverte (espace parent, pro, profil, connexion, déconnexion…). Il est placé avant `AuthenticatesRequests` dans la liste de priorité, sans quoi `auth` renverrait vers la connexion. Un test parcourt **toutes** les routes GET du groupe `web` : une nouvelle route est couverte d'office.
- **Espace enfant** : préfixe `/enfant`, middleware `kid.session` (`EnsureKidSession`). Les pages ne reçoivent que le prénom de l'enfant (`kid`), jamais `auth.user` ni les données parent.
- **Sortie** (`kid.exit`) : code parent = PIN de 4 à 6 chiffres s'il est défini (`users.parent_pin`, haché), sinon le mot de passe du compte. 5 essais par minute et par enfant ; en cas de succès, la session enfant se ferme et le parent est reconnecté.
- Le PIN se gère dans la page Sécurité (parents seulement, derrière `password.confirm`).

## Conséquences

- Positives : l'invariant tient par construction (un seul point de contrôle) et reste vérifié automatiquement ; le parent n'est jamais connecté pendant la session enfant, donc aucune donnée parent ne fuit dans les props.
- Négatives : sortir de la session enfant demande toujours un code, même au parent ; si le compte parent disparaît pendant la session, la sortie ferme la session enfant et renvoie vers la connexion.

## Alternatives envisagées

- Garder la session parent ouverte et filtrer les routes parent : plus simple, mais une seule route oubliée suffirait à exposer l'espace parent.
- Compte enfant avec mot de passe : contraire à la minimisation (§11.1) et à l'usage à la maison.
- Code image (l'enfant ouvre lui-même sa session avec 3 pictos) : prévu en option, traité séparément.
