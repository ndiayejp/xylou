import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import XLogo from '../XLogo.vue';

describe('XLogo', () => {
    it('décoratif : le lien qui l’entoure porte le libellé', () => {
        const wrapper = mount(XLogo);

        expect(wrapper.attributes('aria-hidden')).toBe('true');
        expect(wrapper.text()).toBe('xylou');
        expect(wrapper.get('svg').attributes('width')).toBe('30');
    });

    it('version compacte (symbole seul) et inversée', () => {
        expect(mount(XLogo, { props: { compact: true } }).text()).toBe('');
        expect(
            mount(XLogo, { props: { inverse: true } })
                .get('span span')
                .classes(),
        ).toContain('text-surface');
    });
});
