import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

// Comptes du DatabaseSeeder (mot de passe « password »).
async function login(page: Page, email: string): Promise<void> {
    await page.goto('/login');
    await page.locator('input[type="email"]').fill(email);
    await page.locator('input[type="password"]').fill('password');
    await page.locator('input[type="password"]').press('Enter');
}

async function expectNoSeriousViolations(page: Page): Promise<void> {
    const { violations } = await new AxeBuilder({ page }).analyze();

    expect(violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')).toEqual([]);
}

test('un parent arrive dans son espace et ne peut pas entrer dans l’espace pro', async ({
    page,
}) => {
    await login(page, 'parent@example.com');

    await expect(page).toHaveURL(/\/parent$/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Bonjour Sophie');
    await expectNoSeriousViolations(page);

    const response = await page.goto('/pro');
    expect(response?.status()).toBe(403);
});

test('un parent passe d’un enfant à l’autre', async ({ page }) => {
    await login(page, 'parent@example.com');

    // Point de départ connu, quel que soit le choix laissé par un passage précédent.
    const switcher = page.getByRole('button', { name: /^Changer d’enfant, actuellement/ });
    for (const [name, week] of [
        ['Emma', 'La semaine d’Emma'],
        ['Lucas', 'La semaine de Lucas'],
    ]) {
        await switcher.click();
        await page.getByRole('button', { name: new RegExp(`^${name}`) }).click();
        // Le sous-titre existe en version mobile (masquée) et bureau.
        await expect(page.getByText(week).filter({ visible: true })).toBeVisible();
        await expect(switcher).toHaveAccessibleName(`Changer d’enfant, actuellement ${name}`);
    }
    await expectNoSeriousViolations(page);
});

// Le pro de démo n'a pas de 2FA confirmée : il doit l'activer avant d'entrer dans son espace.
test('un pro sans 2FA est guidé vers son activation, puis se déconnecte', async ({ page }) => {
    await login(page, 'pro@example.com');

    await expect(page).toHaveURL(/\/settings\/security$/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Sécurité');
    await expect(page.getByText('Obligatoire pour les professionnels')).toBeVisible();

    // Base locale : l'activation d'un passage précédent peut déjà attendre sa confirmation.
    const enable = page.getByRole('button', { name: 'Activer' });
    if (await enable.isVisible()) {
        await enable.click();
    }
    await expect(page.getByRole('img', { name: /QR code/ })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Annuler' })).toHaveCount(0);
    await expectNoSeriousViolations(page);

    await page.getByRole('button', { name: 'Mon compte' }).click();
    await page.getByRole('button', { name: 'Se déconnecter' }).click();

    await expect(page).toHaveURL(/\/$/);
});

test('session enfant : l’enfant reste dans son espace, le code parent l’en fait sortir', async ({
    page,
}) => {
    await login(page, 'parent@example.com');
    await page.getByRole('button', { name: /^Ouvrir l’espace d/ }).click();

    await expect(page).toHaveURL(/\/enfant$/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(/^Bonjour (Emma|Lucas) !$/);
    await expectNoSeriousViolations(page);

    // L'enfant tente d'atteindre l'espace parent : il reste chez lui.
    await page.goto('/parent');
    await expect(page).toHaveURL(/\/enfant$/);

    await page.getByRole('link', { name: 'Espace parent' }).click();
    await expect(page).toHaveURL(/\/enfant\/sortie$/);
    await page.getByLabel('Mot de passe').fill('mauvais');
    await page.getByRole('button', { name: 'Revenir à l’espace parent' }).click();
    await expect(page.getByText('Ce code ne correspond pas.')).toBeVisible();

    await page.getByLabel('Mot de passe').fill('password');
    await page.getByRole('button', { name: 'Revenir à l’espace parent' }).click();
    await expect(page).toHaveURL(/\/parent$/);
});
