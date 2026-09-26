# ADR 0014 — Journal d'audit : spatie/laravel-activitylog, sans donnée personnelle

- Statut : accepté
- Date : 2026-09-26

## Contexte

Xylou traite des données de mineurs. La spécification demande un journal d'audit des accès sensibles (§3.1, spatie/laravel-activitylog) et interdit toute donnée personnelle dans les logs (§11.4). L'étape 2 doit le brancher sur l'identité et les sessions.

## Décision

- **spatie/laravel-activitylog 5**, table `activity_log`, deux journaux :
  - `security` : connexions (réussie, échouée, déconnexion, blocage), réinitialisation du mot de passe, suppression de compte, 2FA (activation, confirmation, désactivation, codes de secours, code accepté, refusé ou de secours), session enfant (ouverture, sortie, code parent refusé, blocage), PIN parent (défini, retiré).
  - `children` : création, modification, suppression d'un profil enfant (trait `LogsActivity`, options par défaut : **aucun attribut** enregistré).
- Chaque entrée ne contient que le type d'action (`event`, Enum `AuditEvent`), l'auteur (`causer`) et, le cas échéant, le sujet (l'enfant). Jamais d'e-mail, de nom, de prénom, de code, ni d'IP. Un échec de connexion sur un compte inconnu ne garde pas l'e-mail saisi ; une suppression de compte ne garde que l'identifiant.
- Un seul abonné, `AuditSubscriber` (domaine Identity), écoute les événements de Laravel, de Fortify et des événements métier (`KidSessionOpened`, `KidSessionClosed`, `ParentCodeRejected`, `ParentPinChanged`, `AccountDeleted`). Les connexions sur la garde `kid` ne sont pas journalisées deux fois : les événements de session enfant les couvrent.
- **Rétention** : un an (`clean_after_days`), purge quotidienne planifiée (`activitylog:clean`).
- Correctif lié : la connexion vérifie les identifiants sans `Auth::attempt()` (ADR 0012) ; elle émet désormais elle-même l'événement `Failed`.

## Conséquences

- Positives : traçabilité RGPD sans exposer de données ; ajouter un événement = une ligne dans l'abonné et un test.
- Négatives : un journal pauvre en contexte (pas d'IP) rend l'enquête sur un incident plus difficile ; une IP hachée pourra être ajoutée si le DPO le juge utile. La consultation par le parent des accès des pros (§11.3) viendra avec l'étape 9.

## Alternatives envisagées

- Logs applicatifs (fichiers) : pas de requêtes possibles, rétention difficile à maîtriser, risque de données personnelles.
- Table d'audit maison : même résultat que spatie/laravel-activitylog, à maintenir soi-même.
- spatie/laravel-activitylog : MIT, ~3,9 millions de téléchargements par mois, prévu par la spécification.
