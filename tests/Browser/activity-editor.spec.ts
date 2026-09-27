import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

async function login(page: Page): Promise<void> {
    await page.goto('/login');
    await page.locator('input[type="email"]').fill('parent@example.com');
    await page.locator('input[type="password"]').fill('password');
    await page.locator('input[type="password"]').press('Enter');
    await expect(page).toHaveURL(/\/parent$/);
}

async function expectNoSeriousViolations(page: Page): Promise<void> {
    const { violations } = await new AxeBuilder({ page }).analyze();

    expect(violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')).toEqual([]);
}

test('écrire une activité, la valider, la retrouver dans la bibliothèque', async ({ page }) => {
    // Titre unique : la base locale garde les passages précédents (dans la corbeille).
    const title = `Mission test ${Date.now()}`;
    await login(page);
    await page.goto('/parent/bibliotheque?grade=');
    await page.getByRole('button', { name: 'Nouvelle activité' }).click();

    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Nouvelle activité');
    await expectNoSeriousViolations(page);

    // Valider trop tôt : chaque manque est signalé.
    await page.getByRole('button', { name: 'Enregistrer et valider' }).click();
    await expect(page.getByRole('alert')).toBeVisible();
    await expect(page.getByText('Écrivez l’énoncé de la question.')).toBeVisible();

    await page.getByLabel('Titre').fill(title);
    await page.getByLabel('Niveau').selectOption('ce2');
    await page.getByLabel('Compétence').selectOption({ index: 1 });
    await page
        .getByLabel('Énoncé')
        .fill('La fusée emporte 4 caisses de 6 bouteilles. Combien en tout ?');
    await page.getByLabel('Nombre attendu').fill('24');
    await page.getByLabel('Explication montrée à l’enfant').fill('4 × 6 = 24.');

    await page.getByRole('button', { name: 'Ajouter une question' }).click();
    const second = page.getByRole('listitem').filter({ hasText: 'Question 2' });
    await second.getByLabel('Énoncé').fill('6 × 7 ?');
    await second.getByLabel('Type de réponse').selectOption('single_choice');
    await second.getByLabel('Proposition 1').fill('42');
    await second.getByLabel('Proposition 2').fill('48');
    await second.getByLabel('Bonne réponse').selectOption({ label: '1. 42' });
    await second.getByLabel('Explication montrée à l’enfant').fill('6 × 7 = 42.');
    await expectNoSeriousViolations(page);

    await page.getByRole('button', { name: 'Enregistrer et valider' }).click();
    await expect(page).toHaveURL(/\/parent\/bibliotheque/);
    await expect(page.getByText('Activité validée et rangée dans la bibliothèque')).toBeVisible();

    await page.goto('/parent/bibliotheque?grade=');
    const card = page.getByRole('article').filter({ hasText: title });
    await expect(card.getByText('Validée')).toBeVisible();

    // Modifier : le formulaire reprend les questions ; puis on range l'activité dans la corbeille.
    await card.getByRole('button', { name: `Actions sur « ${title} »` }).click();
    await page.getByRole('menuitem', { name: 'Modifier' }).click();
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Modifier l’activité');
    await expect(page.getByLabel('Nombre attendu')).toHaveValue('24');
    await expect(page.getByRole('button', { name: 'Enregistrer le brouillon' })).toHaveCount(0);
    await page.getByRole('button', { name: 'Enregistrer les modifications' }).click();
    await expect(page.getByText('Modifications enregistrées')).toBeVisible();

    await page.goto('/parent/bibliotheque?grade=');
    await page.getByRole('button', { name: `Actions sur « ${title} »` }).click();
    await page.getByRole('menuitem', { name: 'Supprimer' }).click();
    await expect(page.getByText('Activité supprimée')).toBeVisible();
});
