<script setup lang="ts">
import { computed } from 'vue';
import type { mastery } from '@/design/tokens';

export type XProgressTone = 'primary' | keyof typeof mastery;

const props = withDefaults(
    defineProps<{
        value: number;
        max?: number;
        label: string;
        valueText: string;
        tone?: XProgressTone;
        size?: 'adult' | 'kid';
        hideValue?: boolean;
    }>(),
    {
        max: 100,
        tone: 'primary',
        size: 'adult',
        hideValue: false,
    },
);

const tones: Record<XProgressTone, string> = {
    primary: 'bg-primary',
    discover: 'bg-mastery-discover-bar',
    consolidate: 'bg-mastery-consolidate-bar',
    progressing: 'bg-mastery-progressing-bar',
    mastered: 'bg-mastery-mastered-bar',
};

const clamped = computed(() => Math.min(Math.max(props.value, 0), props.max));
const percent = computed(() => (props.max > 0 ? (clamped.value / props.max) * 100 : 0));
</script>

<template>
    <div class="flex items-center gap-2.5">
        <div
            role="progressbar"
            :aria-label="label"
            :aria-valuenow="clamped"
            aria-valuemin="0"
            :aria-valuemax="max"
            :aria-valuetext="valueText"
            class="grow overflow-hidden rounded-full bg-track"
            :class="size === 'kid' ? 'h-3.5' : 'h-2.5'"
        >
            <div
                class="h-full origin-left animate-grow rounded-full"
                :class="tones[tone]"
                :style="{ width: `${percent}%` }"
            />
        </div>
        <span
            v-if="!hideValue"
            class="shrink-0 text-right text-muted"
            aria-hidden="true"
            :class="
                size === 'kid'
                    ? 'font-kid text-[18px] font-extrabold'
                    : 'min-w-10 text-[14px] font-bold'
            "
        >
            {{ valueText }}
        </span>
    </div>
</template>
