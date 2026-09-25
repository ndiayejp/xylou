import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import InputError from '../InputError.vue';

describe('InputError', () => {
    it('affiche le message reçu', () => {
        const wrapper = mount(InputError, { props: { message: 'Champ requis' } });

        expect(wrapper.text()).toBe('Champ requis');
        expect(wrapper.isVisible()).toBe(true);
    });

    it('reste masqué sans message', () => {
        const wrapper = mount(InputError, { attachTo: document.body });

        expect(wrapper.isVisible()).toBe(false);
    });
});
