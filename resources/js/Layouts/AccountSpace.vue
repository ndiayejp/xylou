<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import ParentSpace from './ParentSpace.vue';
import ProSpace from './ProSpace.vue';

// Pages communes aux adultes (profil, paramètres) : l'espace suit le rôle du compte.
const isPro = usePage().props.auth.user.roles.includes('professional');
</script>

<template>
    <component :is="isPro ? ProSpace : ParentSpace">
        <template #header><slot name="header" /></template>
        <slot />
    </component>
</template>
