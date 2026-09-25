<script setup lang="ts">
import { CircleAlert } from '@lucide/vue';
import { computed, useId } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        hideLabel?: boolean;
        optionalLabel?: string;
        hint?: string;
        error?: string;
        id?: string;
        as?: 'label' | 'span';
    }>(),
    {
        hideLabel: false,
        optionalLabel: undefined,
        hint: undefined,
        error: undefined,
        id: undefined,
        as: 'label',
    },
);

defineSlots<{
    default(props: {
        id: string;
        labelId: string;
        describedBy: string | undefined;
        invalid: boolean;
    }): unknown;
}>();

const generatedId = useId();
const fieldId = computed(() => props.id ?? generatedId);
const labelId = computed(() => `${fieldId.value}-label`);
const messageId = computed(() => `${fieldId.value}-message`);
const message = computed(() => props.error ?? props.hint);
const slotProps = computed(() => ({
    id: fieldId.value,
    labelId: labelId.value,
    describedBy: message.value ? messageId.value : undefined,
    invalid: Boolean(props.error),
}));
</script>

<template>
    <div class="flex flex-col gap-2">
        <component
            :is="as"
            :id="labelId"
            :for="as === 'label' ? fieldId : undefined"
            :class="hideLabel ? 'sr-only' : 'text-[14px] font-bold text-text'"
        >
            {{ label }}
            <span v-if="optionalLabel" class="font-medium text-muted">{{ optionalLabel }}</span>
        </component>

        <slot v-bind="slotProps" />

        <p v-if="error" :id="messageId" class="flex items-center gap-1.5 text-caption text-danger">
            <CircleAlert :size="16" aria-hidden="true" class="shrink-0" />
            {{ error }}
        </p>
        <p v-else-if="hint" :id="messageId" class="text-caption font-medium text-muted">
            {{ hint }}
        </p>
    </div>
</template>
