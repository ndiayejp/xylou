import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import KidLayout from '../KidLayout.vue';
import { kidNav, RESPONSIVE_DUPLICATES } from './fixtures';

function mountLayout() {
    return mount(KidLayout, {
        props: { nav: kidNav, homeHref: '/enfant' },
        slots: { default: '<h1>Bonjour Emma</h1>' },
        attachTo: document.body,
    });
}

describe('KidLayout', () => {
    it('rail tablette et barre mobile avec les mêmes liens traduits', async () => {
        const wrapper = mountLayout();
        const [rail, bar] = wrapper.findAll('nav');

        expect(rail?.attributes('aria-label')).toBe('Navigation principale');
        expect(rail?.findAll('li a').map((link) => link.text())).toEqual([
            'Accueil',
            'Mes activités',
            'Ma progression',
            'Mes réussites',
            'Profil',
        ]);
        expect(bar?.findAll('a').map((link) => link.attributes('href'))).toEqual(
            kidNav.map((item) => item.href),
        );
        expect(wrapper.get('main#contenu').text()).toBe('Bonjour Emma');
        await expectNoAxeViolations(wrapper.element, RESPONSIVE_DUPLICATES);
        wrapper.unmount();
    });

    it('signale la page courante', () => {
        const wrapper = mountLayout();

        expect(wrapper.findAll('a[aria-current="page"]').map((link) => link.text())).toEqual([
            'Accueil',
            'Accueil',
        ]);
    });

    it('aucun libellé sous 18 px ; sur mobile, les autres onglets restent annoncés', () => {
        const wrapper = mountLayout();
        const [rail, bar] = wrapper.findAll('nav');

        rail?.findAll('li a').forEach((link) => expect(link.classes()).toContain('text-[18px]'));
        bar?.findAll('a').forEach((link) => {
            expect(link.classes()).toEqual(
                expect.arrayContaining(['text-[18px]', 'min-h-kid-touch']),
            );
        });
        expect(bar?.findAll('span.sr-only').map((label) => label.text())).toEqual([
            'Mes activités',
            'Ma progression',
            'Mes réussites',
            'Profil',
        ]);
    });

    it('propose un lien d’évitement vers le contenu', () => {
        const skip = mountLayout().get('a[href="#contenu"]');

        expect(skip.text()).toBe('Aller au contenu');
    });
});

describe('KidLayout : retour à l’espace parent', () => {
    it('affiche un lien « Espace parent » à 18 px quand exitHref est fourni', () => {
        const wrapper = mount(KidLayout, {
            props: { nav: kidNav, homeHref: '/enfant', exitHref: '/enfant/sortie' },
        });
        const link = wrapper.get('a[href="/enfant/sortie"]');

        expect(link.text()).toBe('Espace parent');
        expect(link.classes()).toContain('text-[18px]');
        expect(link.get('svg').attributes('aria-hidden')).toBe('true');
    });

    it('sans exitHref, pas de lien', () => {
        const wrapper = mount(KidLayout, { props: { nav: kidNav, homeHref: '/enfant' } });

        expect(wrapper.find('a[href="/enfant/sortie"]').exists()).toBe(false);
    });
});
