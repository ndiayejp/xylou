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

test('un pro arrive dans son espace et se déconnecte', async ({ page }) => {
    await login(page, 'pro@example.com');

    await expect(page).toHaveURL(/\/pro$/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Bonjour Claire');
    await expectNoSeriousViolations(page);

    await page.getByRole('button', { name: 'Mon compte' }).click();
    await page.getByRole('button', { name: 'Se déconnecter' }).click();

    await expect(page).toHaveURL(/\/$/);
});
