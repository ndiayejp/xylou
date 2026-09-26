import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XStepProgress from '../XStepProgress.vue';

describe('XStepProgress', () => {
    it('segments : étapes faites, en cours et à venir', async () => {
        const wrapper = mount(XStepProgress, {
            props: { current: 3, total: 5, label: 'Question 3 sur 5' },
            attachTo: document.body,
        });
        const segments = wrapper.findAll('span');

        expect(segments.map((segment) => segment.classes().at(-1))).toEqual([
            'bg-success',
            'bg-success',
            'bg-primary',
            'bg-disabled',
            'bg-disabled',
        ]);
        expect(wrapper.attributes()).toMatchObject({
            role: 'progressbar',
            'aria-label': 'Question 3 sur 5',
            'aria-valuenow': '3',
            'aria-valuemax': '5',
            'aria-valuetext': 'Question 3 sur 5',
        });
        await expectNoAxeViolations(wrapper.element);
    });

    it('anneau : arc proportionnel et texte visible décoratif', async () => {
        const wrapper = mount(XStepProgress, {
            props: {
                current: 4,
                total: 5,
                label: 'Question 4 sur 5',
                variant: 'ring',
                ringText: '4/5',
            },
            attachTo: document.body,
        });
        const circumference = 2 * Math.PI * 38;
        const [length] = (wrapper.findAll('circle')[1]?.attributes('stroke-dasharray') ?? '')
            .split(' ')
            .map(Number);

        expect(length).toBeCloseTo(0.8 * circumference);
        expect(wrapper.get('svg').attributes('aria-hidden')).toBe('true');
        expect(wrapper.get('text').text()).toBe('4/5');
        await expectNoAxeViolations(wrapper.element);
    });

    it('borne l’étape courante au total', () => {
        const wrapper = mount(XStepProgress, {
            props: { current: 9, total: 3, label: 'Terminé' },
        });

        expect(wrapper.attributes('aria-valuenow')).toBe('3');
        expect(wrapper.findAll('.bg-success')).toHaveLength(2);
    });
});
