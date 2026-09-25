import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XSegmented from '../XSegmented.vue';

const options = [
    { value: 'discovery', label: 'Découverte' },
    { value: 'practice', label: 'Entraînement' },
    { value: 'challenge', label: 'Défi' },
];

function mountSegmented(modelValue = 'practice') {
    return mount(XSegmented, {
        props: { label: 'Difficulté', options, modelValue },
        attachTo: document.body,
    });
}

describe('XSegmented', () => {
    it('est un groupe de boutons radio nommé, un seul tabulable', async () => {
        const wrapper = mountSegmented();
        const radios = wrapper.findAll('[role="radio"]');

        expect(wrapper.get('[role="radiogroup"]').attributes('aria-label')).toBe('Difficulté');
        expect(radios.map((r) => r.attributes('aria-checked'))).toEqual(['false', 'true', 'false']);
        expect(radios.map((r) => r.attributes('tabindex'))).toEqual(['-1', '0', '-1']);
        await expectNoAxeViolations(wrapper.element);
    });

    it('sélectionne au clic', async () => {
        const wrapper = mountSegmented();

        await wrapper.findAll('[role="radio"]')[2].trigger('click');

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['challenge']);
    });

    it('se pilote aux flèches, en boucle, et déplace le focus', async () => {
        const wrapper = mountSegmented('challenge');
        const radios = wrapper.findAll('[role="radio"]');

        await radios[2].trigger('keydown', { key: 'ArrowRight' });
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['discovery']);
        expect(document.activeElement).toBe(radios[0].element);

        await radios[0].trigger('keydown', { key: 'ArrowLeft' });
        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['challenge']);
    });

    it('rend la première option tabulable si aucune ne correspond', () => {
        const wrapper = mountSegmented('inconnue');

        expect(wrapper.findAll('[role="radio"]')[0].attributes('tabindex')).toBe('0');
    });
});
