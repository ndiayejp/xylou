import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XTextarea from '../XTextarea.vue';

describe('XTextarea', () => {
    it('est accessible et fonctionne avec v-model', async () => {
        const wrapper = mount(XTextarea, {
            props: { label: 'Ce que vous observez', optionalLabel: '(facultatif)', modelValue: '' },
            attachTo: document.body,
        });

        await wrapper.get('textarea').setValue('Se décourage vite.');

        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Se décourage vite.']);
        expect(wrapper.get('textarea').attributes('rows')).toBe('4');
        await expectNoAxeViolations(wrapper.element);
    });

    it('signale une erreur', () => {
        const wrapper = mount(XTextarea, { props: { label: 'Consigne', error: 'Trop long.' } });

        expect(wrapper.get('textarea').attributes('aria-invalid')).toBe('true');
        expect(wrapper.text()).toContain('Trop long.');
    });
});
