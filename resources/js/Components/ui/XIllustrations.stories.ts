import type { Meta, StoryObj } from '@storybook/vue3-vite';
import XMascot from './XMascot.vue';
import XUniverseScene from './XUniverseScene.vue';

const meta = {
    title: 'UI/Mascotte et univers',
    component: XUniverseScene,
    args: { universe: 'space' },
} satisfies Meta<typeof XUniverseScene>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Univers: Story = {
    render: () => ({
        components: { XUniverseScene },
        template: `
            <div class="grid max-w-[1000px] gap-6 sm:grid-cols-3">
                <figure class="flex flex-col gap-2">
                    <div class="h-[200px] overflow-hidden rounded-[24px]">
                        <XUniverseScene universe="space" label="Un rover roule sur Mars, une planète à anneaux dans le ciel" />
                    </div>
                    <figcaption class="text-caption text-muted">Espace</figcaption>
                </figure>
                <figure class="flex flex-col gap-2">
                    <div class="h-[200px] overflow-hidden rounded-[24px]">
                        <XUniverseScene universe="football" label="Un terrain de football, un ballon file vers le but" />
                    </div>
                    <figcaption class="text-caption text-muted">Football</figcaption>
                </figure>
                <figure class="flex flex-col gap-2">
                    <div class="h-[200px] overflow-hidden rounded-[24px]">
                        <XUniverseScene universe="forest" label="Un renard traverse une forêt au soleil" />
                    </div>
                    <figcaption class="text-caption text-muted">Forêt</figcaption>
                </figure>
            </div>`,
    }),
};

export const Mascotte: Story = {
    render: () => ({
        components: { XMascot },
        template: `
            <div class="flex flex-wrap items-end gap-12">
                <div class="flex flex-col items-center gap-2">
                    <XMascot mood="happy" animated />
                    <span class="text-caption">Accueil</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <XMascot mood="think" />
                    <span class="text-caption">Réfléchit avec toi</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <XMascot mood="cheer" />
                    <span class="text-caption">Célèbre</span>
                </div>
                <XMascot mood="happy" :size="48" />
            </div>`,
    }),
};
