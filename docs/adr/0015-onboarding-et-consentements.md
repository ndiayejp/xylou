# ADR 0015 — Onboarding sauvegardé étape par étape, consentements versionnés

- Statut : accepté
- Date : 2026-09-27

## Contexte

L'étape 3 demande un onboarding en 7 écrans (compte, profil de l'enfant, univers, objectifs, difficultés, préférences, résumé) avec sauvegarde à chaque étape, reprise possible et retour arrière sans perte, ainsi qu'un consentement parental horodaté et versionné (§11.1). Décisions de l'utilisateur (2026-09-26) : l'onboarding passe avant la vérification de l'e-mail ; un enfant supplémentaire reprend les écrans 2 à 7.

## Décision

- **Écran 1 = inscription** (`register`, `Onboarding\AccountController`) : prénom, nom, e-mail, mot de passe (12 caractères min.), deux consentements obligatoires. Il remplace l'inscription de Breeze. Le compte, les consentements et l'onboarding sont créés dans une transaction ; le mail de vérification part tout de suite.
- **Consentements** (domaine `Privacy`) : table `consents` (type `ConsentKind`, version `config('xylou.consent_version')`, date, IP pseudonymisée par HMAC avec la clé de l'application). Une nouvelle version de la politique devra être réacceptée.
- **Progression** (domaine `Children`) : table `onboardings`, une ligne par enfant décrit (`reached_step`, `completed_at`). On peut revenir sur une étape atteinte, jamais sauter en avant ; les étapes 3 et suivantes exigent le profil de l'enfant. Chaque écran relit ses données : revenir en arrière retrouve le formulaire rempli, et le réenvoyer met à jour sans doublon.
- **Reprise** : la route `dashboard` (cible de toute connexion) reprend l'onboarding en cours avant d'exiger l'adresse vérifiée ; les écrans 2 à 7 sont accessibles à un parent non vérifié, l'espace parent non.
- **Enfant supplémentaire** : « Ajouter un enfant » (`onboarding.start`) ouvre un nouvel onboarding aux écrans 2 à 7, ou reprend celui dont l'enfant n'est pas encore décrit.
- **Écran 2** : on demande l'âge, on ne garde que l'année de naissance ; avatar = pictogramme (`AvatarKey`), jamais une photo ; options de confort dans `comfort_settings`.
- Provisoire : les écrans 3 à 7 affichent un écran d'attente jusqu'à leur PR ; le parcours et la progression restent complets et testés.

## Conséquences

- Positives : aucune donnée perdue si le parent s'interrompt ; le consentement est prouvable sans conserver d'IP en clair ; un seul parcours pour tous les enfants.
- Négatives : un parent peut créer un profil d'enfant avant d'avoir vérifié son adresse (accepté : l'espace parent reste fermé tant qu'elle n'est pas vérifiée). spatie/laravel-data n'étant pas encore installé, la saisie passe par un objet immuable (`ChildProfileInput`).

## Alternatives envisagées

- Garder l'état de l'onboarding en session : perdu à la déconnexion, pas de reprise sur un autre appareil.
- Colonnes d'onboarding sur `users` : ne permet pas de décrire plusieurs enfants.
- Vérifier l'e-mail avant l'écran 2 : plus strict, mais coupe le parcours (écarté par l'utilisateur).
