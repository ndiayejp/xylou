import { Copy, Trash2 } from '@lucide/vue';
import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XMenu from '../XMenu.vue';

const items = [
    { key: 'duplicate', label: 'Dupliquer', icon: Copy },
    { key: 'archive', label: 'Archiver' },
    { key: 'delete', label: 'Supprimer', icon: Trash2, danger: true },
];

function mountMenu() {
    return mount(XMenu, { props: { label: 'Actions', items }, attachTo: document.body });
}

afterEach(() => {
    document.body.innerHTML = '';
});

describe('XMenu', () => {
    it('est un bouton de menu fermé par défaut', async () => {
        const wrapper = mountMenu();
        const button = wrapper.get('button');

        expect(button.attributes('aria-haspopup')).toBe('menu');
        expect(button.attributes('aria-expanded')).toBe('false');
        expect(wrapper.find('[role="menu"]').exists()).toBe(false);
        await expectNoAxeViolations(wrapper.element);
    });

    it('s’ouvre au clic et donne le focus au premier élément', async () => {
        const wrapper = mountMenu();

        await wrapper.get('button').trigger('click');
        await wrapper.vm.$nextTick();

        const entries = wrapper.findAll('[role="menuitem"]');
        expect(wrapper.get('button').attributes('aria-expanded')).toBe('true');
        expect(entries.map((e) => e.text())).toEqual(['Dupliquer', 'Archiver', 'Supprimer']);
        expect(document.activeElement).toBe(entries[0].element);
        await expectNoAxeViolations(wrapper.element);
    });

    it('se parcourt aux flèches, en boucle, et avec Début / Fin', async () => {
        const wrapper = mountMenu();
        await wrapper.get('button').trigger('click');
        await wrapper.vm.$nextTick();
        const menu = wrapper.get('[role="menu"]');
        const entries = wrapper.findAll('[role="menuitem"]');

        await menu.trigger('keydown', { key: 'ArrowUp' });
        expect(document.activeElement).toBe(entries[2].element);
        await menu.trigger('keydown', { key: 'ArrowDown' });
        expect(document.activeElement).toBe(entries[0].element);
        await menu.trigger('keydown', { key: 'End' });
        expect(document.activeElement).toBe(entries[2].element);
        await menu.trigger('keydown', { key: 'Home' });
        expect(document.activeElement).toBe(entries[0].element);
    });

    it('ouvre sur le dernier élément avec la flèche haut', async () => {
        const wrapper = mountMenu();

        await wrapper.get('button').trigger('keydown', { key: 'ArrowUp' });
        await wrapper.vm.$nextTick();

        expect(document.activeElement).toBe(wrapper.findAll('[role="menuitem"]')[2].element);
    });

    it('émet le choix, se ferme et rend le focus au bouton', async () => {
        const wrapper = mountMenu();
        await wrapper.get('button').trigger('click');
        await wrapper.vm.$nextTick();

        await wrapper.findAll('[role="menuitem"]')[2].trigger('click');

        expect(wrapper.emitted('select')).toEqual([['delete']]);
        expect(wrapper.find('[role="menu"]').exists()).toBe(false);
        expect(document.activeElement).toBe(wrapper.get('button').element);
    });

    it('se ferme avec Échap et au clic à l’extérieur', async () => {
        const wrapper = mountMenu();
        await wrapper.get('button').trigger('click');
        await wrapper.vm.$nextTick();

        await wrapper.get('[role="menu"]').trigger('keydown', { key: 'Escape' });
        expect(wrapper.find('[role="menu"]').exists()).toBe(false);
        expect(document.activeElement).toBe(wrapper.get('button').element);

        await wrapper.get('button').trigger('click');
        document.body.dispatchEvent(new Event('pointerdown', { bubbles: true }));
        await wrapper.vm.$nextTick();
        expect(wrapper.find('[role="menu"]').exists()).toBe(false);
        expect(wrapper.emitted('select')).toBeUndefined();
    });
});
