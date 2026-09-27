import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

// Bibliothèque de démonstration du DatabaseSeeder (parent@example.com, niveau CE2).
async function openLibrary(page: Page): Promise<void> {
    await page.goto('/login');
    await page.locator('input[type="email"]').fill('parent@example.com');
    await page.locator('input[type="password"]').fill('password');
    await page.locator('input[type="password"]').press('Enter');
    await expect(page).toHaveURL(/\/parent$/);
    // Sans niveau : la liste ne dépend pas de l'enfant resté sélectionné.
    await page.goto('/parent/bibliotheque?grade=');
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Bibliothèque');
}

async function expectNoSeriousViolations(page: Page): Promise<void> {
    const { violations } = await new AxeBuilder({ page }).analyze();

    expect(violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')).toEqual([]);
}

test('la bibliothèque se filtre, les archives restent à part', async ({ page }) => {
    await openLibrary(page);

    await expect(page.getByRole('heading', { name: 'Mission Mars' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Tirs au but multiplicatifs' })).toHaveCount(0);
    await expectNoSeriousViolations(page);

    await page.getByLabel('Matière').selectOption('french');
    await expect(page).toHaveURL(/subject=french/);
    await expect(
        page.getByRole('heading', { name: 'Le journal de bord du capitaine' }),
    ).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Mission Mars' })).toHaveCount(0);

    await page.getByRole('button', { name: 'Effacer' }).first().click();
    await page.getByLabel('Statut').selectOption('archived');
    await expect(page.getByRole('heading', { name: 'Tirs au but multiplicatifs' })).toBeVisible();
    await expectNoSeriousViolations(page);
});

test('supprimer une activité puis annuler depuis le toast', async ({ page }) => {
    await openLibrary(page);

    await page.getByRole('button', { name: 'Actions sur « La fusée des tables »' }).click();
    await expect(page.getByRole('menu')).toBeVisible();
    await expectNoSeriousViolations(page);
    await page.getByRole('menuitem', { name: 'Supprimer' }).click();

    await expect(page.getByText('Activité supprimée')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'La fusée des tables' })).toHaveCount(0);

    await page.getByRole('button', { name: 'Annuler' }).click();
    await expect(page.getByText('Activité restaurée')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'La fusée des tables' })).toBeVisible();
});

test('le menu d’une carte se pilote au clavier', async ({ page }) => {
    await openLibrary(page);

    const trigger = page.getByRole('button', { name: 'Actions sur « Mission Mars »' });
    await trigger.focus();
    await page.keyboard.press('ArrowDown');
    await expect(page.getByRole('menuitem', { name: 'Modifier' })).toBeFocused();
    await page.keyboard.press('End');
    await expect(page.getByRole('menuitem', { name: 'Supprimer' })).toBeFocused();
    await page.keyboard.press('Escape');
    await expect(page.getByRole('menu')).toHaveCount(0);
    await expect(trigger).toBeFocused();
});

test('une phrase devient des filtres, un mot mal écrit est retrouvé', async ({ page }) => {
    await openLibrary(page);
    const search = page.getByRole('searchbox', { name: 'Recherche intelligente' });

    await search.fill('problèmes courts avec des animaux');
    await search.press('Enter');
    await expect(page).toHaveURL(/subject=maths/);
    await expect(page).toHaveURL(/duration=short/);
    await expect(page).toHaveURL(/universe=forest/);
    await expect(page.getByRole('status').filter({ hasText: 'Compris comme' })).toHaveText(
        'Compris comme « Mathématiques · Courte (10 min max) · Forêt »',
    );
    await expect(page.getByRole('heading', { name: 'Le renard compte ses pas' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Mission Mars' })).toHaveCount(0);
    await expectNoSeriousViolations(page);

    // Meilisearch tolère la faute de frappe.
    await page.getByRole('button', { name: 'Effacer' }).first().click();
    await search.fill('capitain');
    await search.press('Enter');
    await expect(page.getByText('1 résultat pour « capitain »')).toBeVisible();
    await expect(
        page.getByRole('heading', { name: 'Le journal de bord du capitaine' }),
    ).toBeVisible();
});

test('un chargement en échec propose « Réessayer », une action en échec le dit', async ({
    page,
}) => {
    await openLibrary(page);

    // Panne simulée du serveur sur le filtre « Français ».
    const failing = /\/parent\/bibliotheque\?.*subject=french/;
    await page.route(failing, (route) =>
        route.fulfill({ status: 500, contentType: 'text/html', body: '<h1>Erreur</h1>' }),
    );
    await page.getByLabel('Matière').selectOption('french');

    const failure = page
        .getByRole('alert')
        .filter({ hasText: 'Impossible de charger les activités' });
    await expect(failure).toBeVisible();
    await expectNoSeriousViolations(page);

    await page.unroute(failing);
    await page.getByRole('button', { name: 'Réessayer' }).click();
    await expect(failure).toHaveCount(0);
    await expect(
        page.getByRole('heading', { name: 'Le journal de bord du capitaine' }),
    ).toBeVisible();

    await page.route(/\/activites\/.+\/dupliquer/, (route) =>
        route.fulfill({ status: 500, body: '' }),
    );
    await page
        .getByRole('button', { name: 'Actions sur « Le journal de bord du capitaine »' })
        .click();
    await page.getByRole('menuitem', { name: 'Dupliquer' }).click();
    await expect(page.getByText('Cette action n’a pas abouti. Réessayez.')).toBeVisible();
});
