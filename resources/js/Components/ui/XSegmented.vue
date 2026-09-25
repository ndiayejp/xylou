<script setup lang="ts" generic="T extends string | number">
import { nextTick, ref } from 'vue';

export type XSegmentedOption<V> = { value: V; label: string };

const props = withDefaults(
    defineProps<{
        label: string;
        options: XSegmentedOption<T>[];
        hideLabel?: boolean;
    }>(),
    { hideLabel: true },
);

const model = defineModel<T>({ required: true });
const buttons = ref<HTMLButtonElement[]>([]);

async function move(from: number, step: number): Promise<void> {
    const index = (from + step + props.options.length) % props.options.length;
    model.value = props.options[index].value;
    await nextTick();
    buttons.value[index]?.focus();
}

function isSelected(option: XSegmentedOption<T>, index: number): boolean {
    return (
        model.value === option.value ||
        (index === 0 && !props.options.some((o) => o.value === model.value))
    );
}
</script>

<template>
    <div class="flex flex-col gap-2">
        <span v-if="!hideLabel" class="text-[14px] font-bold text-text" aria-hidden="true">{{
            label
        }}</span>
        <div role="radiogroup" :aria-label="label" class="flex gap-1 rounded-[14px] bg-well p-1">
            <button
                v-for="(option, index) in options"
                :key="option.value"
                ref="buttons"
                type="button"
                role="radio"
                :aria-checked="model === option.value"
                :tabindex="isSelected(option, index) ? 0 : -1"
                class="h-9 min-w-0 flex-1 truncate rounded-[10px] px-3 text-[14px] font-bold transition-colors duration-hover"
                :class="
                    model === option.value
                        ? 'bg-surface text-text shadow'
                        : 'text-muted hover:text-text'
                "
                @click="model = option.value"
                @keydown.right.prevent="move(index, 1)"
                @keydown.down.prevent="move(index, 1)"
                @keydown.left.prevent="move(index, -1)"
                @keydown.up.prevent="move(index, -1)"
            >
                {{ option.label }}
            </button>
        </div>
    </div>
</template>
