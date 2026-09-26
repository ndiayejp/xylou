import { mount } from '@vue/test-utils';
import { Rocket } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XRewardBadge from '../XRewardBadge.vue';

describe('XRewardBadge', () => {
    it('affiche le titre et la description, icône décorative', async () => {
        const wrapper = mount(XRewardBadge, {
            props: { title: 'Première mission', description: 'Obtenu', icon: Rocket },
            attachTo: document.body,
        });

        expect(wrapper.text()).toBe('Première missionObtenu');
        expect(wrapper.get('svg').attributes('aria-hidden')).toBe('true');
        await expectNoAxeViolations(wrapper.element);
    });

    it("côté enfant, aucun texte n'est sous 18 px", () => {
        const wrapper = mount(XRewardBadge, {
            props: {
                title: 'Persévérance',
                description: '3 essais',
                icon: Rocket,
                newLabel: 'Nouveau !',
            },
        });
        const texts = wrapper
            .findAll('span')
            .filter((s) => s.element.children.length === 0 && s.text());

        texts.forEach((span) =>
            expect(span.classes().some((c) => ['text-kid-body', 'text-[18px]'].includes(c))).toBe(
                true,
            ),
        );
    });

    it('verrouillée : seule la tuile est estompée, et le verrou est annoncé', () => {
        const wrapper = mount(XRewardBadge, {
            props: {
                title: 'Défi relevé',
                description: 'Encore 1 défi',
                icon: Rocket,
                locked: true,
                lockedLabel: 'Pas encore obtenue',
                newLabel: 'Nouveau !',
            },
        });

        expect(wrapper.get('.rounded-kid-card').classes()).toContain('opacity-50');
        expect(wrapper.get('.sr-only').text()).toBe('Pas encore obtenue');
        expect(wrapper.text()).not.toContain('Nouveau !');
    });

    it('applique le ton demandé', () => {
        const wrapper = mount(XRewardBadge, {
            props: { title: 'Série', icon: Rocket, tone: 'amber' },
        });

        expect(wrapper.get('.rounded-kid-card').classes()).toContain('bg-reward-amber-bg');
    });
});
