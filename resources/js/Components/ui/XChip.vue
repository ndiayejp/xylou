<script setup lang="ts">
import { Check, Plus } from '@lucide/vue';

withDefaults(defineProps<{ label: string; variant?: 'check' | 'filter'; disabled?: boolean }>(), {
    variant: 'check',
    disabled: false,
});

const model = defineModel<boolean>({ default: false });
</script>

<template>
    <button
        type="button"
        :aria-pressed="model"
        :disabled="disabled"
        class="inline-flex min-h-touch items-center gap-2 whitespace-nowrap rounded-full px-4 text-[14px] font-bold transition-colors duration-hover disabled:cursor-not-allowed disabled:opacity-50"
        :class="
            variant === 'filter'
                ? model
                    ? 'border-[1.5px] border-text bg-text text-white'
                    : 'border-[1.5px] border-line bg-surface text-text hover:border-subtle'
                : model
                  ? 'border-2 border-primary bg-tint text-text'
                  : 'border-2 border-line bg-surface text-text hover:border-subtle'
        "
        @click="model = !model"
    >
        <template v-if="variant === 'check'">
            <Check
                v-if="model"
                :size="16"
                :stroke-width="3"
                class="shrink-0 text-primary-text"
                aria-hidden="true"
            />
            <Plus v-else :size="16" class="shrink-0 text-muted" aria-hidden="true" />
        </template>
        {{ label }}
    </button>
</template>
