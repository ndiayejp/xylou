# Domaine Children

Profils enfants. Un enfant n'a pas de compte : son profil appartient à un parent.

## Règles

- Minimisation (§11.1) : seuls le **prénom** et le **niveau** sont obligatoires ; l'année de naissance est facultative (jamais la date complète), pas de photo.
- Niveaux : Enum `Grade`, du CP à la 3e (`cp`, `ce1`, `ce2`, `cm1`, `cm2`, `6e`, `5e`, `4e`, `3e`) ; libellés dans vue-i18n (`children.grades.*`).
- Profil complété à l’onboarding (ADR 0015) : langue d’apprentissage (`fr`), avatar pictogramme (`AvatarKey`, jamais de photo), options de confort (`comfort_settings` : lecture à voix haute, police très lisible, sans chronomètre). On demande l’âge et on ne garde que l’année de naissance.
- Onboarding : une ligne `onboardings` par enfant décrit ; on revient sur une étape atteinte, on ne saute pas en avant, les étapes 3+ exigent le profil ; accessible à l’onboarding de son seul parent (`OnboardingPolicy`).
- **Univers** (écran 3) : référentiel `interests` (15 passions de la maquette, créées par migration, catégories sports, animaux, sciences, jeux, musique, activités ; libellés vue-i18n `interests.<clé>`), choix ordonné (`child_interest.rank`) et jusqu’à 5 passions libres (`custom_interests`). Au moins une passion ; idéal 3 à 5.
- **Objectifs** (écran 4) : Enum `Goal`, plusieurs objectifs, un seul principal, note obligatoire pour « Autre objectif ». Ils orientent les recommandations, pas le niveau.
- **Difficultés** (écran 5, facultatif) : catalogue `DifficultyCatalog` (matières maths, français, sciences, autre, plus « au quotidien » ; libellés vue-i18n `difficulties.<matière>.<clé>`), lignes `child_difficulties` (`skill_id` réservé au futur référentiel de compétences). L’observation libre est chiffrée (`child_profiles.difficulty_observation`, cast `encrypted`) et stockée une seule fois par enfant, pas par difficulté comme dans la spécification. Rien n’est montré à l’enfant ni envoyé au fournisseur IA. « Je ne sais pas encore » passe l’écran sans rien enregistrer.
- **Préférences** (écran 6) : `learning_preferences` (une ligne par enfant) : façons d’apprendre (`LearningPreferences::STYLES`), durée d’une séance (5, 10, 15 ou 20 min, 10 par défaut), rappel doux.
- Suppression douce (`deleted_at`) : un profil supprimé disparaît de toutes les listes, même pour son parent. La purge définitive à J+30 viendra avec le domaine Privacy.
- **Accès** (`ChildProfilePolicy`) : seul le parent propriétaire voit, modifie ou supprime le profil ; seul un parent peut en créer un. Pro, admin, autre parent, compte sans rôle et visiteur sont refusés. L'accès des pros passera par un partage actif (étape 9, `ShareGate`).
- Espace parent : les enfants du parent sont partagés avec toutes les pages (`parent.children`, par ordre alphabétique) ; l'enfant courant est choisi dans le sélecteur (`POST parent.current-child`, contrôlé par la Policy) et gardé en session, sinon le premier.

## Actions

| Action | Rôle |
|---|---|
| `CreateChildProfile` | Crée le profil et le rattache au parent (`ChildProfileInput`). |
| `UpdateChildProfile` | Met à jour le profil. |
| `StartOnboarding` | Démarre la description d’un enfant, ou reprend celle dont l’enfant n’est pas encore décrit. |
| `SaveOnboardingChild` | Écran 2 : crée le profil au premier passage, le met à jour ensuite, ouvre l’écran 3. |
| `AdvanceOnboarding` | Valide une étape ; la dernière termine l’onboarding. |
| `UpdateInterests` | Écran 3 : passions du référentiel (dans l’ordre choisi) et passions libres. |
| `SetGoals` | Écran 4 : objectifs, dont un seul principal choisi parmi eux ; note pour « Autre ». |
| `RecordDifficulties` | Écran 5 : remplace les difficultés, enregistre ou efface l’observation. |
| `SetLearningPreferences` | Écran 6 : façons d’apprendre, durée d’une séance, rappel doux. |

## Données de démonstration (`db:seed`)

`parent@example.com` : Emma (CE2, née en 2018) et Lucas (6e, né en 2015).

## Tests

- `tests/Feature/Children/ChildProfilePolicyTest.php` : chaque capacité contre chaque type d'utilisateur (autorisé et refusé), profil supprimé.
- `tests/Feature/Children/CurrentChildTest.php` : props partagées, changement d'enfant, enfant d'un autre (403), supprimé (404), pro et visiteur refusés, `CreateChildProfile`.
- `tests/Feature/Children/OnboardingTest.php` `OnboardingInterestsGoalsTest.php` et `OnboardingDifficultiesPreferencesTest.php` : écrans 2 à 6, validation, retour sans perte, progression, reprise, accès.
- `tests/Unit/Children/GradeTest.php` ; `tests/Browser/spaces.spec.ts` : passage d'un enfant à l'autre.
