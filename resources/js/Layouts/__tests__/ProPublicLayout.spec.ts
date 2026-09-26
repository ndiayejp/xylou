import { FileText } from '@lucide/vue';
import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import ProLayout from '../ProLayout.vue';
import PublicLayout from '../PublicLayout.vue';
import { adultNav, RESPONSIVE_DUPLICATES } from './fixtures';

afterEach(() => {
    document.body.innerHTML = '';
});

describe('ProLayout', () => {
    function mountLayout() {
        return mount(ProLayout, {
            props: {
                nav: adultNav,
                homeHref: '/pro',
                user: { name: 'Claire Martin' },
                userDetails: 'Orthopédagogue · 6 profils partagés',
                notificationsHref: '/notifications',
                action: {
                    label: 'nav.adult.newReport',
                    href: '/pro/bilans/nouveau',
                    icon: FileText,
                },
            },
            slots: { header: '<h1>Bonjour Claire</h1>' },
            attachTo: document.body,
        });
    }

    it('barre latérale sobre : badge Pro, identité, action sombre', async () => {
        const wrapper = mountLayout();
        const sidebar = wrapper.get('nav');

        expect(sidebar.text()).toContain('Pro');
        expect(sidebar.text()).toContain('Orthopédagogue · 6 profils partagés');
        expect(sidebar.get('a[href="/pro/bilans/nouveau"]').classes()).toContain('bg-text');
        await expectNoAxeViolations(wrapper.element, RESPONSIVE_DUPLICATES);
    });

    it('menu déroulant sur petit écran', async () => {
        const wrapper = mountLayout();
        const toggle = wrapper.get('button[aria-label="Ouvrir le menu"]');

        await toggle.trigger('click');

        expect(toggle.attributes('aria-label')).toBe('Fermer le menu');
        expect(wrapper.findAll('nav')).toHaveLength(2);
    });
});

describe('PublicLayout', () => {
    function mountLayout() {
        return mount(PublicLayout, {
            props: {
                homeHref: '/',
                sections: [
                    { label: 'nav.public.how', href: '#comment' },
                    { label: 'nav.public.faq', href: '#faq' },
                ],
                loginHref: '/connexion',
                registerHref: '/inscription',
                footer: [
                    {
                        title: 'nav.public.trust',
                        items: [{ label: 'nav.public.privacy', href: '/confidentialite' }],
                    },
                ],
                legalHref: '/mentions-legales',
            },
            slots: { default: '<h1>Xylou</h1>' },
            attachTo: document.body,
        });
    }

    it('en-tête, sections, connexion et pied de page traduits', async () => {
        const wrapper = mountLayout();

        expect(wrapper.get('nav[aria-label="Sections"]').text()).toContain('Comment ça marche');
        expect(wrapper.get('a[href="/connexion"]').text()).toBe('Se connecter');
        expect(wrapper.get('a[href="/inscription"]').text()).toBe('Créer un profil');
        expect(wrapper.get('footer nav').attributes('aria-label')).toBe('Confiance');
        expect(wrapper.get('footer').text()).toContain(`© ${new Date().getFullYear()} Xylou`);
        await expectNoAxeViolations(wrapper.element, RESPONSIVE_DUPLICATES);
    });

    it('menu mobile qui se replie après un choix', async () => {
        const wrapper = mountLayout();

        await wrapper.get('button[aria-controls]').trigger('click');
        const faq = wrapper.findAll('a[href="#faq"]').at(-1);
        await faq?.trigger('click');

        expect(wrapper.get('button[aria-controls]').attributes('aria-expanded')).toBe('false');
    });
});
