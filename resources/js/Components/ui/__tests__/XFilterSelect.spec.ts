import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XFilterSelect from '../XFilterSelect.vue';

const options = [
    { value: 'maths', label: 'Maths' },
    { value: 'french', label: 'Français' },
];

function mountFilter(modelValue: string | null = null) {
    return mount(XFilterSelect, {
        props: {
            label: 'Matière',
            allLabel: 'Toutes',
            summary: 'Matière : Maths',
            options,
            modelValue,
        },
        attachTo: document.body,
    });
}

describe('XFilterSelect', () => {
    it('est un select nommé, avec une option « tout »', async () => {
        const wrapper = mountFilter();
        const select = wrapper.get('select');

        expect(select.attributes('aria-label')).toBe('Matière');
        expect(select.findAll('option').map((o) => o.text())).toEqual([
            'Toutes',
            'Maths',
            'Français',
        ]);
        expect(wrapper.get('span').text()).toBe('Matière');
        await expectNoAxeViolations(wrapper.element);
    });

    it('affiche le résumé et se met en avant quand une valeur est choisie', () => {
        const wrapper = mountFilter('maths');

        expect(wrapper.get('span').text()).toBe('Matière : Maths');
        expect(wrapper.get('span').classes()).toContain('bg-tint');
    });

    it('émet la valeur choisie, et null pour « tout »', async () => {
        const wrapper = mountFilter('maths');

        await wrapper.get('select').setValue('french');
        await wrapper.get('select').setValue('');

        expect(wrapper.emitted('update:modelValue')).toEqual([['french'], [null]]);
    });
});
