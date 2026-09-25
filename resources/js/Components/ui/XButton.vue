<script setup lang="ts">
import type { Component } from 'vue';
import { computed } from 'vue';

export type XButtonVariant =
    'primary' | 'secondary' | 'soft' | 'ghost' | 'accent' | 'success' | 'danger';
export type XButtonSize = 'sm' | 'md' | 'lg' | 'kid';

const props = withDefaults(
    defineProps<{
        variant?: XButtonVariant;
        size?: XButtonSize;
        type?: 'button' | 'submit' | 'reset';
        icon?: Component;
        loading?: boolean;
        disabled?: boolean;
        block?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        type: 'button',
        icon: undefined,
        loading: false,
        disabled: false,
        block: false,
    },
);

defineEmits<{ click: [event: MouseEvent] }>();

const variants: Record<XButtonVariant, string> = {
    primary: 'bg-primary text-white hover:bg-primary-text',
    secondary: 'border-[1.5px] border-line bg-surface text-text hover:border-primary',
    soft: 'bg-tint text-primary-strong',
    ghost: 'bg-transparent text-primary-strong hover:bg-tint',
    accent: 'bg-accent text-text',
    success: 'bg-success-text text-white',
    danger: 'border-[1.5px] border-danger-line bg-surface text-danger',
};

const sizes: Record<XButtonSize, string> = {
    sm: 'min-h-touch px-4 text-caption rounded-input',
    md: 'h-12 px-5 text-body font-bold rounded-button',
    lg: 'h-14 px-6 text-h3 rounded-button',
    kid: 'min-h-kid-touch px-7 font-kid text-kid-button rounded-card',
};

const iconSizes: Record<XButtonSize, number> = { sm: 16, md: 19, lg: 20, kid: 24 };

const inactive = computed(() => props.disabled || props.loading);

const classes = computed(() => [
    'inline-flex items-center justify-center gap-2 whitespace-nowrap transition duration-hover ease-out',
    sizes[props.size],
    props.block && 'w-full',
    props.disabled
        ? 'cursor-not-allowed bg-disabled text-disabled-text'
        : [
              variants[props.variant],
              !props.loading &&
                  'hover:-translate-y-px hover:shadow-lift active:translate-y-0 active:scale-[.98]',
          ],
    props.loading && 'cursor-progress opacity-85',
]);
</script>

<template>
    <button
        :type="type"
        :class="classes"
        :disabled="inactive"
        :aria-busy="loading || undefined"
        @click="$emit('click', $event)"
    >
        <span
            v-if="loading"
            data-test="spinner"
            class="block size-4 shrink-0 animate-spin rounded-full border-[2.5px] border-current border-t-transparent"
            aria-hidden="true"
        />
        <component
            :is="icon"
            v-else-if="icon"
            :size="iconSizes[size]"
            :stroke-width="2"
            class="shrink-0"
            aria-hidden="true"
        />
        <slot />
    </button>
</template>
