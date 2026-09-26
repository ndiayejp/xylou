<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Lock, Mail } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import XToggle from '@/Components/ui/XToggle.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthLayout
        :title="$t('auth.login.title')"
        :description="$t('auth.login.description')"
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
            <XInput
                v-model="form.password"
                type="password"
                :label="$t('auth.fields.password')"
                :icon="Lock"
                :error="form.errors.password"
                required
                autocomplete="current-password"
            />
            <div class="flex flex-wrap items-center justify-between gap-2">
                <XToggle v-model="form.remember" :label="$t('auth.fields.remember')" />
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="link text-[14px]"
                >
                    {{ $t('auth.login.forgot') }}
                </Link>
            </div>
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('auth.login.submit') }}
            </XButton>
        </form>

        <template #footer>
            {{ $t('auth.login.noAccount') }}
            <Link :href="route('register')" class="link">{{ $t('auth.login.register') }}</Link>
        </template>
    </AuthLayout>
</template>
