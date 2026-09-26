<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import XCallout from '@/Components/ui/XCallout.vue';
import XChip from '@/Components/ui/XChip.vue';
import XTextarea from '@/Components/ui/XTextarea.vue';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import StepFooter from './Partials/StepFooter.vue';

// Écran 4 « Objectifs » : plusieurs choix, un principal parmi eux.
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    childName: string | null;
    goals: string[];
    selected: string[];
    primary: string | null;
    note: string | null;
}>();

const form = useForm({
    goals: [...props.selected],
    primary: props.primary ?? props.selected[0] ?? '',
    note: props.note ?? '',
});

function toggle(goal: string, on: boolean): void {
    form.goals = on ? [...form.goals, goal] : form.goals.filter((item) => item !== goal);
}

// Le principal reste toujours l'un des objectifs retenus.
watch(
    () => form.goals,
    (goals) => {
        if (!goals.includes(form.primary)) {
            form.primary = goals[0] ?? '';
        }
    },
);

const back = computed(() => route('onboarding.interests', props.onboarding.id));
const submit = () => form.put(route('onboarding.goals.update', props.onboarding.id));
</script>

<template>
    <OnboardingLayout
        :step="4"
        :title="$t('onboarding.goals.title')"
        :description="$t('onboarding.goals.description')"
        :home-href="route('home')"
        :back-href="back"
    >
        <form
            id="objectifs"
            class="grid max-w-5xl gap-9 lg:grid-cols-[1.3fr_1fr]"
            novalidate
            @submit.prevent="submit"
        >
            <div class="flex flex-col gap-6">
                <fieldset>
                    <legend class="sr-only">{{ $t('onboarding.goals.choose') }}</legend>
                    <div class="flex flex-wrap gap-2.5">
                        <XChip
                            v-for="goal in goals"
                            :key="goal"
                            :label="$t(`goals.${goal}`)"
                            :model-value="form.goals.includes(goal)"
                            @update:model-value="toggle(goal, $event)"
                        />
                    </div>
                    <p v-if="form.errors.goals" class="mt-2 text-caption text-danger" role="alert">
                        {{ form.errors.goals }}
                    </p>
                </fieldset>

                <XTextarea
                    v-if="form.goals.includes('other')"
                    v-model="form.note"
                    :label="$t('onboarding.goals.noteLabel')"
                    :error="form.errors.note"
                    maxlength="200"
                    :rows="3"
                />

                <fieldset v-if="form.goals.length > 1" class="flex flex-col gap-2">
                    <legend class="mb-2 text-[14px] font-bold">
                        {{ $t('onboarding.goals.primaryTitle') }}
                    </legend>
                    <div class="flex flex-wrap gap-2.5">
                        <label
                            v-for="goal in form.goals"
                            :key="goal"
                            class="inline-flex min-h-touch cursor-pointer items-center gap-2.5 rounded-full border-2 px-4 text-[14px] font-bold transition-colors duration-hover"
                            :class="
                                form.primary === goal
                                    ? 'border-primary bg-tint'
                                    : 'border-line bg-surface'
                            "
                        >
                            <input
                                v-model="form.primary"
                                type="radio"
                                name="primary"
                                :value="goal"
                                class="size-4 text-primary focus:ring-0"
                            />
                            {{ $t(`goals.${goal}`) }}
                        </label>
                    </div>
                </fieldset>
            </div>

            <XCallout
                v-if="form.primary"
                class="self-start"
                tone="ai"
                :title="$t('onboarding.goals.understandTitle')"
            >
                {{ $t(`onboarding.goals.hints.${form.primary}`) }}
            </XCallout>
        </form>

        <template #footer>
            <StepFooter :back-href="back" form="objectifs" :processing="form.processing" />
        </template>
    </OnboardingLayout>
</template>
