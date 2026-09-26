import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XCheckbox from '../XCheckbox.vue';

describe('XCheckbox', () => {
    it('case native reliée à son libellé, qui peut contenir un lien', async () => {
        const wrapper = mount(XCheckbox, {
            slots: { default: 'J’accepte les <a href="/cgu">conditions</a>' },
            attachTo: document.body,
        });
        const input = wrapper.get('input[type="checkbox"]');

        expect(wrapper.get('label').attributes('for')).toBe(input.attributes('id'));
        expect(wrapper.find('label a[href="/cgu"]').exists()).toBe(true);
        await expectNoAxeViolations(wrapper.element);
        wrapper.unmount();
    });

    it('v-model', async () => {
        const wrapper = mount(XCheckbox, {
            props: { modelValue: false },
            slots: { default: 'Oui' },
        });

        await wrapper.get('input').setValue(true);

        expect(wrapper.emitted('update:modelValue')).toEqual([[true]]);
    });

    it('erreur annoncée et reliée à la case', async () => {
        const wrapper = mount(XCheckbox, {
            props: { error: 'Ce consentement est nécessaire.' },
            slots: { default: 'Je suis le parent' },
            attachTo: document.body,
        });
        const input = wrapper.get('input');

        expect(input.attributes('aria-invalid')).toBe('true');
        expect(wrapper.get(`#${input.attributes('aria-describedby')}`).text()).toBe(
            'Ce consentement est nécessaire.',
        );
        await expectNoAxeViolations(wrapper.element);
        wrapper.unmount();
    });
});
