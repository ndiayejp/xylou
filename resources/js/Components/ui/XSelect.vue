<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';
import XField from './XField.vue';
import { controlClasses } from './field';

export type XSelectOption = { value: string | number; label: string; disabled?: boolean };

defineOptions({ inheritAttrs: false });

withDefaults(
    defineProps<{
        label: string;
        options: XSelectOption[];
        placeholder?: string;
        hideLabel?: boolean;
        optionalLabel?: string;
        hint?: string;
        error?: string;
        id?: string;
    }>(),
    {
        placeholder: undefined,
        hideLabel: false,
        optionalLabel: undefined,
        hint: undefined,
        error: undefined,
        id: undefined,
    },
);

const model = defineModel<string | number | null>({ default: null });
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
            <div class="relative">
                <select
                    :id="fieldId"
                    v-model="model"
                    v-bind="$attrs"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :class="[controlClasses(invalid), 'h-12 appearance-none bg-none pl-4 pr-11']"
                >
                    <option v-if="placeholder" :value="null" disabled>{{ placeholder }}</option>
                    <option
                        v-for="option in options"
                        :key="option.value"
                        :value="option.value"
                        :disabled="option.disabled"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <ChevronDown
                    :size="20"
                    aria-hidden="true"
                    class="pointer-events-none absolute right-3.5 top-3.5 text-muted"
                />
            </div>
        </template>
    </XField>
</template>
