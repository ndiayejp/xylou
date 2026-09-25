# ADR 0004 — IA derrière `AiProvider`, sorties structurées, vérification déterministe

- Statut : accepté
- Date : 2026-09-25

## Contexte

La génération d'activités par IA doit rester pédagogiquement sûre (réponses justes, contenus adaptés à l'âge), respecter la vie privée d'enfants et survivre à un changement de fournisseur ou de modèle.

## Décision

- L'IA habille une compétence ; elle ne choisit ni la compétence, ni le niveau, ni la réponse. Le système calcule la structure et les réponses attendues.
- Tout appel passe par l'interface `AiProvider::generateStructured()`, dont la sortie JSON est contrainte par un schéma. Le fournisseur et le modèle sont définis dans `config/ai.php`.
- Chaque sortie est validée (schéma), puis vérifiée côté PHP (recalcul des réponses, contrôle du contenu). Toute incohérence entraîne un rejet.
- Aucune donnée identifiante (nom, e-mail, observations, difficultés en texte libre) n'est envoyée au fournisseur.
- Une activité générée n'est visible par l'enfant qu'après approbation d'un adulte.
- Les prompts sont versionnés dans `resources/prompts/<version>/`.
- En test, on utilise toujours `FakeAiProvider`.

## Conséquences

- Positives : fournisseur remplaçable par configuration, tests déterministes et sans coût, erreurs de l'IA interceptées avant l'enfant.
- Négatives : pipeline plus long (validation, vérification, relecture adulte) ; chaque nouveau format d'activité demande un schéma et un vérificateur.

## Alternatives envisagées

- Appels directs au SDK d'un fournisseur depuis le métier : couplage fort, tests coûteux.
- Génération libre, vérifiée seulement par l'adulte : risque d'erreurs mathématiques non détectées.
