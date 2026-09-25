<script setup lang="ts">
import type { Component } from 'vue';
import XField from './XField.vue';
import { controlClasses } from './field';

defineOptions({ inheritAttrs: false });

withDefaults(
    defineProps<{
        label: string;
        type?: 'text' | 'email' | 'password' | 'search' | 'tel' | 'url' | 'number';
        icon?: Component;
        hideLabel?: boolean;
        optionalLabel?: string;
        hint?: string;
        error?: string;
        id?: string;
    }>(),
    {
        type: 'text',
        icon: undefined,
        hideLabel: false,
        optionalLabel: undefined,
        hint: undefined,
        error: undefined,
        id: undefined,
    },
);

const model = defineModel<string | number | null>({ default: '' });
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
                <component
                    :is="icon"
                    v-if="icon"
                    :size="20"
                    aria-hidden="true"
                    class="pointer-events-none absolute left-3.5 top-3.5 text-subtle"
                />
                <input
                    :id="fieldId"
                    v-model="model"
                    :type="type"
                    v-bind="$attrs"
                    :aria-invalid="invalid || undefined"
                    :aria-describedby="describedBy"
                    :class="[controlClasses(invalid), 'h-12 pr-4', icon ? 'pl-11' : 'pl-4']"
                />
            </div>
        </template>
    </XField>
</template>
