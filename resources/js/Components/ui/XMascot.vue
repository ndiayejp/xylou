<script setup lang="ts">
// Xy, le guide : il accompagne, il ne juge jamais. Décoratif sauf si un label est fourni.
export type XMascotMood = 'happy' | 'think' | 'cheer';

withDefaults(
    defineProps<{ mood?: XMascotMood; size?: number; label?: string; animated?: boolean }>(),
    {
        mood: 'happy',
        size: 96,
        label: undefined,
        animated: false,
    },
);
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 90 90"
        class="block shrink-0"
        :class="animated && 'animate-breathe'"
        :role="label ? 'img' : undefined"
        :aria-label="label"
        :aria-hidden="label ? undefined : 'true'"
        :data-mood="mood"
    >
        <template v-if="mood === 'think'">
            <circle cx="76" cy="22" r="4" class="fill-accent" />
            <circle cx="84" cy="12" r="2.5" class="fill-accent" />
        </template>
        <template v-if="mood === 'cheer'">
            <path
                d="M10 40q-8 -10 -4 -20"
                class="stroke-primary"
                fill="none"
                stroke-width="7"
                stroke-linecap="round"
            />
            <path
                d="M80 40q8 -10 4 -20"
                class="stroke-primary"
                fill="none"
                stroke-width="7"
                stroke-linecap="round"
            />
        </template>
        <path d="M45 14c-3-8 2-12 8-12-1 6-4 9-8 12z" class="fill-success" />
        <path
            d="M45 13c20 0 34 16 34 38 0 20-15 33-34 33S11 71 11 51c0-22 14-38 34-38z"
            class="fill-primary"
        />
        <ellipse cx="45" cy="64" rx="20" ry="14" class="fill-secondary" />
        <circle cx="34" cy="44" r="7" class="fill-surface" />
        <circle cx="56" cy="44" r="7" class="fill-surface" />
        <circle cx="35" cy="45" r="3.5" class="fill-text" />
        <circle cx="57" cy="45" r="3.5" class="fill-text" />
        <circle cx="26" cy="54" r="4" fill="#FF9EB0" opacity=".7" />
        <circle cx="64" cy="54" r="4" fill="#FF9EB0" opacity=".7" />
        <path v-if="mood === 'cheer'" d="M36 56q9 12 18 0z" class="fill-text" />
        <path
            v-else
            :d="mood === 'think' ? 'M40 60q5 -3 10 0' : 'M38 57q7 7 14 0'"
            class="stroke-text"
            fill="none"
            stroke-width="3"
            stroke-linecap="round"
        />
    </svg>
</template>
