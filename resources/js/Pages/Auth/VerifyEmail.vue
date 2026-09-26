<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps<{
    status?: string;
}>();

const { t } = useI18n();
const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const statusMessage = computed(() =>
    props.status === 'verification-link-sent' ? t('auth.verify.linkSent') : undefined,
);
</script>

<template>
    <AuthLayout
        :title="$t('auth.verify.title')"
        :description="$t('auth.verify.description')"
        :status="statusMessage"
    >
        <form class="flex flex-col items-center gap-3" @submit.prevent="submit">
            <XButton type="submit" size="lg" block :loading="form.processing">
                {{ $t('auth.verify.resend') }}
            </XButton>
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="min-h-touch rounded-input px-3 text-[14px] font-bold text-primary-text hover:text-primary-strong"
            >
                {{ $t('auth.verify.logout') }}
            </Link>
        </form>
    </AuthLayout>
</template>
