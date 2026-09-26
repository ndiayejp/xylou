<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import XCallout from '@/Components/ui/XCallout.vue';
import XCard from '@/Components/ui/XCard.vue';
import XCheckbox from '@/Components/ui/XCheckbox.vue';
import XChip from '@/Components/ui/XChip.vue';
import XSegmented from '@/Components/ui/XSegmented.vue';
import XTextarea from '@/Components/ui/XTextarea.vue';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import StepFooter from './Partials/StepFooter.vue';

// Écran 5 « Difficultés » : tout est facultatif et reste privé (jamais montré à l'enfant).
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    childName: string | null;
    catalog: Record<string, string[]>;
    selected: string[];
    observation: string | null;
}>();

const DAILY = 'daily';
const form = useForm({ difficulties: [...props.selected], observation: props.observation ?? '' });

const subjects = computed(() => Object.keys(props.catalog).filter((subject) => subject !== DAILY));
const subject = ref(subjects.value[0] ?? 'maths');

function has(id: string): boolean {
    return form.difficulties.includes(id);
}

function toggle(id: string, on: boolean): void {
    form.difficulties = on
        ? [...form.difficulties, id]
        : form.difficulties.filter((item) => item !== id);
}

const name = computed(() => props.childName ?? '');
const back = computed(() => route('onboarding.goals', props.onboarding.id));
const submit = () => form.put(route('onboarding.difficulties.update', props.onboarding.id));
const skip = () => router.post(route('onboarding.step.next', [props.onboarding.id, 5]));
</script>

<template>
    <OnboardingLayout
        :step="5"
        :title="$t('onboarding.difficulties.title', { name })"
        :description="$t('onboarding.difficulties.description')"
        :home-href="route('home')"
        :back-href="back"
    >
        <form
            id="difficultes"
            class="grid max-w-5xl gap-9 lg:grid-cols-[1.3fr_1fr]"
            novalidate
            @submit.prevent="submit"
        >
            <div class="flex flex-col gap-5">
                <XSegmented
                    v-model="subject"
                    :label="$t('onboarding.difficulties.subject')"
                    :options="
                        subjects.map((value) => ({
                            value,
                            label: $t(`onboarding.difficulties.subjects.${value}`),
                        }))
                    "
                />
                <div class="flex flex-wrap gap-2.5">
                    <XChip
                        v-for="key in catalog[subject]"
                        :key="`${subject}.${key}`"
                        :label="$t(`difficulties.${subject}.${key}`)"
                        :model-value="has(`${subject}.${key}`)"
                        @update:model-value="toggle(`${subject}.${key}`, $event)"
                    />
                </div>
                <p class="text-caption text-muted" aria-live="polite">
                    {{
                        $t(
                            'onboarding.difficulties.selected',
                            form.difficulties.filter((id) => !id.startsWith(`${DAILY}.`)).length,
                        )
                    }}
                </p>
                <XTextarea
                    v-model="form.observation"
                    :label="$t('onboarding.difficulties.observation')"
                    :optional-label="$t('onboarding.difficulties.observationOptional')"
                    :placeholder="$t('onboarding.difficulties.observationPlaceholder')"
                    :error="form.errors.observation"
                    :rows="4"
                    maxlength="1000"
                />
            </div>

            <div class="flex flex-col gap-4">
                <XCard as="fieldset" padding="lg" class="flex flex-col gap-3">
                    <legend class="float-left mb-1 w-full text-[14px] font-extrabold">
                        {{ $t('onboarding.difficulties.daily') }}
                    </legend>
                    <XCheckbox
                        v-for="key in catalog[DAILY]"
                        :key="key"
                        :model-value="has(`${DAILY}.${key}`)"
                        @update:model-value="toggle(`${DAILY}.${key}`, $event)"
                    >
                        {{ $t(`difficulties.${DAILY}.${key}`) }}
                    </XCheckbox>
                </XCard>
                <XCallout tone="info">
                    {{ $t('onboarding.difficulties.private', { name }) }}
                </XCallout>
            </div>
        </form>

        <template #footer>
            <StepFooter :back-href="back" form="difficultes" :processing="form.processing">
                <template #secondary>
                    <button type="button" class="link text-[15px] text-muted" @click="skip">
                        {{ $t('onboarding.difficulties.skip') }}
                    </button>
                </template>
            </StepFooter>
        </template>
    </OnboardingLayout>
</template>
