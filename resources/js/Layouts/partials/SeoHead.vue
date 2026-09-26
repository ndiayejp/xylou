<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// SEO de base des pages publiques : titre, description, URL canonique, aperçu de partage.
const props = defineProps<{ title: string; description: string }>();

const page = usePage();
const canonical = computed(() =>
    typeof window === 'undefined' ? undefined : new URL(page.url, window.location.origin).href,
);
const fullTitle = computed(() => `${props.title} - Xylou`);
</script>

<template>
    <Head :title="title">
        <meta head-key="description" name="description" :content="description" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:site_name" property="og:site_name" content="Xylou" />
        <meta head-key="og:title" property="og:title" :content="fullTitle" />
        <meta head-key="og:description" property="og:description" :content="description" />
        <meta v-if="canonical" head-key="og:url" property="og:url" :content="canonical" />
        <link v-if="canonical" head-key="canonical" rel="canonical" :href="canonical" />
    </Head>
</template>
