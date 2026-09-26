<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Rocket } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import XMascot from '@/Components/ui/XMascot.vue';
import KidSpace from '@/Layouts/KidSpace.vue';

const firstName = usePage().props.kid?.firstName ?? '';
</script>

<template>
    <Head :title="$t('nav.kid.home')" />

    <KidSpace>
        <div class="flex flex-col gap-6 px-4 pb-8 pt-4 md:px-8">
            <div class="flex items-center gap-4">
                <XMascot mood="happy" :size="72" animated />
                <h1 class="text-kid-title">{{ $t('kid.home.greeting', { name: firstName }) }}</h1>
            </div>
            <XCard size="kid" class="flex min-h-[280px] items-center justify-center p-6">
                <XEmptyState
                    :icon="Rocket"
                    size="kid"
                    :title="$t('kid.home.emptyTitle')"
                    :description="$t('kid.home.emptyText')"
                />
            </XCard>
        </div>
    </KidSpace>
</template>
