import { mount } from '@vue/test-utils';
import { Mail } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XInput from '../XInput.vue';

describe('XInput', () => {
    it('relie le libellé au champ et reste accessible', async () => {
        const wrapper = mount(XInput, {
            props: { label: 'Prénom de l’enfant' },
            attachTo: document.body,
        });
        const input = wrapper.get('input');

        expect(wrapper.get('label').attributes('for')).toBe(input.attributes('id'));
        await expectNoAxeViolations(wrapper.element);
    });

    it('fonctionne avec v-model', async () => {
        const wrapper = mount(XInput, { props: { label: 'Prénom', modelValue: 'Emma' } });

        expect(wrapper.get('input').element.value).toBe('Emma');
        await wrapper.get('input').setValue('Lucas');
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['Lucas']);
    });

    it("affiche l'aide et la lie au champ", () => {
        const wrapper = mount(XInput, {
            props: { label: 'Rechercher', hint: 'Essayez « fractions »' },
        });
        const describedBy = wrapper.get('input').attributes('aria-describedby');

        expect(wrapper.get(`#${describedBy}`).text()).toBe('Essayez « fractions »');
    });

    it("en erreur : message lié, aria-invalid, bordure rouge, l'aide est remplacée", async () => {
        const wrapper = mount(XInput, {
            props: {
                label: 'E-mail',
                type: 'email',
                hint: 'Aide',
                error: 'Il manque la fin de l’adresse.',
                icon: Mail,
            },
            attachTo: document.body,
        });
        const input = wrapper.get('input');

        expect(input.attributes('aria-invalid')).toBe('true');
        expect(input.classes()).toContain('border-danger');
        expect(wrapper.get(`#${input.attributes('aria-describedby')}`).text()).toBe(
            'Il manque la fin de l’adresse.',
        );
        expect(wrapper.text()).not.toContain('Aide');
        await expectNoAxeViolations(wrapper.element);
    });

    it('peut masquer visuellement son libellé sans le retirer', () => {
        const wrapper = mount(XInput, { props: { label: 'Rechercher', hideLabel: true } });

        expect(wrapper.get('label').classes()).toContain('sr-only');
    });

    it('affiche la mention facultatif et transmet les attributs au champ', () => {
        const wrapper = mount(XInput, {
            props: { label: 'Surnom', optionalLabel: '(facultatif)' },
            attrs: { autocomplete: 'nickname', required: true },
        });

        expect(wrapper.get('label').text()).toContain('(facultatif)');
        expect(wrapper.get('input').attributes('autocomplete')).toBe('nickname');
        expect(wrapper.get('input').attributes('required')).toBeDefined();
    });
});
