<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Lock, Mail } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.reset.title')" :description="$t('auth.reset.description')">
        <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
            <XInput
                v-model="form.email"
                type="email"
                :label="$t('auth.fields.email')"
                :icon="Mail"
                :error="form.errors.email"
                required
                autocomplete="username"
            />
            <XInput
                v-model="form.password"
                type="password"
                :label="$t('auth.fields.newPassword')"
                :icon="Lock"
                :hint="$t('auth.fields.passwordHint')"
                :error="form.errors.password"
                required
                autofocus
                autocomplete="new-password"
            />
            <XInput
                v-model="form.password_confirmation"
                type="password"
                :label="$t('auth.fields.passwordConfirmation')"
                :icon="Lock"
                :error="form.errors.password_confirmation"
                required
                autocomplete="new-password"
            />
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('auth.reset.submit') }}
            </XButton>
        </form>
    </AuthLayout>
</template>
