import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XMascot from '../XMascot.vue';
import XUniverseScene from '../XUniverseScene.vue';

describe('XMascot', () => {
    it('décorative par défaut', async () => {
        const wrapper = mount(XMascot, { attachTo: document.body });

        expect(wrapper.attributes('aria-hidden')).toBe('true');
        expect(wrapper.attributes('role')).toBeUndefined();
        expect(wrapper.attributes('width')).toBe('96');
        await expectNoAxeViolations(wrapper.element);
    });

    it('annoncée comme une image quand un label est fourni', async () => {
        const wrapper = mount(XMascot, {
            props: { label: 'Xy te félicite', mood: 'cheer' },
            attachTo: document.body,
        });

        expect(wrapper.attributes()).toMatchObject({ role: 'img', 'aria-label': 'Xy te félicite' });
        expect(wrapper.attributes('aria-hidden')).toBeUndefined();
        await expectNoAxeViolations(wrapper.element);
    });

    it.each([
        ['happy', 10, 'M38 57q7 7 14 0'],
        ['think', 12, 'M40 60q5 -3 10 0'],
        ['cheer', 12, 'M36 56q9 12 18 0z'],
    ] as const)('humeur %s : décor et bouche propres', (mood, shapes, mouth) => {
        const wrapper = mount(XMascot, { props: { mood } });

        expect(wrapper.attributes('data-mood')).toBe(mood);
        expect(wrapper.findAll('path, circle, ellipse')).toHaveLength(shapes);
        expect(wrapper.findAll('path').at(-1)?.attributes('d')).toBe(mouth);
    });

    it('taille et respiration optionnelles', () => {
        const wrapper = mount(XMascot, { props: { size: 64, animated: true } });

        expect(wrapper.attributes('height')).toBe('64');
        expect(wrapper.classes()).toContain('animate-breathe');
    });
});

describe('XUniverseScene', () => {
    it.each(['space', 'football', 'forest'] as const)(
        '%s : même cadre, illustration propre',
        (universe) => {
            const wrapper = mount(XUniverseScene, { props: { universe } });

            expect(wrapper.attributes()).toMatchObject({
                viewBox: '0 0 400 260',
                preserveAspectRatio: 'xMidYMid slice',
                'data-universe': universe,
                'aria-hidden': 'true',
            });
            expect(wrapper.findAll(':scope > g')).toHaveLength(1);
        },
    );

    it('les trois scènes sont différentes', () => {
        const html = (['space', 'football', 'forest'] as const).map((universe) =>
            mount(XUniverseScene, { props: { universe } }).get('g').html(),
        );

        expect(new Set(html).size).toBe(3);
    });

    it('porte le texte alternatif fourni par la page', async () => {
        const wrapper = mount(XUniverseScene, {
            props: { universe: 'forest', label: 'Un renard traverse une forêt au soleil' },
            attachTo: document.body,
        });

        expect(wrapper.attributes()).toMatchObject({
            role: 'img',
            'aria-label': 'Un renard traverse une forêt au soleil',
        });
        await expectNoAxeViolations(wrapper.element);
    });
});
