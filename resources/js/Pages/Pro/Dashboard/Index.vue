<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { UserRoundSearch } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import ProSpace from '@/Layouts/ProSpace.vue';

const firstName = usePage().props.auth.user.name.split(' ')[0];
</script>

<template>
    <Head :title="$t('nav.adult.home')" />

    <ProSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">{{ $t('pro.dashboard.title', { name: firstName }) }}</h1>
        </template>

        <XCard padding="lg" class="flex min-h-[300px] items-center justify-center">
            <XEmptyState
                :icon="UserRoundSearch"
                tone="neutral"
                :title="$t('pro.dashboard.emptyTitle')"
                :description="$t('pro.dashboard.emptyText')"
            />
        </XCard>
    </ProSpace>
</template>
