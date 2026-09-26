<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { RefreshCw, ShieldCheck, ShieldOff } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import XCallout from '@/Components/ui/XCallout.vue';
import XCard from '@/Components/ui/XCard.vue';
import XInput from '@/Components/ui/XInput.vue';
import XTag from '@/Components/ui/XTag.vue';
import AccountSpace from '@/Layouts/AccountSpace.vue';

const props = defineProps<{
    twoFactor: {
        enabled: boolean;
        pending: boolean;
        required: boolean;
        qrCodeSvg: string | null;
        setupKey: string | null;
        recoveryCodes: string[];
    };
    status?: string;
}>();

const { t, te } = useI18n();

const statusMessage = computed(() => {
    const key = `security.status.${props.status}`;
    return props.status && te(key) ? t(key) : undefined;
});

// SVG généré par le serveur, affiché en image : pas de v-html.
const qrSrc = computed(() =>
    props.twoFactor.qrCodeSvg
        ? `data:image/svg+xml;charset=utf-8,${encodeURIComponent(props.twoFactor.qrCodeSvg)}`
        : undefined,
);

const confirmForm = useForm({ code: '' });

const options = { preserveScroll: true };
const enable = () => router.post(route('two-factor.enable'), {}, options);
const disable = () => router.delete(route('two-factor.disable'), options);
const regenerate = () => router.post(route('two-factor.regenerate-recovery-codes'), {}, options);
const confirm = () =>
    confirmForm.post(route('two-factor.confirm'), {
        ...options,
        errorBag: 'confirmTwoFactorAuthentication',
        onFinish: () => confirmForm.reset(),
    });
</script>

<template>
    <Head :title="$t('security.title')" />

    <AccountSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">{{ $t('security.title') }}</h1>
        </template>

        <div class="flex max-w-3xl flex-col gap-6">
            <XCallout v-if="statusMessage" tone="ok" role="status">{{ statusMessage }}</XCallout>

            <XCard
                as="section"
                padding="lg"
                class="flex flex-col gap-5"
                aria-labelledby="deux-etapes"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="deux-etapes" class="text-h3">{{ $t('security.twoFactor.title') }}</h2>
                    <XTag
                        :tone="twoFactor.enabled ? 'green' : 'neutral'"
                        :icon="twoFactor.enabled ? ShieldCheck : ShieldOff"
                    >
                        {{
                            twoFactor.enabled
                                ? $t('security.twoFactor.enabled')
                                : $t('security.twoFactor.disabled')
                        }}
                    </XTag>
                </div>
                <p class="text-muted">{{ $t('security.twoFactor.description') }}</p>

                <XCallout v-if="twoFactor.required && !twoFactor.enabled" tone="warn">
                    {{ $t('security.twoFactor.required') }}
                </XCallout>

                <!-- 1. Désactivée -->
                <div v-if="!twoFactor.enabled && !twoFactor.pending">
                    <XButton :icon="ShieldCheck" @click="enable">
                        {{ $t('security.twoFactor.enable') }}
                    </XButton>
                </div>

                <!-- 2. En attente de confirmation -->
                <div v-if="twoFactor.pending" class="flex flex-col gap-5">
                    <p class="font-semibold">{{ $t('security.twoFactor.scan') }}</p>
                    <img
                        v-if="qrSrc"
                        :src="qrSrc"
                        :alt="$t('security.twoFactor.qrAlt')"
                        class="size-48 rounded-input border border-line bg-surface p-3"
                    />
                    <p v-if="twoFactor.setupKey" class="text-caption text-muted">
                        {{ $t('security.twoFactor.setupKey') }}
                        <code
                            class="ml-1 select-all rounded-tag bg-well px-2 py-1 font-mono text-[14px] text-text"
                            >{{ twoFactor.setupKey }}</code
                        >
                    </p>
                    <p class="font-semibold">{{ $t('security.twoFactor.confirmStep') }}</p>
                    <form class="flex max-w-sm flex-col gap-4" novalidate @submit.prevent="confirm">
                        <XInput
                            v-model="confirmForm.code"
                            :label="$t('security.twoFactor.code')"
                            :error="confirmForm.errors.code"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            required
                        />
                        <div class="flex flex-wrap gap-2">
                            <XButton type="submit" :loading="confirmForm.processing">
                                {{ $t('security.twoFactor.confirm') }}
                            </XButton>
                            <XButton v-if="!twoFactor.required" variant="ghost" @click="disable">
                                {{ $t('security.twoFactor.cancel') }}
                            </XButton>
                        </div>
                    </form>
                </div>

                <!-- 3. Activée -->
                <div v-if="twoFactor.enabled" class="flex flex-col gap-4">
                    <div>
                        <h3 class="font-bold">{{ $t('security.twoFactor.recoveryTitle') }}</h3>
                        <p class="text-caption text-muted">
                            {{ $t('security.twoFactor.recoveryDescription') }}
                        </p>
                    </div>
                    <ul
                        class="grid gap-2 rounded-input bg-well p-4 font-mono text-[14px] sm:grid-cols-2"
                    >
                        <li v-for="code in twoFactor.recoveryCodes" :key="code">{{ code }}</li>
                    </ul>
                    <div class="flex flex-wrap gap-2">
                        <XButton variant="secondary" :icon="RefreshCw" @click="regenerate">
                            {{ $t('security.twoFactor.regenerate') }}
                        </XButton>
                        <XButton v-if="!twoFactor.required" variant="danger" @click="disable">
                            {{ $t('security.twoFactor.disable') }}
                        </XButton>
                    </div>
                </div>
            </XCard>
        </div>
    </AccountSpace>
</template>
