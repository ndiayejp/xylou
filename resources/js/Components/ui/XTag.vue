<script setup lang="ts">
import type { Component } from 'vue';

export type XTagTone = 'indigo' | 'amber' | 'green' | 'neutral';
export type XTagSize = 'sm' | 'md' | 'kid';

// Sans ton, la couleur vient de la classe passée par le parent (matière, niveau).
withDefaults(defineProps<{ icon?: Component; tone?: XTagTone; size?: XTagSize }>(), {
    icon: undefined,
    tone: undefined,
    size: 'md',
});

const tones: Record<XTagTone, string> = {
    indigo: 'bg-tint text-primary-strong',
    amber: 'bg-accent-soft text-accent-text',
    green: 'bg-success-soft text-success-text',
    neutral: 'bg-mastery-discover-bg text-muted',
};

const sizes: Record<XTagSize, string> = {
    sm: 'gap-1.5 px-[11px] py-[5px] text-[12px] font-bold',
    md: 'gap-1.5 px-[11px] py-[5px] text-caption font-bold',
    kid: 'gap-2 px-3.5 py-1.5 font-kid text-[18px] font-extrabold',
};

const iconSizes: Record<XTagSize, number> = { sm: 14, md: 15, kid: 20 };
</script>

<template>
    <span
        class="inline-flex items-center whitespace-nowrap rounded-full leading-[1.2]"
        :class="[sizes[size], tone && tones[tone]]"
    >
        <component
            :is="icon"
            v-if="icon"
            :size="iconSizes[size]"
            class="shrink-0"
            aria-hidden="true"
        />
        <slot />
    </span>
</template>
