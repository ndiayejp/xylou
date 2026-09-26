<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import XCard from '@/Components/ui/XCard.vue';
import AccountSpace from '@/Layouts/AccountSpace.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head :title="$t('profile.title')" />

    <AccountSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">{{ $t('profile.title') }}</h1>
        </template>

        <div class="flex max-w-3xl flex-col gap-6">
            <XCard padding="lg">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </XCard>
            <XCard padding="lg">
                <UpdatePasswordForm class="max-w-xl" />
            </XCard>
            <XCard padding="lg">
                <DeleteUserForm class="max-w-xl" />
            </XCard>
        </div>
    </AccountSpace>
</template>
