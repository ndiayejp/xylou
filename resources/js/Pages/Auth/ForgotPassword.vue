<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Mail } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout
        :title="$t('auth.forgot.title')"
        :description="$t('auth.forgot.description')"
        :status="status"
    >
        <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
            <XInput
                v-model="form.email"
                type="email"
                :label="$t('auth.fields.email')"
                :icon="Mail"
                :error="form.errors.email"
                required
                autofocus
                autocomplete="username"
            />
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('auth.forgot.submit') }}
            </XButton>
        </form>

        <template #footer>
            <Link :href="route('login')" class="link">{{ $t('auth.forgot.back') }}</Link>
        </template>
    </AuthLayout>
</template>
