import { Check, Play, Sparkles, Trash2 } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import XButton from './XButton.vue';

const meta = {
    title: 'UI/XButton',
    component: XButton,
    args: { variant: 'primary', size: 'md', loading: false, disabled: false, block: false },
    argTypes: {
        variant: {
            control: 'select',
            options: ['primary', 'secondary', 'soft', 'ghost', 'accent', 'success', 'danger'],
        },
        size: { control: 'inline-radio', options: ['sm', 'md', 'lg', 'kid'] },
        icon: { control: false },
    },
    render: (args) => ({
        components: { XButton },
        setup: () => ({ args }),
        template: '<XButton v-bind="args">Continuer</XButton>',
    }),
} satisfies Meta<typeof XButton>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {};

export const Variantes: Story = {
    render: () => ({
        components: { XButton },
        setup: () => ({ Sparkles, Check, Trash2 }),
        template: `
            <div class="flex flex-wrap gap-3">
                <XButton>Créer un profil</XButton>
                <XButton variant="secondary">Découvrir</XButton>
                <XButton variant="soft">Voir le détail</XButton>
                <XButton variant="ghost">Plus tard</XButton>
                <XButton variant="accent" :icon="Sparkles">Débloquer</XButton>
                <XButton variant="success" :icon="Check">Approuver</XButton>
                <XButton variant="danger" :icon="Trash2">Supprimer</XButton>
            </div>`,
    }),
};

export const Tailles: Story = {
    render: () => ({
        components: { XButton },
        setup: () => ({ Play }),
        template: `
            <div class="flex flex-wrap items-center gap-3">
                <XButton size="sm">Petit</XButton>
                <XButton>Moyen</XButton>
                <XButton size="lg">Grand</XButton>
                <XButton size="kid" :icon="Play">Commencer</XButton>
                <XButton size="kid" variant="soft">Un indice</XButton>
            </div>`,
    }),
};

export const Chargement: Story = {
    args: { loading: true },
    render: (args) => ({
        components: { XButton },
        setup: () => ({ args }),
        template: '<XButton v-bind="args">Génération…</XButton>',
    }),
};

export const Desactive: Story = { name: 'Désactivé', args: { disabled: true } };
