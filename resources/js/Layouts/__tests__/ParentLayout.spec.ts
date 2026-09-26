import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import ParentLayout from '../ParentLayout.vue';
import {
    accountLinks,
    adultNav,
    children,
    generateAction,
    RESPONSIVE_DUPLICATES,
} from './fixtures';

function mountLayout(props = {}) {
    return mount(ParentLayout, {
        props: {
            nav: adultNav,
            homeHref: '/parent',
            user: { name: 'Sophie' },
            children,
            currentChildId: 1,
            notificationsHref: '/notifications',
            unreadNotifications: 3,
            action: generateAction,
            helpHref: '/aide',
            accountLinks,
            ...props,
        },
        slots: { header: '<h1>Bonjour Sophie</h1>', default: '<p>Contenu</p>' },
        attachTo: document.body,
    });
}

describe('ParentLayout', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('barre latérale : liens, action principale et aide', async () => {
        const wrapper = mountLayout();
        const sidebar = wrapper.findAll('nav')[0];

        expect(sidebar?.findAll('ul a').map((link) => link.text())).toEqual([
            'Accueil',
            'Enfants',
            'Activités',
            'Progression',
            'Bilans',
            'Bibliothèque',
            'Paramètres',
        ]);
        expect(sidebar?.text()).toContain('Générer une activité');
        expect(sidebar?.get('a[href="/aide"]').text()).toBe('Aide & contact');
        await expectNoAxeViolations(wrapper.element, RESPONSIVE_DUPLICATES);
    });

    it('annonce les notifications non lues', () => {
        const wrapper = mountLayout();

        expect(wrapper.get('a[href="/notifications"]').attributes('aria-label')).toBe(
            'Notifications, 3 non lues',
        );
        expect(
            mountLayout({ unreadNotifications: 0 })
                .get('a[href="/notifications"]')
                .attributes('aria-label'),
        ).toBe('Notifications');
    });

    it('change d’enfant depuis le menu de la barre latérale', async () => {
        const wrapper = mountLayout({ canAddChild: true });
        const trigger = wrapper.get('button[aria-label="Changer d’enfant, actuellement Emma"]');

        await trigger.trigger('click');

        expect(trigger.attributes('aria-expanded')).toBe('true');

        const lucas = wrapper
            .findAll('li button')
            .find((button) => button.text().includes('Lucas'));
        await lucas?.trigger('click');

        expect(wrapper.emitted('switchChild')).toEqual([[2]]);
        expect(trigger.attributes('aria-expanded')).toBe('false');
    });

    it('ferme le menu d’enfant avec Échap', async () => {
        const wrapper = mountLayout();
        const trigger = wrapper.findAll('button[aria-expanded]')[0];

        await trigger?.trigger('click');
        await trigger?.trigger('keydown', { key: 'Escape' });

        expect(trigger?.attributes('aria-expanded')).toBe('false');
    });

    it('mobile : 4 liens et « Plus » qui déplie le reste', async () => {
        const wrapper = mountLayout();
        const bar = wrapper.findAll('nav').at(-1);
        const more = bar?.get('button');

        expect(bar?.findAll('ul > li > a').map((link) => link.text())).toEqual([
            'Accueil',
            'Enfants',
            'Activités',
            'Progression',
        ]);
        expect(more?.text()).toBe('Plus');

        await more?.trigger('click');

        expect(more?.attributes('aria-expanded')).toBe('true');
        expect(bar?.text()).toContain('Bibliothèque');
    });

    it('menu de compte : profil et déconnexion en POST', async () => {
        const wrapper = mountLayout();
        const trigger = wrapper.get('button[aria-label="Mon compte"]');

        await trigger.trigger('click');

        const menu = wrapper.get(`#${trigger.attributes('aria-controls')}`);

        expect(trigger.attributes('aria-expanded')).toBe('true');
        expect(menu.get('a[href="/profile"]').text()).toBe('Mon profil');
        // Déconnexion : un bouton (POST), pas un lien.
        expect(menu.get('button').text()).toBe('Se déconnecter');
    });

    it('sans lien de notifications, pas de cloche', () => {
        const wrapper = mountLayout({ notificationsHref: undefined });

        expect(wrapper.find('a[aria-label^="Notifications"]').exists()).toBe(false);
    });

    it('mobile : le compte est accessible depuis « Plus »', async () => {
        const wrapper = mountLayout();
        const bar = wrapper.findAll('nav').at(-1);

        await bar?.get('button[aria-expanded]').trigger('click');

        expect(bar?.text()).toContain('Se déconnecter');
    });
});
