import { Archive, Eye, Heart, Layers, Pencil, Rocket, Send, Sparkles, Target } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { ref } from 'vue';
import XAiBadge from './XAiBadge.vue';
import XAvatar from './XAvatar.vue';
import XChip from './XChip.vue';
import XLevelBadge from './XLevelBadge.vue';
import XRewardBadge from './XRewardBadge.vue';
import XSubjectTag from './XSubjectTag.vue';
import XTag from './XTag.vue';

const meta = {
    title: 'UI/Tags, badges et avatars',
    component: XTag,
} satisfies Meta<typeof XTag>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Matieres: Story = {
    name: 'Matières',
    render: () => ({
        components: { XSubjectTag },
        template: `
            <div class="flex flex-wrap gap-2">
                <XSubjectTag subject="maths" label="Mathématiques" />
                <XSubjectTag subject="french" label="Français" />
                <XSubjectTag subject="sciences" label="Sciences" />
                <XSubjectTag subject="history" label="Histoire" />
                <XSubjectTag subject="geography" label="Géographie" />
                <XSubjectTag subject="languages" label="Langues" />
            </div>`,
    }),
};

export const NiveauxEtStatuts: Story = {
    name: 'Niveaux et statuts',
    render: () => ({
        components: { XLevelBadge, XAiBadge, XTag },
        setup: () => ({ Eye, Send, Pencil, Archive }),
        template: `
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap gap-2">
                    <XLevelBadge level="mastered" label="Maîtrisé" />
                    <XLevelBadge level="progressing" label="En bonne voie" />
                    <XLevelBadge level="consolidate" label="À consolider" />
                    <XLevelBadge level="discover" label="À découvrir" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <XAiBadge label="Proposé par l’IA" />
                    <XTag tone="amber" :icon="Eye">À valider</XTag>
                    <XTag tone="green" :icon="Send">Envoyée à Emma</XTag>
                    <XTag tone="neutral" :icon="Pencil">Brouillon</XTag>
                    <XTag tone="neutral" :icon="Archive">Archivée</XTag>
                </div>
                <div class="flex flex-wrap gap-2">
                    <XLevelBadge level="mastered" label="Maîtrisé" size="kid" />
                    <XLevelBadge level="consolidate" label="On y revient" size="kid" />
                </div>
            </div>`,
    }),
};

export const Puces: Story = {
    render: () => ({
        components: { XChip },
        setup: () => ({ a: ref(true), b: ref(false), c: ref(true), d: ref(false) }),
        template: `
            <div class="flex flex-col gap-4">
                <div class="flex flex-wrap gap-2.5">
                    <XChip v-model="a" label="Résolution de problèmes" />
                    <XChip v-model="b" label="Calcul mental" />
                    <XChip label="Désactivée" disabled />
                </div>
                <div class="flex flex-wrap gap-2.5">
                    <XChip v-model="c" variant="filter" label="Tout" />
                    <XChip v-model="d" variant="filter" label="Sports" />
                </div>
            </div>`,
    }),
};

export const Avatars: Story = {
    render: () => ({
        components: { XAvatar },
        template: `
            <div class="flex items-center gap-3">
                <XAvatar name="Emma" color="coral" size="sm" />
                <XAvatar name="Lucas" color="teal" />
                <XAvatar name="Sophie" color="blue" size="lg" />
                <XAvatar name="Inès" />
                <XAvatar name="Noah" />
                <XAvatar name="Jade" />
            </div>`,
    }),
};

export const Recompenses: Story = {
    name: 'Récompenses',
    render: () => ({
        components: { XRewardBadge },
        setup: () => ({ Rocket, Layers, Heart, Sparkles, Target }),
        template: `
            <div class="flex flex-wrap gap-10 pt-4">
                <XRewardBadge :icon="Rocket" title="Première mission" description="Obtenu" new-label="Nouveau !" />
                <XRewardBadge :icon="Layers" tone="amber" title="5 activités" description="Obtenu" />
                <XRewardBadge :icon="Heart" tone="red" title="Persévérance" description="3 essais sans lâcher" />
                <XRewardBadge :icon="Sparkles" tone="green" title="Nouvelle compétence" description="Obtenu" />
                <XRewardBadge :icon="Target" tone="blue" title="Défi relevé" description="Encore 1 défi" locked locked-label="Pas encore obtenue" />
                <XRewardBadge :icon="Rocket" tone="pink" title="Version adulte" description="Obtenu" size="adult" />
            </div>`,
    }),
};
