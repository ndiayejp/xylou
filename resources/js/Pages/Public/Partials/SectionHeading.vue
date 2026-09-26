<script setup lang="ts">
withDefaults(
    defineProps<{ id: string; eyebrow?: string; title: string; text?: string; center?: boolean }>(),
    { eyebrow: undefined, text: undefined, center: false },
);
</script>

<template>
    <div class="flex max-w-3xl flex-col gap-3" :class="center && 'mx-auto items-center text-center'">
        <p
            v-if="eyebrow"
            class="text-[12px] font-extrabold uppercase tracking-[.08em] text-primary-text"
        >
            {{ eyebrow }}
        </p>
        <h2 :id="id" class="text-[30px] font-extrabold leading-[1.2] tracking-[-0.02em] md:text-[38px]">
            {{ title }}
        </h2>
        <p v-if="text" class="text-[17px] leading-[1.55] text-muted">{{ text }}</p>
    </div>
</template>
