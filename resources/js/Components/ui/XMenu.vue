<script setup lang="ts">
import { EllipsisVertical } from '@lucide/vue';
import { nextTick, onBeforeUnmount, ref, useId, type Component } from 'vue';

export type XMenuItem = { key: string; label: string; icon?: Component; danger?: boolean };

// Bouton de menu (motif ARIA « menu button ») : flèches, Début/Fin, Échap ; clic extérieur ferme.
const props = withDefaults(defineProps<{ label: string; items: XMenuItem[]; icon?: Component }>(), {
    icon: () => EllipsisVertical,
});

const emit = defineEmits<{ select: [key: string] }>();

const id = useId();
const open = ref(false);
const root = ref<HTMLElement>();
const trigger = ref<HTMLButtonElement>();
const entries = ref<HTMLButtonElement[]>([]);

function onOutside(event: PointerEvent): void {
    if (!root.value?.contains(event.target as Node)) {
        close(false);
    }
}

async function show(focusIndex = 0): Promise<void> {
    open.value = true;
    document.addEventListener('pointerdown', onOutside);
    await nextTick();
    focusAt(focusIndex);
}

function close(returnFocus = true): void {
    open.value = false;
    document.removeEventListener('pointerdown', onOutside);
    if (returnFocus) {
        trigger.value?.focus();
    }
}

function focusAt(index: number): void {
    const count = props.items.length;
    entries.value[(index + count) % count]?.focus();
}

function current(): number {
    return entries.value.findIndex((entry) => entry === document.activeElement);
}

function onTriggerKey(event: KeyboardEvent): void {
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        void show(event.key === 'ArrowDown' ? 0 : props.items.length - 1);
    }
}

function onMenuKey(event: KeyboardEvent): void {
    const moves: Record<string, () => void> = {
        ArrowDown: () => focusAt(current() + 1),
        ArrowUp: () => focusAt(current() - 1),
        Home: () => focusAt(0),
        End: () => focusAt(props.items.length - 1),
        Escape: () => close(),
    };

    if (event.key === 'Tab') {
        close(false);
    } else if (moves[event.key]) {
        event.preventDefault();
        moves[event.key]();
    }
}

function choose(key: string): void {
    close();
    emit('select', key);
}

onBeforeUnmount(() => document.removeEventListener('pointerdown', onOutside));
</script>

<template>
    <div ref="root" class="relative inline-flex">
        <button
            ref="trigger"
            type="button"
            :aria-label="label"
            :title="label"
            aria-haspopup="menu"
            :aria-expanded="open"
            :aria-controls="open ? id : undefined"
            class="inline-flex size-11 items-center justify-center rounded-input text-muted transition duration-hover hover:bg-tint hover:text-primary-strong"
            @click="open ? close() : show()"
            @keydown="onTriggerKey"
        >
            <component :is="icon" :size="20" aria-hidden="true" />
        </button>
        <ul
            v-if="open"
            :id="id"
            role="menu"
            :aria-label="label"
            class="absolute right-0 top-full z-20 mt-1 min-w-[200px] rounded-input border border-line bg-surface p-1.5 shadow-lift"
            @keydown="onMenuKey"
        >
            <li v-for="item in items" :key="item.key" role="none">
                <button
                    ref="entries"
                    type="button"
                    role="menuitem"
                    tabindex="-1"
                    :class="[
                        'flex w-full items-center gap-2.5 rounded-[10px] px-3 py-2.5 text-left text-[14px] font-semibold hover:bg-tint focus:bg-tint',
                        item.danger ? 'text-danger' : 'text-text',
                    ]"
                    @click="choose(item.key)"
                >
                    <component :is="item.icon" v-if="item.icon" :size="18" aria-hidden="true" />
                    {{ item.label }}
                </button>
            </li>
        </ul>
    </div>
</template>
