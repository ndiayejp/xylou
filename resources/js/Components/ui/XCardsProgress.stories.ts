import { Clock, Play, Rocket, Send, Trees } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import XButton from './XButton.vue';
import XCard from './XCard.vue';
import XLevelBadge from './XLevelBadge.vue';
import XProgressBar from './XProgressBar.vue';
import XStepProgress from './XStepProgress.vue';
import XSubjectTag from './XSubjectTag.vue';
import XTag from './XTag.vue';

const meta = {
    title: 'UI/Cartes et progression',
    component: XCard,
} satisfies Meta<typeof XCard>;

export default meta;
type Story = StoryObj<typeof meta>;

// En attendant XUniverseScene (tâche 3), un aplat coloré tient lieu d'illustration.
export const CartesActivite: Story = {
    name: 'Cartes d’activité',
    render: () => ({
        components: { XButton, XCard, XProgressBar, XSubjectTag, XTag },
        setup: () => ({ Clock, Play, Rocket, Send, Trees }),
        template: `
            <div class="flex flex-wrap items-start gap-4">
                <XCard as="article" class="w-[300px]" interactive>
                    <template #media>
                        <div class="flex h-full items-center justify-center bg-tint text-primary-text">
                            <component :is="Rocket" :size="48" aria-hidden="true" />
                        </div>
                    </template>
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-wrap gap-2">
                            <XSubjectTag subject="maths" label="Mathématiques" size="sm" />
                            <XTag tone="green" :icon="Send" size="sm">Envoyée</XTag>
                        </div>
                        <h3 class="text-[16px] font-extrabold">Problèmes de division</h3>
                        <div class="flex gap-3.5 text-caption text-muted">
                            <span class="flex items-center gap-1.5">
                                <component :is="Clock" :size="16" aria-hidden="true" />10 min
                            </span>
                            <span class="flex items-center gap-1.5">
                                <component :is="Rocket" :size="16" aria-hidden="true" />Espace
                            </span>
                        </div>
                    </div>
                </XCard>
                <XCard as="article" size="kid" class="w-[300px]" interactive>
                    <template #media>
                        <div class="flex h-full items-center justify-center bg-success-soft text-success-text">
                            <component :is="Trees" :size="56" aria-hidden="true" />
                        </div>
                    </template>
                    <h3 class="text-kid-body !font-black">Le renard compte</h3>
                    <XProgressBar :value="3" :max="5" size="kid" label="Le renard compte" value-text="3/5" />
                    <XButton size="kid" :icon="Play" block>Reprendre</XButton>
                </XCard>
            </div>`,
    }),
};

export const Progression: Story = {
    render: () => ({
        components: { XCard, XLevelBadge, XProgressBar },
        template: `
            <XCard padding="lg" class="flex max-w-[520px] flex-col gap-[18px]">
                <div class="flex flex-col gap-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                        <div>
                            <div class="text-body !font-bold">Fractions</div>
                            <div class="mt-0.5 text-caption !font-medium text-muted">Lucas · 6e</div>
                        </div>
                        <XLevelBadge level="progressing" label="En bonne voie" />
                    </div>
                    <XProgressBar :value="80" tone="progressing" label="Fractions" value-text="80 %" />
                </div>
                <div class="flex flex-col gap-2.5">
                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                        <div>
                            <div class="text-body !font-bold">Résolution de problèmes</div>
                            <div class="mt-0.5 text-caption !font-medium text-muted">Lucas · 6e</div>
                        </div>
                        <XLevelBadge level="consolidate" label="À consolider" />
                    </div>
                    <XProgressBar :value="60" tone="consolidate" label="Résolution de problèmes" value-text="60 %" />
                </div>
                <XProgressBar :value="100" tone="mastered" label="Addition" value-text="100 %" />
                <XProgressBar :value="15" tone="discover" label="Géométrie" value-text="15 %" />
            </XCard>`,
    }),
};

export const Etapes: Story = {
    name: 'Étapes',
    render: () => ({
        components: { XCard, XStepProgress },
        template: `
            <XCard padding="lg" class="flex max-w-[520px] flex-col gap-[18px]">
                <div class="flex items-center gap-[18px]">
                    <XStepProgress variant="ring" :current="4" :total="5" label="Question 4 sur 5" ring-text="4/5" />
                    <div>
                        <div class="text-[16px] font-extrabold">Anneau d’étape</div>
                        <p class="text-caption !font-medium text-muted">Progression dans une activité : « question 4 sur 5 ». Le texte dit toujours la valeur.</p>
                    </div>
                </div>
                <XStepProgress :current="3" :total="5" label="Question 3 sur 5" />
                <XStepProgress :current="1" :total="8" label="Question 1 sur 8" />
            </XCard>`,
    }),
};
