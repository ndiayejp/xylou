<script setup lang="ts">
import { Lock } from '@lucide/vue';
import type { Component } from 'vue';
import type { reward } from '@/design/tokens';

export type XRewardTone = keyof typeof reward;

withDefaults(
    defineProps<{
        title: string;
        icon: Component;
        tone?: XRewardTone;
        description?: string;
        locked?: boolean;
        lockedLabel?: string;
        newLabel?: string;
        size?: 'adult' | 'kid';
    }>(),
    {
        tone: 'indigo',
        description: undefined,
        locked: false,
        lockedLabel: undefined,
        newLabel: undefined,
        size: 'kid',
    },
);

const tones: Record<XRewardTone, string> = {
    indigo: 'bg-reward-indigo-bg text-reward-indigo-text',
    amber: 'bg-reward-amber-bg text-reward-amber-text',
    red: 'bg-reward-red-bg text-reward-red-text',
    green: 'bg-reward-green-bg text-reward-green-text',
    blue: 'bg-reward-blue-bg text-reward-blue-text',
    pink: 'bg-reward-pink-bg text-reward-pink-text',
};
</script>

<template>
    <div class="flex flex-col items-center gap-2.5 text-center font-kid">
        <div class="relative">
            <!-- Verrouillée : seule la tuile s'efface, le texte garde son contraste. -->
            <span
                class="flex size-[84px] -rotate-[4deg] items-center justify-center rounded-kid-card"
                :class="[tones[tone], locked && 'opacity-50 grayscale-[.3]']"
                aria-hidden="true"
            >
                <component :is="icon" :size="38" />
            </span>
            <span
                v-if="locked"
                class="absolute -bottom-0.5 -right-0.5 flex size-[26px] items-center justify-center rounded-full border-2 border-line bg-surface text-muted"
                aria-hidden="true"
            >
                <Lock :size="13" />
            </span>
            <span
                v-if="newLabel && !locked"
                class="absolute -right-10 -top-3 whitespace-nowrap rounded-full bg-accent px-2.5 py-0.5 font-black text-text"
                :class="size === 'kid' ? 'text-[18px]' : 'text-caption'"
            >
                {{ newLabel }}
            </span>
        </div>
        <span
            class="font-black text-text"
            :class="size === 'kid' ? 'text-kid-body !font-black' : 'text-body'"
        >
            {{ title }}
        </span>
        <span v-if="locked && lockedLabel" class="sr-only">{{ lockedLabel }}</span>
        <span
            v-if="description"
            class="-mt-1.5 font-bold text-muted"
            :class="size === 'kid' ? 'text-[18px] leading-[1.4]' : 'text-caption'"
        >
            {{ description }}
        </span>
    </div>
</template>
