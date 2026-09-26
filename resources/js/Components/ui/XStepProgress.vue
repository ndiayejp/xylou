<script setup lang="ts">
import { computed } from 'vue';

// current : étape en cours (1 à total). En anneau, la part remplie vaut current / total.
const props = withDefaults(
    defineProps<{
        current: number;
        total: number;
        label: string;
        variant?: 'segments' | 'ring';
        ringText?: string;
    }>(),
    {
        variant: 'segments',
        ringText: undefined,
    },
);

const step = computed(() => Math.min(Math.max(props.current, 0), props.total));

const radius = 38;
const circumference = 2 * Math.PI * radius;
const dash = computed(() => (props.total > 0 ? (step.value / props.total) * circumference : 0));

function segmentClass(index: number): string {
    if (index < step.value) return 'bg-success';
    if (index === step.value) return 'bg-primary';

    return 'bg-disabled';
}
</script>

<template>
    <div
        role="progressbar"
        :aria-label="label"
        :aria-valuenow="step"
        aria-valuemin="0"
        :aria-valuemax="total"
        :aria-valuetext="label"
    >
        <div v-if="variant === 'segments'" class="flex gap-1.5">
            <span
                v-for="index in total"
                :key="index"
                class="h-2.5 grow rounded-full"
                :class="segmentClass(index)"
            />
        </div>
        <svg v-else width="92" height="92" viewBox="0 0 92 92" aria-hidden="true">
            <circle
                cx="46"
                cy="46"
                :r="radius"
                fill="none"
                class="stroke-track"
                stroke-width="10"
            />
            <circle
                cx="46"
                cy="46"
                :r="radius"
                fill="none"
                class="stroke-primary"
                stroke-width="10"
                stroke-linecap="round"
                transform="rotate(-90 46 46)"
                :stroke-dasharray="`${dash} ${circumference}`"
            />
            <text
                v-if="ringText"
                x="46"
                y="52"
                text-anchor="middle"
                class="fill-text text-[20px] font-extrabold"
            >
                {{ ringText }}
            </text>
        </svg>
    </div>
</template>
