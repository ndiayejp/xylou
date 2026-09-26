import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

async function expectNoSeriousViolations(page: Page): Promise<void> {
    const { violations } = await new AxeBuilder({ page }).analyze();

    expect(violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')).toEqual([]);
}

test('la landing : titre, description, sections et accès à l’inscription', async ({ page }) => {
    await page.goto('/');

    await expect(page).toHaveTitle('Accompagnement scolaire personnalisé - Xylou');
    await expect(page.locator('meta[name="description"]')).toHaveAttribute(
        'content',
        /du CP à la 3e/,
    );
    await expect(page.getByRole('heading', { level: 1 })).toContainText('son monde');
    await expect(
        page.getByRole('heading', { name: 'Même exercice. Un autre monde.' }),
    ).toBeVisible();
    await expect(
        page.getByText('Témoignages fictifs, fournis à titre d’illustration.'),
    ).toBeVisible();
    await expectNoSeriousViolations(page);

    await page.getByRole('link', { name: 'Créer un profil' }).first().click();
    await expect(page).toHaveURL(/\/register$/);
});

test('la FAQ se déplie au clavier', async ({ page }) => {
    await page.goto('/#faq');
    const question = page.getByText('Dès quel âge ?');

    await question.focus();
    await page.keyboard.press('Enter');

    await expect(page.getByText('Du CP à la 3e.', { exact: false })).toBeVisible();
});

test('les pages légales sont accessibles depuis le pied de page', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('contentinfo').getByRole('link', { name: 'Confidentialité' }).click();

    await expect(page).toHaveURL(/\/confidentialite$/);
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(
        'Politique de confidentialité',
    );
    await expect(
        page.getByRole('heading', { name: 'Utilisation de l’intelligence artificielle' }),
    ).toBeVisible();
    await expectNoSeriousViolations(page);
});
