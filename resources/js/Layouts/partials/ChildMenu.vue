<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, useId } from 'vue';
import XAvatar from '@/Components/ui/XAvatar.vue';
import XChildSwitcher, { type XChildSwitcherItem } from '@/Components/ui/XChildSwitcher.vue';

const props = defineProps<{
    items: XChildSwitcherItem[];
    currentId?: string | number;
    variant: 'sidebar' | 'rail' | 'pill';
    canAdd?: boolean;
    align?: 'start' | 'end';
}>();

const emit = defineEmits<{ switch: [id: string | number]; add: [] }>();

const open = ref(false);
const root = ref<HTMLElement>();
const panelId = useId();
const current = computed(() => props.items.find((child) => child.id === props.currentId));

function select(id: string | number | undefined): void {
    open.value = false;
    if (id !== undefined && id !== props.currentId) {
        emit('switch', id);
    }
}

function add(): void {
    open.value = false;
    emit('add');
}

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
            :aria-controls="panelId"
            :aria-label="
                current
                    ? $t('layout.currentChild', { name: current.name })
                    : $t('layout.changeChild')
            "
            class="flex items-center transition-colors duration-hover"
            :class="{
                'w-full gap-3 rounded-button border-[1.5px] border-line bg-surface px-3 py-2.5 text-left hover:bg-bg':
                    variant === 'sidebar',
                'rounded-full p-1': variant === 'rail',
                'h-10 gap-2 rounded-full border-[1.5px] border-line bg-surface pl-1 pr-2.5 text-[14px] font-extrabold':
                    variant === 'pill',
            }"
            @click="open = !open"
        >
            <XAvatar
                v-if="current"
                :name="current.name"
                :color="current.color"
                :size="variant === 'pill' ? 'sm' : 'md'"
                decorative
                :class="
                    variant === 'rail' && 'ring-2 ring-surface ring-offset-2 ring-offset-primary'
                "
            />
            <span v-if="variant === 'sidebar'" class="grow">
                <span class="block text-[15px] font-extrabold text-text">{{ current?.name }}</span>
                <span v-if="current?.details" class="block text-[12px] text-muted">
                    {{ current.details }}
                </span>
            </span>
            <span v-if="variant === 'pill'">{{ current?.name }}</span>
            <ChevronDown
                v-if="variant !== 'rail'"
                :size="16"
                class="shrink-0 text-muted"
                aria-hidden="true"
            />
        </button>

        <div
            v-if="open"
            :id="panelId"
            class="absolute z-50"
            :class="
                variant === 'rail'
                    ? 'left-full top-0 ml-3'
                    : ['top-full mt-2', align === 'end' ? 'right-0' : 'left-0']
            "
        >
            <XChildSwitcher
                :model-value="currentId"
                :items="items"
                :label="$t('layout.changeChild')"
                :add-label="canAdd ? $t('layout.addChild') : undefined"
                class="shadow-card"
                @update:model-value="select"
                @add="add"
            />
        </div>
    </div>
</template>
