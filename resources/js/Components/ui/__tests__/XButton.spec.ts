import { mount } from '@vue/test-utils';
import { Check } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XButton from '../XButton.vue';

describe('XButton', () => {
    it('affiche son libellé dans un vrai bouton de type button', async () => {
        const wrapper = mount(XButton, {
            slots: { default: 'Continuer' },
            attachTo: document.body,
        });

        expect(wrapper.element.tagName).toBe('BUTTON');
        expect(wrapper.attributes('type')).toBe('button');
        expect(wrapper.text()).toBe('Continuer');
        await expectNoAxeViolations(wrapper.element);
    });

    it.each([
        ['primary', 'bg-primary'],
        ['secondary', 'border-line'],
        ['soft', 'bg-tint'],
        ['ghost', 'bg-transparent'],
        ['accent', 'bg-accent'],
        ['success', 'bg-success-text'],
        ['danger', 'text-danger'],
    ] as const)('applique la variante %s', (variant, expected) => {
        const wrapper = mount(XButton, { props: { variant }, slots: { default: 'Ok' } });

        expect(wrapper.classes()).toContain(expected);
    });

    it('la taille enfant utilise Nunito et une zone tactile de 64 px', () => {
        const wrapper = mount(XButton, { props: { size: 'kid' }, slots: { default: 'Commencer' } });

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['font-kid', 'min-h-kid-touch']));
    });

    it("affiche l'icône décorative sans l'annoncer", () => {
        const wrapper = mount(XButton, { props: { icon: Check }, slots: { default: 'Approuver' } });

        expect(wrapper.find('svg').attributes('aria-hidden')).toBe('true');
    });

    it('émet click', async () => {
        const wrapper = mount(XButton, { slots: { default: 'Ok' } });

        await wrapper.trigger('click');

        expect(wrapper.emitted('click')).toHaveLength(1);
    });

    it('en chargement : occupé, désactivé, spinner à la place de l’icône', async () => {
        const wrapper = mount(XButton, {
            props: { loading: true, icon: Check },
            slots: { default: 'Génération…' },
        });

        expect(wrapper.attributes('aria-busy')).toBe('true');
        expect(wrapper.attributes('disabled')).toBeDefined();
        expect(wrapper.find('[data-test="spinner"]').exists()).toBe(true);
        expect(wrapper.find('svg').exists()).toBe(false);

        await wrapper.trigger('click');
        expect(wrapper.emitted('click')).toBeUndefined();
    });

    it('désactivé : style neutre et aucun clic', async () => {
        const wrapper = mount(XButton, {
            props: { disabled: true, variant: 'accent' },
            slots: { default: 'Ok' },
        });

        expect(wrapper.classes()).toContain('bg-disabled');
        expect(wrapper.classes()).not.toContain('bg-accent');
        expect(wrapper.attributes('aria-busy')).toBeUndefined();

        await wrapper.trigger('click');
        expect(wrapper.emitted('click')).toBeUndefined();
    });

    it('accepte le type submit', () => {
        const wrapper = mount(XButton, {
            props: { type: 'submit' },
            slots: { default: 'Envoyer' },
        });

        expect(wrapper.attributes('type')).toBe('submit');
    });
});
