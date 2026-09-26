<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useProNavigation } from '@/Composables/useSpaceNavigation';
import ProLayout from './ProLayout.vue';

const user = usePage().props.auth.user;
const navigation = useProNavigation();
</script>

<template>
    <ProLayout v-bind="navigation" :user="{ name: user.name }">
        <template #header><slot name="header" /></template>
        <template #actions><slot name="actions" /></template>
        <slot />
    </ProLayout>
</template>
