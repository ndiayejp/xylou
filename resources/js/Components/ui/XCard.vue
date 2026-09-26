<script setup lang="ts">
export type XCardSize = 'adult' | 'kid';
export type XCardPadding = 'md' | 'lg';

withDefaults(
    defineProps<{
        as?: string;
        size?: XCardSize;
        padding?: XCardPadding;
        interactive?: boolean;
    }>(),
    {
        as: 'div',
        size: 'adult',
        padding: 'md',
        interactive: false,
    },
);
</script>

<template>
    <component
        :is="as"
        class="bg-surface text-text"
        :class="[
            size === 'kid'
                ? 'overflow-hidden rounded-kid-card border-2 border-line font-kid'
                : ['rounded-card border border-line shadow-rest', padding === 'lg' ? 'p-5' : 'p-4'],
            interactive &&
                'transition duration-hover ease-out-expo hover:-translate-y-0.5 hover:shadow-card',
        ]"
    >
        <!-- Adulte : visuel arrondi dans la marge ; enfant : visuel bord à bord. -->
        <div
            v-if="$slots.media"
            :class="
                size === 'kid' ? 'h-[130px]' : 'mb-3.5 h-[120px] overflow-hidden rounded-[14px]'
            "
        >
            <slot name="media" />
        </div>
        <div v-if="size === 'kid'" class="flex flex-col gap-2.5 p-[18px]">
            <slot />
        </div>
        <slot v-else />
    </component>
</template>
