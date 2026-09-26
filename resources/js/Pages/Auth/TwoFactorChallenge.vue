<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { KeyRound, ShieldCheck } from '@lucide/vue';
import { ref } from 'vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const toggle = () => {
    recovery.value = !recovery.value;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.transform((data) =>
        recovery.value ? { recovery_code: data.recovery_code } : { code: data.code },
    ).post(route('two-factor.login.store'), { onFinish: () => form.reset() });
};
</script>

<template>
    <AuthLayout
        :title="$t('auth.twoFactor.title')"
        :description="
            recovery ? $t('auth.twoFactor.recoveryDescription') : $t('auth.twoFactor.description')
        "
    >
        <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
            <XInput
                v-if="!recovery"
                v-model="form.code"
                :label="$t('auth.twoFactor.code')"
                :icon="ShieldCheck"
                :error="form.errors.code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="6"
                required
                autofocus
            />
            <XInput
                v-else
                v-model="form.recovery_code"
                :label="$t('auth.twoFactor.recoveryCode')"
                :icon="KeyRound"
                :error="form.errors.recovery_code"
                autocomplete="off"
                required
                autofocus
            />
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('auth.twoFactor.submit') }}
            </XButton>
        </form>

        <template #footer>
            <button type="button" class="link" @click="toggle">
                {{ recovery ? $t('auth.twoFactor.useCode') : $t('auth.twoFactor.useRecovery') }}
            </button>
        </template>
    </AuthLayout>
</template>
