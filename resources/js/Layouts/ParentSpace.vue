<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useParentNavigation } from '@/Composables/useSpaceNavigation';
import ParentLayout from './ParentLayout.vue';

// ParentLayout alimenté par la session : utilisateur connecté et navigation de l'espace.
// Les enfants arriveront avec le domaine Children.
const user = usePage().props.auth.user;
const navigation = useParentNavigation();
</script>

<template>
    <ParentLayout v-bind="navigation" :user="{ name: user.name }" :children="[]">
        <template #header><slot name="header" /></template>
        <template #actions><slot name="actions" /></template>
        <slot />
    </ParentLayout>
</template>
