import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XAvatar from '../XAvatar.vue';

describe('XAvatar', () => {
    it("affiche l'initiale et porte le prénom comme nom accessible", async () => {
        const wrapper = mount(XAvatar, { props: { name: 'émilie' }, attachTo: document.body });

        expect(wrapper.text()).toBe('É');
        expect(wrapper.attributes('role')).toBe('img');
        expect(wrapper.attributes('aria-label')).toBe('émilie');
        await expectNoAxeViolations(wrapper.element);
    });

    it('décoratif quand le prénom est déjà écrit à côté', () => {
        const wrapper = mount(XAvatar, { props: { name: 'Emma', decorative: true } });

        expect(wrapper.attributes('aria-hidden')).toBe('true');
        expect(wrapper.attributes('role')).toBeUndefined();
        expect(wrapper.attributes('aria-label')).toBeUndefined();
    });

    it('garde toujours la même couleur pour le même prénom', () => {
        const first = mount(XAvatar, { props: { name: 'Lucas' } });
        const second = mount(XAvatar, { props: { name: 'Lucas' } });
        const color = first.classes().find((c) => c.startsWith('bg-avatar-'));

        expect(color).toBeDefined();
        expect(second.classes()).toContain(color);
    });

    it('accepte une couleur et une taille imposées', () => {
        const wrapper = mount(XAvatar, { props: { name: 'Emma', color: 'teal', size: 'lg' } });

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['bg-avatar-teal', 'size-14']));
    });
});
