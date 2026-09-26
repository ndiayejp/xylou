<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Lock } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

// Écran destiné au parent, affiché pendant la session enfant.
defineProps<{ usesPin: boolean }>();

const form = useForm({ code: '' });

const submit = () => {
    form.post(route('kid.exit.store'), { onFinish: () => form.reset() });
};
</script>

<template>
    <AuthLayout
        :title="$t('kid.exit.title')"
        :description="usesPin ? $t('kid.exit.descriptionPin') : $t('kid.exit.descriptionPassword')"
        :home-href="route('kid.home')"
    >
        <form class="flex flex-col gap-5" novalidate @submit.prevent="submit">
            <XInput
                v-model="form.code"
                type="password"
                :label="usesPin ? $t('kid.exit.pin') : $t('kid.exit.password')"
                :icon="Lock"
                :error="form.errors.code"
                :inputmode="usesPin ? 'numeric' : undefined"
                autocomplete="off"
                required
                autofocus
            />
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('kid.exit.submit') }}
            </XButton>
        </form>

        <template #footer>
            <Link :href="route('kid.home')" class="link">{{ $t('kid.exit.back') }}</Link>
        </template>
    </AuthLayout>
</template>
