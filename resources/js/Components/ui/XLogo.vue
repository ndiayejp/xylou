<script setup lang="ts">
// Le nom de marque n'est pas traduit ; le lien qui entoure le logo porte son propre libellé.
withDefaults(defineProps<{ size?: 'sm' | 'md' | 'lg'; inverse?: boolean; compact?: boolean }>(), {
    size: 'md',
    inverse: false,
    compact: false,
});

const sizes = {
    sm: { mark: 25, text: 'text-[20px]' },
    md: { mark: 30, text: 'text-[24px]' },
    lg: { mark: 34, text: 'text-[26px]' },
};
</script>

<template>
    <span class="inline-flex items-center gap-2.5" aria-hidden="true">
        <svg :width="sizes[size].mark" :height="sizes[size].mark" viewBox="0 0 40 40" class="block">
            <rect x="2" y="2" width="36" height="36" rx="12" class="fill-primary" />
            <path
                d="M12 13l16 14M28 13l-16 14"
                class="stroke-surface"
                fill="none"
                stroke-width="4.5"
                stroke-linecap="round"
            />
            <circle cx="31" cy="9" r="5" class="fill-accent" />
        </svg>
        <span
            v-if="!compact"
            class="font-sans font-extrabold tracking-[-0.03em]"
            :class="[sizes[size].text, inverse ? 'text-surface' : 'text-text']"
        >
            xylou
        </span>
    </span>
</template>
