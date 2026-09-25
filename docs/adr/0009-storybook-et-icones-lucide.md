# ADR 0009 — Storybook pour documenter les composants, Lucide pour les icônes

- Statut : accepté
- Date : 2026-09-26

## Contexte

La spécification (§10.2) demande de documenter chaque composant `Components/ui` dans Histoire ou Storybook, avec ses variantes et ses états, et de vérifier l'accessibilité. Le projet tourne sur Vite 7 et Vitest 5. La maquette dessine ses icônes en trait de 2 px, bouts arrondis, grille de 24 : le style de Lucide.

## Décision

- **Storybook 10** (`@storybook/vue3-vite`, `addon-docs`, `addon-a11y`), configuré dans `.storybook/`. Les stories sont à côté des composants (`XButton.stories.ts`). Le plugin Laravel de Vite est retiré de la config Storybook. L'addon a11y lance axe sur chaque story et signale les violations comme des erreurs.
- La CI construit Storybook (`npm run build-storybook`) : une story cassée fait échouer la PR.
- Les tests Vitest des composants vérifient le rendu, les états et l'accessibilité de base avec `axe-core` (`resources/js/test/axe.ts`). Le contraste n'y est pas testé (jsdom ne calcule pas les couleurs) : il l'est par `tokens.spec.ts`, Storybook et Playwright.
- **Icônes : `@lucide/vue`**. Les composants reçoivent un composant d'icône en prop (`:icon="Check"`), toujours décoratif (`aria-hidden`) et accompagné d'un libellé ou d'un `aria-label`.
- Les composants `ui` ne contiennent aucun texte : tout libellé arrive par slot ou par prop. Ils n'ont donc pas besoin de vue-i18n.

## Conséquences

- Positives : Storybook suit Vite 7 et est très maintenu ; documentation, contrôle a11y et catalogue visuel au même endroit. Lucide couvre des centaines d'icônes cohérentes avec la maquette, et seules les icônes importées sont embarquées.
- Négatives : Storybook ajoute des dépendances de développement lourdes et un build de plus en CI (~30 s). Les icônes Lucide diffèrent très légèrement des tracés simplifiés de la maquette.

## Alternatives envisagées

- Histoire : plus léger et pensé pour Vue, mais sa version compatible Vite 7 (1.0.0-beta.1) est encore en beta, sans publication depuis janvier 2026, et sa communauté est bien plus petite.
- Recopier les SVG de la maquette dans un composant `XIcon` : aucune dépendance, mais chaque nouvelle icône à dessiner ou copier à la main.
