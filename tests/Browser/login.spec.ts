import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

const pages = [
    ['/login', 'Bon retour sur Xylou'],
    ['/register', 'Créons votre espace parent'],
    ['/forgot-password', 'Mot de passe oublié'],
] as const;

for (const [url, heading] of pages) {
    test(`${url} s'affiche en français sans violation d'accessibilité grave`, async ({ page }) => {
        await page.goto(url);
        await expect(page.getByRole('heading', { level: 1 })).toHaveText(heading);

        const { violations } = await new AxeBuilder({ page }).analyze();
        const serious = violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');

        expect(serious).toEqual([]);
    });
}

test('un échec de connexion affiche l’erreur sous le champ', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Adresse e-mail').fill('inconnu@example.com');
    await page.getByLabel('Mot de passe').fill('mauvais-mot-de-passe');
    await page.getByRole('button', { name: 'Se connecter' }).click();

    const email = page.getByLabel('Adresse e-mail');
    await expect(email).toHaveAttribute('aria-invalid', 'true');
    await expect(page.getByText('Ces identifiants ne correspondent pas')).toBeVisible();
});
