<script setup lang="ts">
import type { Component } from 'vue';
import { computed } from 'vue';

export type XIconButtonVariant = 'outline' | 'soft' | 'ghost';

const props = withDefaults(
    defineProps<{
        icon: Component;
        label: string;
        variant?: XIconButtonVariant;
        size?: 'md' | 'kid';
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        pressed?: boolean;
    }>(),
    {
        variant: 'outline',
        size: 'md',
        type: 'button',
        disabled: false,
        pressed: undefined,
    },
);

defineEmits<{ click: [event: MouseEvent] }>();

const variants: Record<XIconButtonVariant, string> = {
    outline: 'border-[1.5px] border-line bg-surface text-text hover:border-primary',
    soft: 'bg-tint text-primary-strong',
    ghost: 'bg-transparent text-muted hover:bg-tint hover:text-primary-strong',
};

const classes = computed(() => [
    'inline-flex shrink-0 items-center justify-center transition duration-hover ease-out',
    props.size === 'kid' ? 'size-16 rounded-card' : 'size-11 rounded-input',
    props.disabled
        ? 'cursor-not-allowed bg-disabled text-disabled-text'
        : [
              variants[props.variant],
              'hover:-translate-y-px hover:shadow-lift active:translate-y-0 active:scale-[.98]',
          ],
]);
</script>

<template>
    <button
        :type="type"
        :class="classes"
        :disabled="disabled"
        :aria-label="label"
        :aria-pressed="pressed"
        :title="label"
        @click="$emit('click', $event)"
    >
        <component
            :is="icon"
            :size="size === 'kid' ? 26 : 20"
            :stroke-width="2"
            aria-hidden="true"
        />
    </button>
</template>
