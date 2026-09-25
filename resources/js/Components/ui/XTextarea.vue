<script setup lang="ts">
import XField from './XField.vue';
import { controlClasses } from './field';

defineOptions({ inheritAttrs: false });

withDefaults(
    defineProps<{
        label: string;
        rows?: number;
        hideLabel?: boolean;
        optionalLabel?: string;
        hint?: string;
        error?: string;
        id?: string;
    }>(),
    {
        rows: 4,
        hideLabel: false,
        optionalLabel: undefined,
        hint: undefined,
        error: undefined,
        id: undefined,
    },
);

const model = defineModel<string | null>({ default: '' });
</script>

<template>
    <XField
        :id="id"
        :label="label"
        :hide-label="hideLabel"
        :optional-label="optionalLabel"
        :hint="hint"
        :error="error"
    >
        <template #default="{ id: fieldId, describedBy, invalid }">
            <textarea
                :id="fieldId"
                v-model="model"
                :rows="rows"
                v-bind="$attrs"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :class="[controlClasses(invalid), 'resize-y px-4 py-3.5 leading-[1.5]']"
            />
        </template>
    </XField>
</template>
