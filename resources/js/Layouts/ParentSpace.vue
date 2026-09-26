<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useChildren } from '@/Composables/useChildren';
import { useParentNavigation } from '@/Composables/useSpaceNavigation';
import ParentLayout from './ParentLayout.vue';

// ParentLayout alimenté par la session : utilisateur connecté, enfants et navigation de l'espace.
const user = usePage().props.auth.user;
const navigation = useParentNavigation();
const { switcherItems, currentChildId, switchTo } = useChildren();
</script>

<template>
    <ParentLayout
        v-bind="navigation"
        :user="{ name: user.name }"
        :children="switcherItems"
        :current-child-id="currentChildId"
        @switch-child="switchTo"
    >
        <template #header><slot name="header" /></template>
        <template #actions><slot name="actions" /></template>
        <slot />
    </ParentLayout>
</template>
