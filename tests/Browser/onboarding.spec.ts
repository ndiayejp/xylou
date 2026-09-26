import AxeBuilder from '@axe-core/playwright';
import { expect, test, type Page } from '@playwright/test';

async function expectNoSeriousViolations(page: Page): Promise<void> {
    const { violations } = await new AxeBuilder({ page }).analyze();

    expect(violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')).toEqual([]);
}

// Crée un compte à chaque passage (adresse unique) : l'inscription est le point d'entrée du parcours.
test('onboarding : compte, profil de l’enfant, retour sans perte, jusqu’à la fin', async ({
    page,
}) => {
    await page.goto('/register');
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Créons votre espace parent');
    await expect(page.getByRole('progressbar')).toHaveAttribute('aria-valuetext', 'Étape 1 sur 7');
    await expectNoSeriousViolations(page);

    await page.getByLabel('Votre prénom').fill('Sophie');
    await page.getByLabel('Votre nom').fill('Bernard');
    await page.getByLabel('Adresse e-mail').fill(`parent-${Date.now()}@example.com`);
    await page.getByLabel('Mot de passe').fill('une phrase facile à retenir');
    await expect(page.getByText('Mot de passe solide')).toBeVisible();

    // Sans les consentements, pas de compte.
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(
        page.getByLabel('Je suis le parent ou le responsable légal', { exact: false }),
    ).toHaveAttribute('aria-invalid', 'true');

    // Par sécurité, le mot de passe est effacé après un envoi refusé : on le ressaisit.
    await expect(page.getByLabel('Mot de passe')).toHaveValue('');
    await page.getByLabel('Mot de passe').fill('une phrase facile à retenir');
    await page.getByLabel('Je suis le parent ou le responsable légal', { exact: false }).check();
    await page.getByLabel('J’accepte les', { exact: false }).check();
    await page.getByRole('button', { name: 'Continuer' }).click();

    // Écran 2
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Parlez-nous de votre enfant');
    await expectNoSeriousViolations(page);
    await page.getByLabel('Prénom', { exact: true }).fill('Lucas');
    await page.getByLabel('Âge').selectOption('11');
    await page.getByRole('radio', { name: 'Collège' }).click();
    await page.getByLabel('Classe').selectOption('6e');
    await page.getByRole('button', { name: 'Avatar ballon' }).click();
    await page.getByRole('switch', { name: 'Lecture à voix haute' }).click();
    await page.getByRole('button', { name: 'Continuer' }).click();

    // Écran 3, puis retour : les réponses de l'écran 2 sont là.
    await expect(page.getByRole('progressbar')).toHaveAttribute('aria-valuetext', 'Étape 3 sur 7');
    await page.getByRole('contentinfo').getByRole('link', { name: 'Retour' }).click();
    await expect(page.getByLabel('Prénom', { exact: true })).toHaveValue('Lucas');
    await expect(page.getByLabel('Classe')).toHaveValue('6e');
    await expect(page.getByRole('button', { name: 'Avatar ballon' })).toHaveAttribute(
        'aria-pressed',
        'true',
    );
    await expect(page.getByRole('switch', { name: 'Lecture à voix haute' })).toHaveAttribute(
        'aria-checked',
        'true',
    );

    await page.getByRole('button', { name: 'Continuer' }).click();

    // Écran 3 : univers
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(
        'Découvrons l’univers de Lucas',
    );
    await page.getByRole('button', { name: 'Continuer' }).click();
    await expect(page.getByText('Choisissez au moins une passion', { exact: false })).toBeVisible();
    await page.getByRole('button', { name: 'Football' }).click();
    await page.getByLabel('Autres passions, personnages ou thèmes favoris').fill('Les robots');
    await page.getByLabel('Autres passions, personnages ou thèmes favoris').press('Enter');
    await expect(page.getByText('2 univers choisis', { exact: false })).toBeVisible();
    await expectNoSeriousViolations(page);
    await page.getByRole('button', { name: 'Continuer' }).click();

    // Écran 4 : objectifs, puis retour à l'écran 3 sans perte.
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Quels sont vos objectifs ?');
    await page.getByRole('contentinfo').getByRole('link', { name: 'Retour' }).click();
    await expect(page.getByRole('button', { name: 'Football' })).toHaveAttribute(
        'aria-pressed',
        'true',
    );
    await expect(page.getByText('Les robots')).toBeVisible();
    await page.getByRole('button', { name: 'Continuer' }).click();

    await page.getByRole('button', { name: 'Reprendre confiance' }).click();
    await page.getByRole('button', { name: 'Progresser en maths' }).click();
    await page.getByRole('radio', { name: 'Progresser en maths' }).check();
    await expect(
        page.getByText('Les activités de mathématiques seront proposées en priorité.'),
    ).toBeVisible();
    await expectNoSeriousViolations(page);
    await page.getByRole('button', { name: 'Continuer' }).click();

    for (const step of [5, 6, 7]) {
        await expect(page.getByRole('progressbar')).toHaveAttribute(
            'aria-valuetext',
            `Étape ${step} sur 7`,
        );
        await page.getByRole('button', { name: 'Continuer' }).click();
    }

    // Fin : l'espace parent exige l'adresse vérifiée.
    await expect(page).toHaveURL(/\/verify-email$/);
});
