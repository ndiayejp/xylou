import { Ellipsis, Pencil, Volume2 } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import XIconButton from './XIconButton.vue';

const meta = {
    title: 'UI/XIconButton',
    component: XIconButton,
    args: {
        icon: Volume2,
        label: 'Écouter la question',
        variant: 'outline',
        size: 'md',
        disabled: false,
    },
    argTypes: {
        variant: { control: 'inline-radio', options: ['outline', 'soft', 'ghost'] },
        size: { control: 'inline-radio', options: ['md', 'kid'] },
        icon: { control: false },
    },
} satisfies Meta<typeof XIconButton>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const Variantes: Story = {
    render: () => ({
        components: { XIconButton },
        setup: () => ({ Volume2, Pencil, Ellipsis }),
        template: `
            <div class="flex items-center gap-3">
                <XIconButton :icon="Volume2" label="Écouter la question" />
                <XIconButton :icon="Pencil" label="Modifier" variant="soft" />
                <XIconButton :icon="Ellipsis" label="Plus d’options" variant="ghost" />
                <XIconButton :icon="Volume2" label="Écouter la question" size="kid" variant="soft" />
                <XIconButton :icon="Pencil" label="Modifier" disabled />
            </div>`,
    }),
};
