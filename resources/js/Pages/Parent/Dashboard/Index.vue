<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarDays, Users } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import { useChildren } from '@/Composables/useChildren';
import { elides } from '@/i18n/elision';
import ParentSpace from '@/Layouts/ParentSpace.vue';

const firstName = usePage().props.auth.user.name.split(' ')[0];
const { currentChild } = useChildren();
</script>

<template>
    <Head :title="$t('nav.adult.home')" />

    <ParentSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">
                {{ $t('parent.dashboard.title', { name: firstName }) }}
            </h1>
            <p v-if="currentChild" class="mt-1 text-[14px] text-muted">
                {{
                    $t(
                        elides(currentChild.firstName)
                            ? 'parent.dashboard.weekElided'
                            : 'parent.dashboard.week',
                        { name: currentChild.firstName },
                    )
                }}
            </p>
        </template>

        <XCard padding="lg" class="flex min-h-[300px] items-center justify-center">
            <XEmptyState
                v-if="currentChild"
                :icon="CalendarDays"
                tone="blue"
                :title="$t('parent.dashboard.noActivityTitle')"
                :description="$t('parent.dashboard.noActivityText')"
            />
            <XEmptyState
                v-else
                :icon="Users"
                :title="$t('parent.dashboard.emptyTitle')"
                :description="$t('parent.dashboard.emptyText')"
            />
        </XCard>
    </ParentSpace>
</template>
