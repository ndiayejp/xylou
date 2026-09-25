<script setup lang="ts">
import { useId } from 'vue';

withDefaults(defineProps<{ label: string; hint?: string; disabled?: boolean }>(), {
    hint: undefined,
    disabled: false,
});

const model = defineModel<boolean>({ default: false });
const hintId = useId();
</script>

<template>
    <div class="flex flex-col">
        <label
            class="flex min-h-touch items-center gap-3"
            :class="disabled ? 'cursor-not-allowed' : 'cursor-pointer'"
        >
            <button
                type="button"
                role="switch"
                :aria-checked="model"
                :aria-describedby="hint ? hintId : undefined"
                :disabled="disabled"
                class="relative h-7 w-12 shrink-0 rounded-full transition-colors duration-hover disabled:opacity-50"
                :class="model ? 'bg-primary' : 'bg-switch'"
                @click="model = !model"
            >
                <span
                    class="absolute left-0.5 top-0.5 size-6 rounded-full bg-surface shadow transition-transform duration-hover"
                    :class="model && 'translate-x-5'"
                    aria-hidden="true"
                />
            </button>
            <span class="text-[14px] font-semibold text-text">{{ label }}</span>
        </label>
        <span v-if="hint" :id="hintId" class="-mt-2 pl-[60px] text-caption font-medium text-muted">
            {{ hint }}
        </span>
    </div>
</template>
