<script setup lang="ts">
import { Check, CircleDashed, Repeat, TrendingUp } from '@lucide/vue';
import type { Component } from 'vue';
import XTag, { type XTagSize } from './XTag.vue';

export type XMasteryLevel = 'discover' | 'consolidate' | 'progressing' | 'mastered';

defineProps<{ level: XMasteryLevel; label: string; size?: XTagSize }>();

const styles: Record<XMasteryLevel, { icon: Component; classes: string }> = {
    discover: { icon: CircleDashed, classes: 'bg-mastery-discover-bg text-mastery-discover-text' },
    consolidate: {
        icon: Repeat,
        classes: 'bg-mastery-consolidate-bg text-mastery-consolidate-text',
    },
    progressing: {
        icon: TrendingUp,
        classes: 'bg-mastery-progressing-bg text-mastery-progressing-text',
    },
    mastered: { icon: Check, classes: 'bg-mastery-mastered-bg text-mastery-mastered-text' },
};
</script>

<template>
    <XTag :icon="styles[level].icon" :size="size" :class="styles[level].classes">
        {{ label }}
    </XTag>
</template>
