<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, Lock, Mail } from '@lucide/vue';
import { computed } from 'vue';
import XButton from '@/Components/ui/XButton.vue';
import XCheckbox from '@/Components/ui/XCheckbox.vue';
import XInput from '@/Components/ui/XInput.vue';
import { passwordStrength, strengthLevel } from '@/Composables/passwordStrength';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';

// Écran 1 « Votre compte ».
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    parental_authority: false,
    terms: false,
});

const strength = computed(() => passwordStrength(form.password));
const level = computed(() => strengthLevel[strength.value]);

const submit = () => {
    form.post(route('register'), { onFinish: () => form.reset('password') });
};
</script>

<template>
    <OnboardingLayout
        :step="1"
        :title="$t('onboarding.account.title')"
        :description="$t('onboarding.account.description')"
        :home-href="route('home')"
    >
        <form id="compte" class="flex max-w-xl flex-col gap-5" novalidate @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <XInput
                    v-model="form.first_name"
                    :label="$t('onboarding.account.firstName')"
                    :error="form.errors.first_name"
                    autocomplete="given-name"
                    required
                    autofocus
                />
                <XInput
                    v-model="form.last_name"
                    :label="$t('onboarding.account.lastName')"
                    :error="form.errors.last_name"
                    autocomplete="family-name"
                    required
                />
            </div>
            <XInput
                v-model="form.email"
                type="email"
                :icon="Mail"
                :label="$t('onboarding.account.email')"
                :error="form.errors.email"
                autocomplete="email"
                required
            />
            <div class="flex flex-col gap-2">
                <XInput
                    v-model="form.password"
                    type="password"
                    :icon="Lock"
                    :label="$t('onboarding.account.password')"
                    :hint="$t('onboarding.account.passwordHint')"
                    :error="form.errors.password"
                    autocomplete="new-password"
                    required
                />
                <div v-if="form.password" class="flex flex-col gap-1.5" aria-live="polite">
                    <div class="flex gap-1.5" aria-hidden="true">
                        <span
                            v-for="segment in 4"
                            :key="segment"
                            class="h-1.5 grow rounded-full"
                            :class="
                                segment <= level
                                    ? level >= 3
                                        ? 'bg-success'
                                        : 'bg-accent'
                                    : 'bg-disabled'
                            "
                        />
                    </div>
                    <p
                        class="text-caption"
                        :class="level >= 3 ? 'text-success-text' : 'text-accent-text'"
                    >
                        {{ $t(`onboarding.account.strength.${strength}`) }}
                    </p>
                </div>
            </div>
            <XCheckbox v-model="form.parental_authority" :error="form.errors.parental_authority">
                {{ $t('onboarding.account.parentalAuthority') }}
            </XCheckbox>
            <XCheckbox v-model="form.terms" :error="form.errors.terms">
                {{ $t('onboarding.account.termsStart') }}
                <a :href="route('legal.terms')" target="_blank" class="link">{{
                    $t('onboarding.account.termsLink')
                }}</a>
                {{ $t('onboarding.account.termsMiddle') }}
                <a :href="route('legal.privacy')" target="_blank" class="link">{{
                    $t('onboarding.account.privacyLink')
                }}</a
                >.
            </XCheckbox>
        </form>

        <template #footer>
            <p class="text-[14px] text-muted">
                {{ $t('onboarding.account.hasAccount') }}
                <Link :href="route('login')" class="link">{{
                    $t('onboarding.account.login')
                }}</Link>
            </p>
            <XButton type="submit" form="compte" size="lg" :loading="form.processing">
                {{ $t('onboarding.continue') }}
                <ArrowRight :size="20" aria-hidden="true" />
            </XButton>
        </template>
    </OnboardingLayout>
</template>
