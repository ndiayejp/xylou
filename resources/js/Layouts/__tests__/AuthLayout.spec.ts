import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import AuthLayout from '../AuthLayout.vue';

describe('AuthLayout', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('titre, description, contenu et pied de page', async () => {
        const wrapper = mount(AuthLayout, {
            props: { title: 'Bon retour sur Xylou', description: 'Connectez-vous.' },
            slots: {
                default: '<form aria-label="Connexion"></form>',
                footer: 'Pas encore de compte ?',
            },
            attachTo: document.body,
        });

        expect(wrapper.get('h1').text()).toBe('Bon retour sur Xylou');
        expect(wrapper.text()).toContain('Connectez-vous.');
        expect(wrapper.get('main#contenu').text()).toContain('Pas encore de compte ?');
        expect(wrapper.get('a[href="/"]').attributes('aria-label')).toBe('Accueil Xylou');
        await expectNoAxeViolations(wrapper.element);
    });

    it('annonce un message de statut', () => {
        const wrapper = mount(AuthLayout, {
            props: { title: 'Mot de passe oublié', status: 'Lien envoyé.' },
        });

        expect(wrapper.get('[role="status"]').text()).toBe('Lien envoyé.');
    });

    it('sans statut, pas de message', () => {
        const wrapper = mount(AuthLayout, {
            props: { title: 'Connexion' },
        });

        expect(wrapper.find('[role="status"]').exists()).toBe(false);
    });
});
