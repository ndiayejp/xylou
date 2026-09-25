import { mount } from '@vue/test-utils';
import { Pencil } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XIconButton from '../XIconButton.vue';

describe('XIconButton', () => {
    it('porte son nom accessible et masque l’icône', async () => {
        const wrapper = mount(XIconButton, {
            props: { icon: Pencil, label: 'Modifier' },
            attachTo: document.body,
        });

        expect(wrapper.attributes('aria-label')).toBe('Modifier');
        expect(wrapper.find('svg').attributes('aria-hidden')).toBe('true');
        await expectNoAxeViolations(wrapper.element);
    });

    it.each([
        ['md', 'size-11'],
        ['kid', 'size-16'],
    ] as const)('taille %s : zone tactile %s', (size, expected) => {
        const wrapper = mount(XIconButton, { props: { icon: Pencil, label: 'Modifier', size } });

        expect(wrapper.classes()).toContain(expected);
    });

    it('expose aria-pressed seulement quand il sert d’interrupteur', () => {
        const plain = mount(XIconButton, { props: { icon: Pencil, label: 'Modifier' } });
        const toggle = mount(XIconButton, {
            props: { icon: Pencil, label: 'Écouter', pressed: true },
        });

        expect(plain.attributes('aria-pressed')).toBeUndefined();
        expect(toggle.attributes('aria-pressed')).toBe('true');
    });

    it('émet click, sauf désactivé', async () => {
        const active = mount(XIconButton, { props: { icon: Pencil, label: 'Modifier' } });
        const disabled = mount(XIconButton, {
            props: { icon: Pencil, label: 'Modifier', disabled: true },
        });

        await active.trigger('click');
        await disabled.trigger('click');

        expect(active.emitted('click')).toHaveLength(1);
        expect(disabled.emitted('click')).toBeUndefined();
    });
});
