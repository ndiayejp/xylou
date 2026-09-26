<script setup lang="ts">
import { Check, CircleAlert, Info, Pause, Sparkles } from '@lucide/vue';
import type { Component } from 'vue';

export type XCalloutTone = 'ai' | 'info' | 'warn' | 'ok' | 'err';

withDefaults(defineProps<{ tone?: XCalloutTone; title?: string; icon?: Component }>(), {
    tone: 'info',
    title: undefined,
    icon: undefined,
});

const tones: Record<XCalloutTone, { icon: Component; box: string; accent: string }> = {
    ai: { icon: Sparkles, box: 'bg-tint', accent: 'text-primary-strong' },
    info: { icon: Info, box: 'bg-info-soft', accent: 'text-info-text' },
    warn: { icon: Pause, box: 'bg-accent-soft', accent: 'text-accent-text' },
    ok: { icon: Check, box: 'bg-success-soft', accent: 'text-success-text' },
    err: { icon: CircleAlert, box: 'bg-danger-soft', accent: 'text-danger' },
};
</script>

<template>
    <div class="flex items-start gap-3 rounded-button px-[18px] py-4" :class="tones[tone].box">
        <component
            :is="icon ?? tones[tone].icon"
            :size="20"
            class="mt-px shrink-0"
            :class="tones[tone].accent"
            aria-hidden="true"
        />
        <div class="flex flex-col gap-1 text-[14px] leading-[1.55]">
            <p v-if="title" class="font-extrabold" :class="tones[tone].accent">{{ title }}</p>
            <div class="text-text"><slot /></div>
        </div>
    </div>
</template>
