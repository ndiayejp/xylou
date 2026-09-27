import { Archive, Copy, Trash2 } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { ref } from 'vue';
import XFilterSelect from './XFilterSelect.vue';
import XMenu from './XMenu.vue';

const meta = {
    title: 'UI/Filtres et menus',
    component: XMenu,
    args: {
        label: 'Actions sur l’activité',
        items: [
            { key: 'duplicate', label: 'Dupliquer', icon: Copy },
            { key: 'archive', label: 'Archiver', icon: Archive },
            { key: 'delete', label: 'Supprimer', icon: Trash2, danger: true },
        ],
    },
} satisfies Meta<typeof XMenu>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Menu: Story = {
    render: (args) => ({
        components: { XMenu },
        setup: () => ({ args, chosen: ref('') }),
        template: `
            <div class="flex min-h-[220px] items-start gap-4">
                <XMenu v-bind="args" @select="chosen = $event" />
                <p class="text-muted">{{ chosen ? 'Choisi : ' + chosen : '' }}</p>
            </div>`,
    }),
};

export const Filtres: Story = {
    render: () => ({
        components: { XFilterSelect },
        setup: () => ({
            subject: ref<string | null>('maths'),
            grade: ref<string | null>(null),
            subjects: [
                { value: 'maths', label: 'Maths' },
                { value: 'french', label: 'Français' },
            ],
            grades: ['CP', 'CE1', 'CE2', 'CM1', 'CM2'].map((g) => ({ value: g, label: g })),
        }),
        template: `
            <div class="flex flex-wrap gap-2">
                <XFilterSelect v-model="subject" label="Matière" all-label="Toutes les matières" :summary="'Matière : ' + (subjects.find((s) => s.value === subject)?.label ?? '')" :options="subjects" />
                <XFilterSelect v-model="grade" label="Niveau" all-label="Tous les niveaux" :summary="'Niveau : ' + grade" :options="grades" />
            </div>`,
    }),
};
