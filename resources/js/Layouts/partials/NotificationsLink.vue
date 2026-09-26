<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Bell } from '@lucide/vue';

withDefaults(defineProps<{ href: string; unread?: number }>(), { unread: 0 });
</script>

<template>
    <Link
        :href="href"
        :aria-label="$t('layout.notifications', unread)"
        class="relative inline-flex size-10 items-center justify-center rounded-input border-[1.5px] border-line bg-surface text-text transition-colors duration-hover hover:bg-bg md:size-11"
    >
        <Bell :size="20" aria-hidden="true" />
        <span
            v-if="unread > 0"
            class="absolute right-2 top-2 size-[9px] rounded-full border-2 border-surface bg-danger"
            aria-hidden="true"
        />
    </Link>
</template>
