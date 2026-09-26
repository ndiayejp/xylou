<script setup lang="ts">
import type { Component } from 'vue';

// Présentation seule : la durée d'affichage et la file des toasts reviennent au layout.
withDefaults(
    defineProps<{ variant?: 'dark' | 'light'; icon?: Component; actionLabel?: string }>(),
    {
        variant: 'dark',
        icon: undefined,
        actionLabel: undefined,
    },
);

defineEmits<{ action: [] }>();
</script>

<template>
    <div
        role="status"
        class="inline-flex items-center gap-3 rounded-[14px] px-4 py-3 text-[14px] font-semibold"
        :class="
            variant === 'dark'
                ? 'bg-text text-surface'
                : 'border border-line bg-surface text-text shadow-rest'
        "
    >
        <component :is="icon" v-if="icon" :size="18" class="shrink-0" aria-hidden="true" />
        <span><slot /></span>
        <button
            v-if="actionLabel"
            type="button"
            class="ml-0.5 rounded-tag font-extrabold underline-offset-2 hover:underline"
            :class="variant === 'dark' ? 'text-accent' : 'text-primary-text'"
            @click="$emit('action')"
        >
            {{ actionLabel }}
        </button>
    </div>
</template>
