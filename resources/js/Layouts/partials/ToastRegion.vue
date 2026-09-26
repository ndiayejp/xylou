<script setup lang="ts">
import XToast from '@/Components/ui/XToast.vue';
import { useToasts, type Toast } from '@/Composables/useToasts';

const { toasts, dismiss } = useToasts();

function runAction(toast: Toast): void {
    toast.onAction?.();
    dismiss(toast.id);
}
</script>

<template>
    <section
        :aria-label="$t('layout.notificationsRegion')"
        aria-live="polite"
        class="pointer-events-none fixed inset-x-4 bottom-24 z-50 flex flex-col items-center gap-2 md:bottom-6"
    >
        <XToast
            v-for="toast in toasts"
            :key="toast.id"
            class="pointer-events-auto"
            :icon="toast.icon"
            :variant="toast.variant"
            :action-label="toast.actionLabel"
            @action="runAction(toast)"
        >
            {{ toast.message }}
        </XToast>
    </section>
</template>
