<script setup lang="ts">
import { Check, Plus } from '@lucide/vue';
import { useId } from 'vue';
import XAvatar, { type XAvatarColor } from './XAvatar.vue';

export interface XChildSwitcherItem {
    id: string | number;
    name: string;
    details?: string;
    color?: XAvatarColor;
}

defineProps<{ items: XChildSwitcherItem[]; label: string; addLabel?: string }>();

const model = defineModel<string | number>();

defineEmits<{ add: [] }>();

const labelId = useId();

const itemClass =
    'flex w-full items-center gap-3 rounded-[14px] p-2.5 text-left transition-colors duration-hover';
</script>

<template>
    <div
        class="flex w-[260px] flex-col gap-1.5 rounded-card border border-line bg-surface p-2.5 shadow-rest"
    >
        <p
            :id="labelId"
            class="px-2 py-1 text-[12px] font-extrabold uppercase tracking-[.06em] text-muted"
        >
            {{ label }}
        </p>
        <ul role="list" :aria-labelledby="labelId" class="flex flex-col gap-1.5">
            <li v-for="child in items" :key="child.id">
                <button
                    type="button"
                    :aria-pressed="model === child.id"
                    :class="[itemClass, model === child.id ? 'bg-tint' : 'hover:bg-bg']"
                    @click="model = child.id"
                >
                    <XAvatar :name="child.name" :color="child.color" decorative />
                    <span class="grow">
                        <span class="block text-body !font-bold text-text">{{ child.name }}</span>
                        <span
                            v-if="child.details"
                            class="block text-caption !font-medium text-muted"
                        >
                            {{ child.details }}
                        </span>
                    </span>
                    <Check
                        v-if="model === child.id"
                        :size="18"
                        class="shrink-0 text-primary-text"
                        aria-hidden="true"
                    />
                </button>
            </li>
        </ul>
        <template v-if="addLabel">
            <div class="my-1 h-px bg-line" />
            <button
                type="button"
                :class="[itemClass, 'gap-2.5 text-[14px] font-bold text-primary-text hover:bg-bg']"
                @click="$emit('add')"
            >
                <Plus :size="18" aria-hidden="true" />
                {{ addLabel }}
            </button>
        </template>
    </div>
</template>
