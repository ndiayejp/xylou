<script setup lang="ts">
import { Sparkles } from '@lucide/vue';
import XTag, { type XTagSize } from './XTag.vue';

withDefaults(defineProps<{ label: string; size?: XTagSize }>(), { size: 'sm' });
</script>

<template>
    <XTag :icon="Sparkles" tone="indigo" :size="size">{{ label }}</XTag>
</template>
