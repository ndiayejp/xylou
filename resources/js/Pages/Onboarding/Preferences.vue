<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Hand, Image, Landmark, Layers, Repeat, Zap } from '@lucide/vue';
import { computed, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import XSegmented from '@/Components/ui/XSegmented.vue';
import XSelectCard from '@/Components/ui/XSelectCard.vue';
import XToggle from '@/Components/ui/XToggle.vue';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import StepFooter from './Partials/StepFooter.vue';

// Écran 6 « Préférences ».
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    childName: string | null;
    styles: string[];
    durations: number[];
    preferences: { styles: string[]; sessionMinutes: number; gentleReminder: boolean } | null;
}>();

const { t } = useI18n();

const icons: Record<string, Component> = {
    visual: Image,
    concrete_examples: Landmark,
    small_steps: Layers,
    repetition: Repeat,
    short_explanations: Zap,
    interactive: Hand,
};

const form = useForm({
    styles: [...(props.preferences?.styles ?? [])],
    session_minutes: props.preferences?.sessionMinutes ?? 10,
    gentle_reminder: props.preferences?.gentleReminder ?? false,
});

function toggle(style: string, on: boolean): void {
    form.styles = on ? [...form.styles, style] : form.styles.filter((item) => item !== style);
}

const durationOptions = computed(() =>
    props.durations.map((value) => ({
        value,
        label: t('onboarding.preferences.minutes', { count: value }),
    })),
);

const back = computed(() => route('onboarding.difficulties', props.onboarding.id));
const submit = () => form.put(route('onboarding.preferences.update', props.onboarding.id));
</script>

<template>
    <OnboardingLayout
        :step="6"
        :title="$t('onboarding.preferences.title', { name: childName ?? '' })"
        :description="$t('onboarding.preferences.description')"
        :home-href="route('home')"
        :back-href="back"
    >
        <form
            id="preferences"
            class="flex max-w-4xl flex-col gap-8"
            novalidate
            @submit.prevent="submit"
        >
            <fieldset>
                <legend class="mb-3 text-[14px] font-bold">
                    {{ $t('onboarding.preferences.styles') }}
                </legend>
                <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="style in styles" :key="style">
                        <XSelectCard
                            class="h-full w-full"
                            :icon="icons[style]"
                            :title="$t(`onboarding.preferences.styleOptions.${style}.title`)"
                            :description="
                                $t(`onboarding.preferences.styleOptions.${style}.description`)
                            "
                            :model-value="form.styles.includes(style)"
                            @update:model-value="toggle(style, $event)"
                        />
                    </li>
                </ul>
            </fieldset>

            <XSegmented
                v-model="form.session_minutes"
                class="max-w-md"
                :label="$t('onboarding.preferences.duration')"
                :hide-label="false"
                :options="durationOptions"
            />

            <XToggle
                v-model="form.gentle_reminder"
                :label="$t('onboarding.preferences.reminder')"
                :hint="$t('onboarding.preferences.reminderHint')"
            />
        </form>

        <template #footer>
            <StepFooter
                :back-href="back"
                form="preferences"
                :processing="form.processing"
                :submit-label="$t('onboarding.preferences.submit')"
            />
        </template>
    </OnboardingLayout>
</template>
