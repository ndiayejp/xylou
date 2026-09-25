import { mount } from '@vue/test-utils';
import { Eye } from '@lucide/vue';
import { describe, expect, it } from 'vitest';
import { expectNoAxeViolations } from '@/test/axe';
import XAiBadge from '../XAiBadge.vue';
import XLevelBadge from '../XLevelBadge.vue';
import XSubjectTag from '../XSubjectTag.vue';
import XTag from '../XTag.vue';

describe('XTag', () => {
    it('affiche son texte, une icône décorative et son ton', async () => {
        const wrapper = mount(XTag, {
            props: { icon: Eye, tone: 'amber' },
            slots: { default: 'À valider' },
            attachTo: document.body,
        });

        expect(wrapper.text()).toBe('À valider');
        expect(wrapper.classes()).toEqual(
            expect.arrayContaining(['bg-accent-soft', 'text-accent-text']),
        );
        expect(wrapper.get('svg').attributes('aria-hidden')).toBe('true');
        await expectNoAxeViolations(wrapper.element);
    });

    it('la taille enfant ne descend pas sous 18 px', () => {
        const wrapper = mount(XTag, { props: { size: 'kid' }, slots: { default: 'Maîtrisé' } });

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['font-kid', 'text-[18px]']));
    });
});

describe('XSubjectTag', () => {
    it.each([
        ['maths', 'bg-subject-maths-bg'],
        ['french', 'bg-subject-french-bg'],
        ['sciences', 'bg-subject-sciences-bg'],
        ['history', 'bg-subject-history-bg'],
        ['geography', 'bg-subject-geography-bg'],
        ['languages', 'bg-subject-languages-bg'],
    ] as const)('%s : couleur, icône et libellé', (subject, expected) => {
        const wrapper = mount(XSubjectTag, { props: { subject, label: 'Matière' } });

        expect(wrapper.classes()).toContain(expected);
        expect(wrapper.find('svg').exists()).toBe(true);
        expect(wrapper.text()).toBe('Matière');
    });
});

describe('XLevelBadge', () => {
    it.each([
        ['discover', 'bg-mastery-discover-bg'],
        ['consolidate', 'bg-mastery-consolidate-bg'],
        ['progressing', 'bg-mastery-progressing-bg'],
        ['mastered', 'bg-mastery-mastered-bg'],
    ] as const)('%s : couleur, icône et mot, jamais une note', (level, expected) => {
        const wrapper = mount(XLevelBadge, { props: { level, label: 'Niveau' } });

        expect(wrapper.classes()).toContain(expected);
        expect(wrapper.find('svg').exists()).toBe(true);
        expect(wrapper.text()).toBe('Niveau');
    });
});

describe('XAiBadge', () => {
    it("signale un contenu proposé par l'IA", () => {
        const wrapper = mount(XAiBadge, { props: { label: 'Proposé par l’IA' } });

        expect(wrapper.text()).toBe('Proposé par l’IA');
        expect(wrapper.classes()).toEqual(expect.arrayContaining(['bg-tint', 'text-[12px]']));
    });
});
