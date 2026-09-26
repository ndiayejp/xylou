import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XProgressBar from '../XProgressBar.vue';

describe('XProgressBar', () => {
    it('expose une barre de progression nommée avec sa valeur textuelle', async () => {
        const wrapper = mount(XProgressBar, {
            props: { value: 80, label: 'Fractions', valueText: '80 %' },
            attachTo: document.body,
        });
        const bar = wrapper.get('[role="progressbar"]');

        expect(bar.attributes()).toMatchObject({
            'aria-label': 'Fractions',
            'aria-valuenow': '80',
            'aria-valuemin': '0',
            'aria-valuemax': '100',
            'aria-valuetext': '80 %',
        });
        expect(wrapper.text()).toBe('80 %');
        expect(bar.get('div').attributes('style')).toContain('width: 80%');
        await expectNoAxeViolations(wrapper.element);
    });

    it('calcule la largeur sur un maximum personnalisé et borne la valeur', () => {
        const wrapper = mount(XProgressBar, {
            props: { value: 7, max: 5, label: 'Mission', valueText: '5/5' },
        });
        const bar = wrapper.get('[role="progressbar"]');

        expect(bar.attributes('aria-valuenow')).toBe('5');
        expect(bar.get('div').attributes('style')).toContain('width: 100%');
    });

    it.each([
        ['primary', 'bg-primary'],
        ['consolidate', 'bg-mastery-consolidate-bar'],
        ['mastered', 'bg-mastery-mastered-bar'],
    ] as const)('ton %s', (tone, expected) => {
        const wrapper = mount(XProgressBar, {
            props: { value: 60, tone, label: 'Compétence', valueText: '60 %' },
        });

        expect(wrapper.get('[role="progressbar"] div').classes()).toContain(expected);
    });

    it('la taille enfant affiche la valeur à 18 px au moins', () => {
        const wrapper = mount(XProgressBar, {
            props: { value: 3, max: 5, size: 'kid', label: 'Le renard compte', valueText: '3/5' },
        });

        expect(wrapper.get('span').classes()).toEqual(
            expect.arrayContaining(['font-kid', 'text-[18px]']),
        );
    });

    it('peut masquer la valeur visible, qui reste annoncée', () => {
        const wrapper = mount(XProgressBar, {
            props: { value: 40, label: 'Lecture', valueText: '40 %', hideValue: true },
        });

        expect(wrapper.find('span').exists()).toBe(false);
        expect(wrapper.get('[role="progressbar"]').attributes('aria-valuetext')).toBe('40 %');
    });
});
