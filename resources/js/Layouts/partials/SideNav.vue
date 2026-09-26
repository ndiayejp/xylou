<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { NavItem } from '../navigation';

// Liste verticale des barres latérales adultes (parent, pro).
defineProps<{ items: NavItem[] }>();
</script>

<template>
    <ul class="flex flex-col gap-1">
        <li v-for="item in items" :key="item.href">
            <Link
                :href="item.href"
                :aria-current="item.current ? 'page' : undefined"
                class="flex min-h-touch items-center gap-3 rounded-input px-3.5 text-[15px] transition-colors duration-hover"
                :class="
                    item.current
                        ? 'bg-tint font-bold text-primary-strong'
                        : 'font-semibold text-muted hover:bg-bg hover:text-text'
                "
            >
                <component :is="item.icon" :size="20" class="shrink-0" aria-hidden="true" />
                {{ $t(item.label) }}
            </Link>
        </li>
    </ul>
</template>
