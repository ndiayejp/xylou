<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, Hourglass } from '@lucide/vue';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';

// Provisoire : écrans 3 à 7 en attente de leur PR ; le parcours et la progression restent complets.
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    step: number;
    childName: string | null;
}>();

const form = useForm({});
const previous =
    props.step - 1 <= 2
        ? route('onboarding.child', props.onboarding.id)
        : route('onboarding.step', [props.onboarding.id, props.step - 1]);

const next = () => form.post(route('onboarding.step.next', [props.onboarding.id, props.step]));
</script>

<template>
    <OnboardingLayout
        :step="step"
        :title="$t(`onboarding.steps.${step}`)"
        :home-href="route('home')"
        :back-href="previous"
    >
        <XCard padding="lg" class="flex min-h-[260px] max-w-3xl items-center justify-center">
            <XEmptyState
                :icon="Hourglass"
                tone="neutral"
                :title="$t('onboarding.pending.title')"
                :description="$t('onboarding.pending.text')"
            />
        </XCard>

        <template #footer>
            <Link
                :href="previous"
                class="inline-flex h-14 items-center gap-2 rounded-button px-5 font-bold text-primary-strong hover:bg-tint"
            >
                <ArrowLeft :size="20" aria-hidden="true" />
                {{ $t('onboarding.back') }}
            </Link>
            <XButton size="lg" :loading="form.processing" @click="next">
                {{ $t('onboarding.continue') }}
                <ArrowRight :size="20" aria-hidden="true" />
            </XButton>
        </template>
    </OnboardingLayout>
</template>
