import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import OnboardingLayout from '../OnboardingLayout.vue';

function mountLayout(step = 3) {
    return mount(OnboardingLayout, {
        props: {
            step,
            title: 'Découvrons l’univers de Lucas',
            description: 'Ses passions serviront de décor.',
            homeHref: '/',
            backHref: '/onboarding/1/enfant',
        },
        slots: { default: '<p>Contenu</p>', footer: '<button type="button">Continuer</button>' },
        attachTo: document.body,
    });
}

describe('OnboardingLayout', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('titre, progression et étape courante', async () => {
        const wrapper = mountLayout();

        expect(wrapper.get('h1').text()).toBe('Découvrons l’univers de Lucas');
        expect(wrapper.get('[role="progressbar"]').attributes('aria-valuetext')).toBe(
            'Étape 3 sur 7',
        );
        expect(wrapper.get('[aria-current="step"]').text()).toContain('Son univers');
        await expectNoAxeViolations(wrapper.element);
    });

    it('les étapes passées sont annoncées comme terminées', () => {
        const items = mountLayout(4).findAll('ol li');

        expect(items.slice(0, 3).map((item) => item.text())).toEqual([
            'Votre compte (terminée)',
            'Profil de l’enfant (terminée)',
            'Son univers (terminée)',
        ]);
        expect(items[4]?.text()).toBe('5 Difficultés');
    });

    it('lien de retour et pied de page d’actions', () => {
        const wrapper = mountLayout();

        expect(wrapper.get('a[aria-label="Retour"]').attributes('href')).toBe(
            '/onboarding/1/enfant',
        );
        expect(wrapper.get('footer').text()).toBe('Continuer');
    });
});
