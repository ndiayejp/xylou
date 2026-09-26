<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Lock } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout :title="$t('auth.confirm.title')" :description="$t('auth.confirm.description')">
        <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
            <XInput
                v-model="form.password"
                type="password"
                :label="$t('auth.fields.password')"
                :icon="Lock"
                :error="form.errors.password"
                required
                autofocus
                autocomplete="current-password"
            />
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('auth.confirm.submit') }}
            </XButton>
        </form>
    </AuthLayout>
</template>
