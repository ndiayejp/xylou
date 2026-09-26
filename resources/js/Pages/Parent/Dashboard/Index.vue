<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Users } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import ParentSpace from '@/Layouts/ParentSpace.vue';

const firstName = usePage().props.auth.user.name.split(' ')[0];
</script>

<template>
    <Head :title="$t('nav.adult.home')" />

    <ParentSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">
                {{ $t('parent.dashboard.title', { name: firstName }) }}
            </h1>
        </template>

        <XCard padding="lg" class="flex min-h-[300px] items-center justify-center">
            <XEmptyState
                :icon="Users"
                :title="$t('parent.dashboard.emptyTitle')"
                :description="$t('parent.dashboard.emptyText')"
            />
        </XCard>
    </ParentSpace>
</template>
