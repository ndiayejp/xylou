import { mount } from '@vue/test-utils';
import { Library, Trash2 } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XCallout from '../XCallout.vue';
import XEmptyState from '../XEmptyState.vue';
import XSkeleton from '../XSkeleton.vue';
import XToast from '../XToast.vue';

describe('XCallout', () => {
    it('affiche titre, texte et une icône décorative', async () => {
        const wrapper = mount(XCallout, {
            props: { tone: 'ai', title: 'Transparence IA' },
            slots: { default: 'Relisez-la avant de l’envoyer.' },
            attachTo: document.body,
        });

        expect(wrapper.get('p').text()).toBe('Transparence IA');
        expect(wrapper.text()).toContain('Relisez-la avant de l’envoyer.');
        expect(wrapper.get('svg').attributes('aria-hidden')).toBe('true');
        await expectNoAxeViolations(wrapper.element);
    });

    it.each([
        ['ai', 'bg-tint'],
        ['info', 'bg-info-soft'],
        ['warn', 'bg-accent-soft'],
        ['ok', 'bg-success-soft'],
        ['err', 'bg-danger-soft'],
    ] as const)('ton %s', (tone, expected) => {
        const wrapper = mount(XCallout, { props: { tone }, slots: { default: 'Message' } });

        expect(wrapper.classes()).toContain(expected);
        expect(wrapper.find('p').exists()).toBe(false);
    });
});

describe('XToast', () => {
    it('annonce son message et propose une action', async () => {
        const wrapper = mount(XToast, {
            props: { icon: Trash2, actionLabel: 'Annuler' },
            slots: { default: 'Activité supprimée' },
            attachTo: document.body,
        });

        expect(wrapper.attributes('role')).toBe('status');
        expect(wrapper.classes()).toContain('bg-text');

        await wrapper.get('button').trigger('click');

        expect(wrapper.emitted('action')).toHaveLength(1);
        await expectNoAxeViolations(wrapper.element);
    });

    it('variante claire, sans action', () => {
        const wrapper = mount(XToast, {
            props: { variant: 'light' },
            slots: { default: 'Hors ligne — tes réponses sont gardées' },
        });

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['bg-surface', 'shadow-rest']));
        expect(wrapper.find('button').exists()).toBe(false);
    });
});

describe('XEmptyState', () => {
    it('titre, description, tuile d’icône et actions', async () => {
        const wrapper = mount(XEmptyState, {
            props: {
                title: 'Votre bibliothèque est vide',
                description: 'Les activités apparaîtront ici.',
                icon: Library,
                tone: 'blue',
            },
            slots: { actions: '<button type="button">Générer une activité</button>' },
            attachTo: document.body,
        });

        expect(wrapper.get('h2').text()).toBe('Votre bibliothèque est vide');
        expect(wrapper.get('span[aria-hidden="true"]').classes()).toContain('bg-info-soft');
        expect(wrapper.get('button').text()).toBe('Générer une activité');
        await expectNoAxeViolations(wrapper.element);
    });

    it('niveau de titre configurable et illustration libre', () => {
        const wrapper = mount(XEmptyState, {
            props: { title: 'Bienvenue, Emma !', headingTag: 'h3' },
            slots: { illustration: '<svg data-test="mascotte" />' },
        });

        expect(wrapper.find('h3').exists()).toBe(true);
        expect(wrapper.find('[data-test="mascotte"]').exists()).toBe(true);
    });

    it('la taille enfant ne descend pas sous 18 px', () => {
        const wrapper = mount(XEmptyState, {
            props: {
                title: 'On reprend ?',
                description: 'Tes réponses sont gardées.',
                size: 'kid',
            },
        });

        expect(wrapper.classes()).toContain('font-kid');
        expect(wrapper.get('p').classes()).toContain('text-[18px]');
    });
});

describe('XSkeleton', () => {
    it.each([
        ['text', 'h-3'],
        ['block', 'h-[110px]'],
        ['circle', 'rounded-full'],
    ] as const)('forme %s, décorative', (shape, expected) => {
        const wrapper = mount(XSkeleton, { props: { shape } });

        expect(wrapper.classes()).toEqual(expect.arrayContaining([expected, 'animate-shimmer']));
        expect(wrapper.attributes('aria-hidden')).toBe('true');
    });
});
