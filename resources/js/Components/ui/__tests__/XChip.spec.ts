import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XChip from '../XChip.vue';

describe('XChip', () => {
    it('est un bouton à bascule accessible', async () => {
        const wrapper = mount(XChip, {
            props: { label: 'Fractions', modelValue: false },
            attachTo: document.body,
        });

        expect(wrapper.attributes('aria-pressed')).toBe('false');
        expect(wrapper.text()).toBe('Fractions');
        await expectNoAxeViolations(wrapper.element);
    });

    it('affiche une coche une fois choisi, un plus sinon', () => {
        const on = mount(XChip, { props: { label: 'Fractions', modelValue: true } });
        const off = mount(XChip, { props: { label: 'Fractions', modelValue: false } });

        expect(on.classes()).toContain('border-primary');
        expect(on.get('svg').classes()).toContain('text-primary-text');
        expect(off.get('svg').classes()).toContain('text-muted');
    });

    it('variante filtre : fond sombre une fois choisi, sans icône', () => {
        const wrapper = mount(XChip, {
            props: { label: 'Sports', variant: 'filter', modelValue: true },
        });

        expect(wrapper.classes()).toContain('bg-text');
        expect(wrapper.find('svg').exists()).toBe(false);
    });

    it('bascule au clic, sauf désactivé', async () => {
        const active = mount(XChip, { props: { label: 'Fractions', modelValue: false } });
        const disabled = mount(XChip, { props: { label: 'Fractions', disabled: true } });

        await active.trigger('click');
        await disabled.trigger('click');

        expect(active.emitted('update:modelValue')).toEqual([[true]]);
        expect(disabled.emitted('update:modelValue')).toBeUndefined();
    });
});
