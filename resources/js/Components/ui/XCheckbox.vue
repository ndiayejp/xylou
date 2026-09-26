<script setup lang="ts">
import { CircleAlert } from '@lucide/vue';
import { computed, useId } from 'vue';

// Case à cocher native ; le libellé passe par le slot (il peut contenir des liens).
const props = withDefaults(defineProps<{ error?: string; id?: string; disabled?: boolean }>(), {
    error: undefined,
    id: undefined,
    disabled: false,
});

const model = defineModel<boolean>({ default: false });
const generatedId = useId();
const inputId = computed(() => props.id ?? generatedId);
const errorId = computed(() => `${inputId.value}-erreur`);
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <div class="flex items-start gap-3">
            <input
                :id="inputId"
                v-model="model"
                type="checkbox"
                :disabled="disabled"
                :aria-invalid="error ? true : undefined"
                :aria-describedby="error ? errorId : undefined"
                class="mt-0.5 size-5 shrink-0 cursor-pointer rounded-[6px] border-[1.5px] text-primary transition-colors duration-hover focus:ring-0 disabled:cursor-not-allowed disabled:opacity-50"
                :class="error ? 'border-danger' : 'border-subtle'"
            />
            <label :for="inputId" class="cursor-pointer text-[14px] leading-[1.5] text-text">
                <slot />
            </label>
        </div>
        <p
            v-if="error"
            :id="errorId"
            class="flex items-center gap-1.5 pl-8 text-caption text-danger"
        >
            <CircleAlert :size="16" class="shrink-0" aria-hidden="true" />
            {{ error }}
        </p>
    </div>
</template>
