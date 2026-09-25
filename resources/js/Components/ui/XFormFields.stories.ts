import { Eye, Hand, Headphones, Mail, Search } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { ref } from 'vue';
import XInput from './XInput.vue';
import XSegmented from './XSegmented.vue';
import XSelect from './XSelect.vue';
import XSelectCard from './XSelectCard.vue';
import XTextarea from './XTextarea.vue';
import XToggle from './XToggle.vue';

const meta = {
    title: 'UI/Champs de formulaire',
    component: XInput,
    args: { label: 'Prénom de l’enfant', modelValue: 'Emma' },
} satisfies Meta<typeof XInput>;

export default meta;
type Story = StoryObj<typeof meta>;

export const Champ: Story = {};

export const ChampsEtEtats: Story = {
    name: 'Champs et états',
    render: () => ({
        components: { XInput, XSelect, XTextarea },
        setup: () => ({
            Mail,
            Search,
            name: ref('Emma'),
            email: ref('sophie@'),
            search: ref(''),
            level: ref('CE2'),
            notes: ref('Il comprend le calcul mais se perd quand l’énoncé est long.'),
            levels: ['CP', 'CE1', 'CE2', 'CM1', 'CM2', '6e'].map((l) => ({ value: l, label: l })),
        }),
        template: `
            <div class="grid max-w-4xl grid-cols-3 gap-4">
                <XInput v-model="name" label="Prénom de l’enfant" />
                <XInput v-model="email" label="E-mail" type="email" :icon="Mail" error="Il manque la fin de l’adresse (ex. : @mail.fr)." />
                <XInput v-model="search" label="Rechercher" type="search" :icon="Search" placeholder="Fractions, lecture…" hint="Recherche intelligente : essayez « problèmes en 2 étapes »" />
                <XSelect v-model="level" label="Niveau scolaire" :options="levels" />
                <XInput label="Désactivé" model-value="Non modifiable" disabled />
                <XInput label="Surnom" optional-label="(facultatif)" />
                <div class="col-span-3">
                    <XTextarea v-model="notes" label="Ce que vous observez" optional-label="(facultatif)" />
                </div>
            </div>`,
    }),
};

export const Segmente: Story = {
    name: 'Segmenté',
    render: () => ({
        components: { XSegmented },
        setup: () => ({
            value: ref('practice'),
            options: [
                { value: 'discovery', label: 'Découverte' },
                { value: 'practice', label: 'Entraînement' },
                { value: 'consolidation', label: 'Consolidation' },
                { value: 'challenge', label: 'Défi' },
            ],
        }),
        template:
            '<div class="max-w-xl"><XSegmented v-model="value" label="Difficulté" :hide-label="false" :options="options" /></div>',
    }),
};

export const Interrupteurs: Story = {
    render: () => ({
        components: { XToggle },
        setup: () => ({ on: ref(true), off: ref(false) }),
        template: `
            <div class="flex flex-col gap-2">
                <XToggle v-model="on" label="Rappel doux" hint="Une notification calme, une fois par jour" />
                <XToggle v-model="off" label="Mode calme" />
                <XToggle label="Désactivé" disabled />
            </div>`,
    }),
};

export const CartesSelectionnables: Story = {
    name: 'Cartes sélectionnables',
    render: () => ({
        components: { XSelectCard },
        setup: () => ({
            Eye,
            Headphones,
            Hand,
            visual: ref(true),
            audio: ref(false),
            hands: ref(false),
        }),
        template: `
            <div class="grid max-w-2xl grid-cols-3 gap-3">
                <XSelectCard v-model="visual" :icon="Eye" title="Visuel" description="Schémas, images, couleurs" />
                <XSelectCard v-model="audio" :icon="Headphones" title="Auditif" description="Écouter, répéter, chanter" />
                <XSelectCard v-model="hands" :icon="Hand" title="Manipulation" description="Toucher, construire" />
                <XSelectCard :model-value="true" :icon="Eye" title="Centrée" align="center" />
                <XSelectCard :icon="Eye" title="Désactivée" disabled />
            </div>`,
    }),
};
