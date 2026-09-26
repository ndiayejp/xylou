<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { CalendarDays, DoorOpen, Users } from '@lucide/vue';
import { ref } from 'vue';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import { useChildren } from '@/Composables/useChildren';
import { elides } from '@/i18n/elision';
import ParentSpace from '@/Layouts/ParentSpace.vue';

const firstName = usePage().props.auth.user.name.split(' ')[0];
const { currentChild } = useChildren();

// Ouvre la session enfant sur cet appareil ; la session parent se ferme.
const opening = ref(false);
function openKidSpace(childId: number): void {
    router.post(
        route('parent.children.kid-session', childId),
        {},
        {
            onStart: () => (opening.value = true),
            onFinish: () => (opening.value = false),
        },
    );
}
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

        <div v-if="currentChild" class="mb-6">
            <XButton :icon="DoorOpen" :loading="opening" @click="openKidSpace(currentChild.id)">
                {{
                    $t(
                        elides(currentChild.firstName)
                            ? 'parent.dashboard.openKidSpaceElided'
                            : 'parent.dashboard.openKidSpace',
                        { name: currentChild.firstName },
                    )
                }}
            </XButton>
        </div>

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
