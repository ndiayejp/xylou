import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XChildSwitcher from '../XChildSwitcher.vue';

const items = [
    { id: 1, name: 'Emma', details: '8 ans · CE2', color: 'coral' as const },
    { id: 2, name: 'Lucas', details: '11 ans · 6e' },
];

describe('XChildSwitcher', () => {
    it('liste les enfants et signale celui qui est choisi', async () => {
        const wrapper = mount(XChildSwitcher, {
            props: { items, label: 'Changer d’enfant', modelValue: 1 },
            attachTo: document.body,
        });
        const buttons = wrapper.findAll('li button');

        expect(buttons).toHaveLength(2);
        expect(buttons[0]?.attributes('aria-pressed')).toBe('true');
        expect(buttons[0]?.text()).toContain('8 ans · CE2');
        expect(buttons[1]?.attributes('aria-pressed')).toBe('false');
        expect(wrapper.get('ul').attributes('aria-labelledby')).toBe(
            wrapper.get('p').attributes('id'),
        );
        await expectNoAxeViolations(wrapper.element);
    });

    it('émet le nouvel enfant choisi', async () => {
        const wrapper = mount(XChildSwitcher, {
            props: { items, label: 'Changer d’enfant', modelValue: 1 },
        });

        await wrapper.findAll('li button')[1]?.trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[2]]);
    });

    it('propose d’ajouter un enfant seulement si un libellé est fourni', async () => {
        const without = mount(XChildSwitcher, { props: { items, label: 'Changer d’enfant' } });
        const withAdd = mount(XChildSwitcher, {
            props: { items, label: 'Changer d’enfant', addLabel: 'Ajouter un enfant' },
        });

        expect(without.findAll('button')).toHaveLength(2);

        const add = withAdd.findAll('button').at(-1);
        await add?.trigger('click');

        expect(add?.text()).toBe('Ajouter un enfant');
        expect(withAdd.emitted('add')).toHaveLength(1);
    });

    it('émet select à chaque choix, même pour l’enfant déjà sélectionné', async () => {
        const wrapper = mount(XChildSwitcher, {
            props: { items, label: 'Changer d’enfant', modelValue: 1 },
        });

        await wrapper.findAll('li button')[0]?.trigger('click');

        expect(wrapper.emitted('select')).toEqual([[1]]);
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });
});
