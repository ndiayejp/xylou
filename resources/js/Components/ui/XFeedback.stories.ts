import { Check, Library, Pause, RotateCcw, Trash2, TriangleAlert, WifiOff } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { ref } from 'vue';
import XButton from './XButton.vue';
import XCallout from './XCallout.vue';
import XCard from './XCard.vue';
import XChildSwitcher from './XChildSwitcher.vue';
import XEmptyState from './XEmptyState.vue';
import XSkeleton from './XSkeleton.vue';
import XToast from './XToast.vue';

const meta = {
    title: 'UI/Messages et états',
    component: XCallout,
} satisfies Meta<typeof XCallout>;

export default meta;
type Story = StoryObj<typeof meta>;

export const MessagesContextuels: Story = {
    name: 'Messages contextuels',
    render: () => ({
        components: { XCallout },
        template: `
            <div class="grid max-w-[900px] gap-3 sm:grid-cols-2">
                <XCallout tone="ai" title="Transparence IA">Cette activité a été générée à partir des passions d’Emma (espace). Relisez-la avant de l’envoyer.</XCallout>
                <XCallout tone="warn" title="Activité interrompue">Emma a interrompu « Mission Mars » à la question 3. Elle pourra reprendre là où elle s’est arrêtée.</XCallout>
                <XCallout tone="ok" title="Rapport disponible">Bilan de septembre prêt. Relisez-le avant de le partager.</XCallout>
                <XCallout tone="err" title="Quelque chose n’a pas marché">La génération n’a pas abouti. Vos réglages sont conservés : réessayez.</XCallout>
                <XCallout tone="info">Les observations restent visibles par les professionnels tant que le partage est actif.</XCallout>
            </div>`,
    }),
};

export const Toasts: Story = {
    render: () => ({
        components: { XToast },
        setup: () => ({ Check, Trash2, WifiOff }),
        template: `
            <div class="flex flex-wrap gap-3">
                <XToast :icon="Check">Activité envoyée à Emma</XToast>
                <XToast :icon="Trash2" action-label="Annuler">Activité supprimée</XToast>
                <XToast :icon="WifiOff" variant="light">Hors ligne — tes réponses sont gardées</XToast>
            </div>`,
    }),
};

export const EtatsVides: Story = {
    name: 'États vides et erreurs',
    render: () => ({
        components: { XButton, XCard, XEmptyState },
        setup: () => ({ Library, Pause, RotateCcw, TriangleAlert }),
        template: `
            <div class="grid max-w-[1000px] gap-6 sm:grid-cols-3">
                <XCard padding="lg" class="flex min-h-[300px] items-center justify-center">
                    <XEmptyState :icon="Library" title="Votre bibliothèque est vide" description="Les activités que vous générez ou enregistrez apparaîtront ici.">
                        <template #actions><XButton size="sm">Générer une activité</XButton></template>
                    </XEmptyState>
                </XCard>
                <XCard padding="lg" class="flex min-h-[300px] items-center justify-center">
                    <XEmptyState :icon="TriangleAlert" tone="red" title="Impossible de charger la progression" description="Le serveur ne répond pas. Vos données ne sont pas perdues.">
                        <template #actions>
                            <XButton size="sm" :icon="RotateCcw">Réessayer</XButton>
                            <XButton size="sm" variant="ghost">Aide</XButton>
                        </template>
                    </XEmptyState>
                </XCard>
                <XCard size="kid" class="flex min-h-[300px] items-center justify-center">
                    <XEmptyState :icon="Pause" tone="amber" size="kid" title="On reprend ?" description="Mission Mars · question 3 sur 5. Tes réponses sont gardées.">
                        <template #actions><XButton size="kid">Reprendre</XButton></template>
                    </XEmptyState>
                </XCard>
            </div>`,
    }),
};

export const Chargement: Story = {
    render: () => ({
        components: { XCard, XSkeleton },
        template: `
            <XCard padding="lg" class="w-[300px]" aria-busy="true">
                <span class="sr-only" role="status">Chargement des activités</span>
                <div class="flex flex-col gap-3">
                    <XSkeleton shape="block" />
                    <XSkeleton class="h-4 w-3/5" />
                    <XSkeleton class="w-[85%]" />
                    <XSkeleton class="w-2/5" />
                    <div class="flex items-center gap-3">
                        <XSkeleton shape="circle" />
                        <XSkeleton class="w-1/2" />
                    </div>
                </div>
            </XCard>`,
    }),
};

export const SelecteurEnfant: Story = {
    name: 'Sélecteur d’enfant',
    render: () => ({
        components: { XChildSwitcher },
        setup: () => ({
            selected: ref(1),
            items: [
                { id: 1, name: 'Emma', details: '8 ans · CE2', color: 'coral' },
                { id: 2, name: 'Lucas', details: '11 ans · 6e', color: 'teal' },
            ],
        }),
        template: `<XChildSwitcher v-model="selected" :items="items" label="Changer d’enfant" add-label="Ajouter un enfant" />`,
    }),
};
