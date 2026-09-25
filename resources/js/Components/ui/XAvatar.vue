<script setup lang="ts">
import { computed } from 'vue';
import { avatar } from '@/design/tokens';

export type XAvatarColor = keyof typeof avatar;

const props = withDefaults(
    defineProps<{
        name: string;
        color?: XAvatarColor;
        size?: 'sm' | 'md' | 'lg';
        decorative?: boolean;
    }>(),
    { color: undefined, size: 'md', decorative: false },
);

const palette = Object.keys(avatar) as XAvatarColor[];

const colorClasses: Record<XAvatarColor, string> = {
    coral: 'bg-avatar-coral',
    teal: 'bg-avatar-teal',
    blue: 'bg-avatar-blue',
    indigo: 'bg-avatar-indigo',
    violet: 'bg-avatar-violet',
    pink: 'bg-avatar-pink',
};

const sizes = { sm: 'size-8 text-caption', md: 'size-10 text-[16px]', lg: 'size-14 text-[22px]' };

const initial = computed(() => (Array.from(props.name.trim())[0] ?? '?').toLocaleUpperCase('fr'));

// Sans couleur imposée, la même personne garde toujours la même couleur.
const resolvedColor = computed<XAvatarColor>(() => {
    if (props.color) {
        return props.color;
    }
    const sum = Array.from(props.name).reduce(
        (total, char) => total + (char.codePointAt(0) ?? 0),
        0,
    );

    return palette[sum % palette.length];
});
</script>

<template>
    <span
        class="inline-flex shrink-0 select-none items-center justify-center rounded-full font-extrabold text-white"
        :class="[sizes[size], colorClasses[resolvedColor]]"
        :role="decorative ? undefined : 'img'"
        :aria-label="decorative ? undefined : name"
        :aria-hidden="decorative || undefined"
    >
        {{ initial }}
    </span>
</template>
