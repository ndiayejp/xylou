<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Lock, Mail } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.register.title')" :description="$t('auth.register.description')">
        <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
            <XInput
                v-model="form.name"
                :label="$t('auth.fields.name')"
                :error="form.errors.name"
                required
                autofocus
                autocomplete="name"
            />
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
                :label="$t('auth.fields.password')"
                :icon="Lock"
                :hint="$t('auth.fields.passwordHint')"
                :error="form.errors.password"
                required
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
                {{ $t('auth.register.submit') }}
            </XButton>
        </form>

        <template #footer>
            {{ $t('auth.register.hasAccount') }}
            <Link :href="route('login')" class="link">{{ $t('auth.register.login') }}</Link>
        </template>
    </AuthLayout>
</template>
