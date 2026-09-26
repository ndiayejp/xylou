<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import XCallout from '@/Components/ui/XCallout.vue';
import XCard from '@/Components/ui/XCard.vue';
import XLogo from '@/Components/ui/XLogo.vue';
import NavigationProgress from './partials/NavigationProgress.vue';
import SkipLink from './partials/SkipLink.vue';

// Pages d'authentification : une carte centrée, un titre, un message de statut éventuel.
withDefaults(
    defineProps<{ title: string; description?: string; status?: string; homeHref?: string }>(),
    { description: undefined, status: undefined, homeHref: '/' },
);
</script>

<template>
    <Head :title="title" />
    <div
        class="flex min-h-screen flex-col items-center bg-bg px-4 py-10 text-text sm:justify-center"
    >
        <SkipLink />
        <NavigationProgress />
        <Link :href="homeHref" :aria-label="$t('layout.homeLink')" class="mb-8 rounded-input">
            <XLogo size="lg" />
        </Link>
        <main id="contenu" class="w-full max-w-md">
            <XCard padding="lg" class="flex flex-col gap-6 sm:p-8">
                <div class="flex flex-col gap-2">
                    <h1 class="text-h2">{{ title }}</h1>
                    <p v-if="description" class="text-body text-muted">{{ description }}</p>
                </div>
                <XCallout v-if="status" tone="ok" role="status">{{ status }}</XCallout>
                <slot />
            </XCard>
            <div v-if="$slots.footer" class="mt-6 text-center text-[14px] text-muted">
                <slot name="footer" />
            </div>
        </main>
    </div>
</template>
