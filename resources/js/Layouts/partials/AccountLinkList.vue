<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { AccountLink } from '../navigation';

defineProps<{ links: AccountLink[] }>();
defineEmits<{ navigate: [] }>();
</script>

<template>
    <ul class="flex flex-col">
        <li v-for="link in links" :key="link.label">
            <Link
                :href="link.href"
                :method="link.method ?? 'get'"
                :as="link.method === 'post' ? 'button' : 'a'"
                class="flex min-h-touch w-full items-center rounded-input px-3.5 text-left text-[15px] font-semibold text-text hover:bg-bg"
                @click="$emit('navigate')"
            >
                {{ $t(link.label) }}
            </Link>
        </li>
    </ul>
</template>
