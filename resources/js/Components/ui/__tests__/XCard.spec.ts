import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XCard from '../XCard.vue';

describe('XCard', () => {
    it('carte adulte : bordure fine, ombre au repos et contenu', async () => {
        const wrapper = mount(XCard, {
            props: { as: 'article' },
            slots: { default: '<h3>Problèmes de division</h3>' },
            attachTo: document.body,
        });

        expect(wrapper.element.tagName).toBe('ARTICLE');
        expect(wrapper.classes()).toEqual(
            expect.arrayContaining(['rounded-card', 'shadow-rest', 'p-4']),
        );
        expect(wrapper.text()).toBe('Problèmes de division');
        await expectNoAxeViolations(wrapper.element);
    });

    it('marge large', () => {
        const wrapper = mount(XCard, { props: { padding: 'lg' } });

        expect(wrapper.classes()).toContain('p-5');
    });

    it('carte enfant : grands coins, Nunito et visuel bord à bord', () => {
        const wrapper = mount(XCard, {
            props: { size: 'kid' },
            slots: { media: '<div data-test="media" />', default: 'Le renard compte' },
        });

        expect(wrapper.classes()).toEqual(
            expect.arrayContaining(['rounded-kid-card', 'font-kid', 'overflow-hidden']),
        );
        expect(wrapper.get('[data-test="media"]').element.parentElement?.className).toBe(
            'h-[130px]',
        );
        expect(wrapper.text()).toBe('Le renard compte');
    });

    it('sans visuel, pas de conteneur vide', () => {
        const wrapper = mount(XCard, { slots: { default: 'Texte' } });

        expect(wrapper.element.children).toHaveLength(0);
    });

    it('ne se soulève au survol que si elle est interactive', () => {
        expect(mount(XCard).classes()).not.toContain('hover:shadow-card');
        expect(mount(XCard, { props: { interactive: true } }).classes()).toContain(
            'hover:shadow-card',
        );
    });
});
