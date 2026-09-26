<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, useId } from 'vue';
import XAvatar from '@/Components/ui/XAvatar.vue';
import type { AccountLink, LayoutUser } from '../navigation';
import AccountLinkList from './AccountLinkList.vue';

defineProps<{ user: LayoutUser; links: AccountLink[] }>();

const open = ref(false);
const root = ref<HTMLElement>();
const menuId = useId();

function onPointerDown(event: PointerEvent): void {
    if (open.value && !root.value?.contains(event.target as Node)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('pointerdown', onPointerDown));
onBeforeUnmount(() => document.removeEventListener('pointerdown', onPointerDown));
</script>

<template>
    <div ref="root" class="relative" @keydown.esc="open = false">
        <button
            type="button"
            :aria-expanded="open"
            :aria-controls="menuId"
            :aria-label="$t('layout.accountMenu')"
            class="rounded-full"
            @click="open = !open"
        >
            <XAvatar :name="user.name" :color="user.color" decorative />
        </button>
        <div
            v-if="open"
            :id="menuId"
            class="absolute right-0 top-full z-50 mt-2 w-56 rounded-card border border-line bg-surface p-2 shadow-card"
        >
            <p class="truncate px-3.5 py-2 text-caption text-muted">{{ user.name }}</p>
            <AccountLinkList :links="links" @navigate="open = false" />
        </div>
    </div>
</template>
