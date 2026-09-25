import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XToggle from '../XToggle.vue';

describe('XToggle', () => {
    it('est un interrupteur nommé par son libellé, décrit par son aide', async () => {
        const wrapper = mount(XToggle, {
            props: { label: 'Rappel doux', hint: 'Une fois par jour', modelValue: true },
            attachTo: document.body,
        });
        const button = wrapper.get('[role="switch"]');

        expect(button.attributes('aria-checked')).toBe('true');
        expect(wrapper.get('label').text()).toBe('Rappel doux');
        expect(wrapper.get(`#${button.attributes('aria-describedby')}`).text()).toBe(
            'Une fois par jour',
        );
        await expectNoAxeViolations(wrapper.element);
    });

    it('bascule au clic, sur le bouton comme sur le libellé', async () => {
        const wrapper = mount(XToggle, {
            props: { label: 'Mode calme', modelValue: false },
            attachTo: document.body,
        });

        await wrapper.get('[role="switch"]').trigger('click');
        expect(wrapper.emitted('update:modelValue')).toEqual([[true]]);

        (wrapper.get('label > span').element as HTMLElement).click();
        expect(wrapper.emitted('update:modelValue')).toHaveLength(2);
    });

    it('ne bascule pas désactivé', async () => {
        const wrapper = mount(XToggle, { props: { label: 'Mode calme', disabled: true } });

        await wrapper.get('[role="switch"]').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });
});
