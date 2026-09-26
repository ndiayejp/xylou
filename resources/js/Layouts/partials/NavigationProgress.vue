<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

// Remplace la barre d'Inertia (role="bar", rôle ARIA invalide) : décorative, n'apparaît
// qu'au-delà de 250 ms pour ne pas clignoter sur les navigations rapides.
const DELAY = 250;

const visible = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;

const stopStart = router.on('start', () => {
    timer = setTimeout(() => (visible.value = true), DELAY);
});
const stopFinish = router.on('finish', () => {
    clearTimeout(timer);
    visible.value = false;
});

onBeforeUnmount(() => {
    clearTimeout(timer);
    stopStart();
    stopFinish();
});
</script>

<template>
    <div
        v-if="visible"
        class="fixed inset-x-0 top-0 z-[60] h-1 overflow-hidden bg-tint"
        aria-hidden="true"
        data-navigation-progress
    >
        <div class="h-full w-1/3 animate-navigation bg-primary" />
    </div>
</template>
