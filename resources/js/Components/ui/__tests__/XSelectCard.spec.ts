import { mount } from '@vue/test-utils';
import { Eye } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XSelectCard from '../XSelectCard.vue';

describe('XSelectCard', () => {
    it('est un bouton à bascule nommé par son titre et sa description', async () => {
        const wrapper = mount(XSelectCard, {
            props: {
                title: 'Visuel',
                description: 'Schémas, images, couleurs',
                icon: Eye,
                modelValue: false,
            },
            attachTo: document.body,
        });

        expect(wrapper.attributes('aria-pressed')).toBe('false');
        expect(wrapper.text()).toBe('VisuelSchémas, images, couleurs');
        await expectNoAxeViolations(wrapper.element);
    });

    it('sélectionnée : bordure indigo et coche', () => {
        const wrapper = mount(XSelectCard, { props: { title: 'Espace', modelValue: true } });

        expect(wrapper.attributes('aria-pressed')).toBe('true');
        expect(wrapper.classes()).toContain('border-primary');
        expect(wrapper.find('svg').exists()).toBe(true);
    });

    it('bascule au clic, sauf désactivée', async () => {
        const active = mount(XSelectCard, { props: { title: 'Espace', modelValue: false } });
        const disabled = mount(XSelectCard, { props: { title: 'Espace', disabled: true } });

        await active.trigger('click');
        await disabled.trigger('click');

        expect(active.emitted('update:modelValue')).toEqual([[true]]);
        expect(disabled.emitted('update:modelValue')).toBeUndefined();
    });

    it('peut centrer son contenu', () => {
        const wrapper = mount(XSelectCard, { props: { title: 'Problème', align: 'center' } });

        expect(wrapper.classes()).toContain('items-center');
    });
});
