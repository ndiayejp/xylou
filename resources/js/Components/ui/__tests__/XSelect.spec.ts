import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XSelect from '../XSelect.vue';

const options = [
    { value: 'CE1', label: 'CE1' },
    { value: 'CE2', label: 'CE2' },
    { value: 'CM1', label: 'CM1', disabled: true },
];

describe('XSelect', () => {
    it('affiche les options et le choix courant', async () => {
        const wrapper = mount(XSelect, {
            props: { label: 'Niveau scolaire', options, modelValue: 'CE2' },
            attachTo: document.body,
        });
        const rendered = wrapper.findAll('option');

        expect(rendered.map((o) => o.text())).toEqual(['CE1', 'CE2', 'CM1']);
        expect(wrapper.get('select').element.value).toBe('CE2');
        expect(rendered[2].attributes('disabled')).toBeDefined();
        await expectNoAxeViolations(wrapper.element);
    });

    it('émet la nouvelle valeur', async () => {
        const wrapper = mount(XSelect, { props: { label: 'Niveau', options, modelValue: 'CE2' } });

        await wrapper.get('select').setValue('CE1');

        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['CE1']);
    });

    it('propose un choix vide non sélectionnable', () => {
        const wrapper = mount(XSelect, {
            props: { label: 'Niveau', options, placeholder: 'Choisir un niveau' },
        });
        const first = wrapper.findAll('option')[0];

        expect(first.text()).toBe('Choisir un niveau');
        expect(first.attributes('disabled')).toBeDefined();
    });
});
