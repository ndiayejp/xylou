<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { computed } from 'vue';

export type XFilterOption = { value: string; label: string };

// Filtre en pastille : un select natif transparent posé sur la pastille (clavier, lecteurs d'écran
// et mobile gérés par le navigateur). « summary » remplace le libellé quand une valeur est choisie.
const props = withDefaults(
    defineProps<{
        label: string;
        options: XFilterOption[];
        allLabel: string;
        summary?: string;
    }>(),
    { summary: undefined },
);

const model = defineModel<string | null>({ default: null });

const active = computed(() => model.value !== null && model.value !== '');

const value = computed({
    get: () => model.value ?? '',
    set: (v: string) => (model.value = v === '' ? null : v),
});
</script>

<template>
    <div class="relative inline-flex">
        <select
            v-model="value"
            :aria-label="label"
            class="peer absolute inset-0 z-10 w-full cursor-pointer appearance-none opacity-0"
        >
            <option value="">{{ allLabel }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <span
            aria-hidden="true"
            :class="[
                'inline-flex h-10 items-center gap-2 whitespace-nowrap rounded-input border-[1.5px] px-3.5 text-[14px] font-bold text-text transition duration-hover',
                'peer-hover:border-primary peer-focus-visible:outline peer-focus-visible:outline-[3px] peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent',
                active ? 'border-primary bg-tint' : 'border-line bg-surface',
            ]"
        >
            {{ active && props.summary ? props.summary : label }}
            <ChevronDown :size="16" class="text-muted" />
        </span>
    </div>
</template>
