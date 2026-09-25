<script setup lang="ts">
import { BookOpen, Calculator, FlaskConical, Globe, Landmark, MessageSquare } from '@lucide/vue';
import type { Component } from 'vue';
import XTag, { type XTagSize } from './XTag.vue';

export type XSubject = 'maths' | 'french' | 'sciences' | 'history' | 'geography' | 'languages';

defineProps<{ subject: XSubject; label: string; size?: XTagSize }>();

const styles: Record<XSubject, { icon: Component; classes: string }> = {
    maths: { icon: Calculator, classes: 'bg-subject-maths-bg text-subject-maths-text' },
    french: { icon: BookOpen, classes: 'bg-subject-french-bg text-subject-french-text' },
    sciences: { icon: FlaskConical, classes: 'bg-subject-sciences-bg text-subject-sciences-text' },
    history: { icon: Landmark, classes: 'bg-subject-history-bg text-subject-history-text' },
    geography: { icon: Globe, classes: 'bg-subject-geography-bg text-subject-geography-text' },
    languages: {
        icon: MessageSquare,
        classes: 'bg-subject-languages-bg text-subject-languages-text',
    },
};
</script>

<template>
    <XTag :icon="styles[subject].icon" :size="size" :class="styles[subject].classes">
        {{ label }}
    </XTag>
</template>
