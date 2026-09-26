<script setup lang="ts">
export type XSkeletonShape = 'text' | 'block' | 'circle';

// Décoratif : la zone qui charge porte elle-même aria-busy et un texte pour les lecteurs d'écran.
withDefaults(defineProps<{ shape?: XSkeletonShape }>(), { shape: 'text' });

const shapes: Record<XSkeletonShape, string> = {
    text: 'h-3 rounded-tag',
    block: 'h-[110px] rounded-[14px]',
    circle: 'size-10 rounded-full',
};
</script>

<template>
    <div
        class="animate-shimmer bg-[linear-gradient(90deg,theme(colors.track)_0%,theme(colors.bg)_50%,theme(colors.track)_100%)] bg-[length:200%_100%]"
        :class="shapes[shape]"
        aria-hidden="true"
    />
</template>
