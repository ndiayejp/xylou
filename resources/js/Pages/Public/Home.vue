<script setup lang="ts">
import PublicSpace from '@/Layouts/PublicSpace.vue';
import SeoHead from '@/Layouts/partials/SeoHead.vue';
import ExampleTrustSections from './Partials/ExampleTrustSections.vue';
import HeroSection from './Partials/HeroSection.vue';
import HowAudienceSections from './Partials/HowAudienceSections.vue';
import PromiseDemoSections from './Partials/PromiseDemoSections.vue';
import VoicesFaqSections from './Partials/VoicesFaqSections.vue';

defineProps<{ contactEmail: string }>();

const registerHref = route('register');
</script>

<template>
    <SeoHead :title="$t('landing.meta.title')" :description="$t('landing.meta.description')" />

    <PublicSpace :contact-email="contactEmail">
        <HeroSection :register-href="registerHref" />
        <PromiseDemoSections />
        <HowAudienceSections />
        <ExampleTrustSections />
        <VoicesFaqSections :register-href="registerHref" :contact-email="contactEmail" />
    </PublicSpace>
</template>
