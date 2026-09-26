<script setup lang="ts">
import type { Component } from 'vue';

export type XEmptyStateTone = 'indigo' | 'blue' | 'green' | 'amber' | 'red' | 'neutral';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: Component;
        tone?: XEmptyStateTone;
        headingTag?: 'h2' | 'h3' | 'h4';
        size?: 'adult' | 'kid';
    }>(),
    {
        description: undefined,
        icon: undefined,
        tone: 'indigo',
        headingTag: 'h2',
        size: 'adult',
    },
);

const tones: Record<XEmptyStateTone, string> = {
    indigo: 'bg-tint text-primary-text',
    blue: 'bg-info-soft text-info-text',
    green: 'bg-success-soft text-success-text',
    amber: 'bg-accent-soft text-accent-text',
    red: 'bg-danger-soft text-danger',
    neutral: 'bg-mastery-discover-bg text-muted',
};
</script>

<template>
    <div class="flex flex-col items-center gap-3 text-center" :class="size === 'kid' && 'font-kid'">
        <!-- Illustration libre (mascotte, scène) ou tuile d'icône. -->
        <slot name="illustration">
            <span
                v-if="icon"
                class="flex size-16 items-center justify-center rounded-[22px]"
                :class="tones[tone]"
                aria-hidden="true"
            >
                <component :is="icon" :size="28" />
            </span>
        </slot>
        <component
            :is="headingTag"
            class="font-extrabold"
            :class="size === 'kid' ? 'text-[24px] !font-black leading-[1.25]' : 'text-[18px]'"
        >
            {{ title }}
        </component>
        <p
            v-if="description"
            class="max-w-[280px] leading-[1.5] text-muted"
            :class="size === 'kid' ? 'max-w-[340px] text-[18px] font-bold' : 'text-[14px]'"
        >
            {{ description }}
        </p>
        <div v-if="$slots.actions" class="mt-1 flex flex-wrap justify-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
