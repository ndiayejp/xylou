import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

test("la page de connexion s'affiche sans violation d'accessibilité grave", async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('input[type="email"]')).toBeVisible();

    const { violations } = await new AxeBuilder({ page }).analyze();
    const serious = violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');

    expect(serious).toEqual([]);
});
