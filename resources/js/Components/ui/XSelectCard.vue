<script setup lang="ts">
import { Check } from '@lucide/vue';
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: Component;
        align?: 'start' | 'center';
        disabled?: boolean;
    }>(),
    { description: undefined, icon: undefined, align: 'start', disabled: false },
);

const model = defineModel<boolean>({ default: false });
</script>

<template>
    <button
        type="button"
        :aria-pressed="model"
        :disabled="disabled"
        class="relative flex min-h-touch flex-col gap-2.5 rounded-[18px] border-2 p-4 transition duration-hover ease-out hover:-translate-y-px hover:shadow-lift active:translate-y-0 active:scale-[.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none"
        :class="[
            model ? 'border-primary bg-tint' : 'border-line bg-surface hover:border-subtle',
            align === 'center' ? 'items-center text-center' : 'items-start text-left',
        ]"
        @click="model = !model"
    >
        <span
            v-if="model"
            class="absolute right-2.5 top-2.5 flex size-6 items-center justify-center rounded-full bg-primary text-white"
            aria-hidden="true"
        >
            <Check :size="14" :stroke-width="3" />
        </span>
        <span
            v-if="icon"
            class="flex size-11 items-center justify-center rounded-[14px] text-primary-text"
            :class="model ? 'bg-surface' : 'bg-tint'"
            aria-hidden="true"
        >
            <component :is="icon" :size="24" />
        </span>
        <span class="text-body font-bold text-text">{{ title }}</span>
        <span v-if="description" class="text-caption text-muted">{{ description }}</span>
    </button>
</template>
