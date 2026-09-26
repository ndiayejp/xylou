<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    Blocks,
    Music,
    PawPrint,
    Rocket,
    Star,
    Timer,
    Type,
    Volleyball,
    Volume2,
} from '@lucide/vue';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import XCallout from '@/Components/ui/XCallout.vue';
import XInput from '@/Components/ui/XInput.vue';
import XSegmented from '@/Components/ui/XSegmented.vue';
import XSelect from '@/Components/ui/XSelect.vue';
import XToggle from '@/Components/ui/XToggle.vue';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import type { Grade } from '@/types';

// Écran 2 « Profil de l'enfant ». Les réponses sont gardées : revenir ici retrouve le formulaire rempli.
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    child: {
        firstName: string;
        age: number | null;
        grade: Grade;
        language: string;
        avatar: string | null;
        readAloud: boolean;
        dyslexiaFont: boolean;
        noTimer: boolean;
    } | null;
}>();

const { t } = useI18n();

const PRIMARY: Grade[] = ['cp', 'ce1', 'ce2', 'cm1', 'cm2'];
const MIDDLE: Grade[] = ['6e', '5e', '4e', '3e'];

const form = useForm({
    first_name: props.child?.firstName ?? '',
    age: props.child?.age ?? null,
    grade: props.child?.grade ?? ('cp' as Grade),
    language: props.child?.language ?? 'fr',
    avatar: props.child?.avatar ?? null,
    read_aloud: props.child?.readAloud ?? false,
    dyslexia_font: props.child?.dyslexiaFont ?? false,
    no_timer: props.child?.noTimer ?? false,
});

type Level = 'primary' | 'middle';
const level = computed<Level>({
    get: () => (MIDDLE.includes(form.grade) ? 'middle' : 'primary'),
    set: (value) => (form.grade = value === 'middle' ? '6e' : 'cp'),
});

const levelOptions = computed(() =>
    (['primary', 'middle'] as const).map((value) => ({
        value,
        label: t(`onboarding.child.levels.${value}`),
    })),
);
const gradeOptions = computed(() =>
    (level.value === 'middle' ? MIDDLE : PRIMARY).map((grade) => ({
        value: grade,
        label: t(`children.grades.${grade}`),
    })),
);
const ageOptions = computed(() =>
    Array.from({ length: 12 }, (_, index) => index + 5).map((age) => ({
        value: age,
        label: t('onboarding.child.ageOption', age),
    })),
);
const languageOptions = computed(() => [
    { value: 'fr', label: t('onboarding.child.languages.fr') },
]);

const avatars = [
    { key: 'rocket', icon: Rocket, tone: 'bg-tint text-primary-text' },
    { key: 'ball', icon: Volleyball, tone: 'bg-subject-sciences-bg text-subject-sciences-text' },
    { key: 'paw', icon: PawPrint, tone: 'bg-subject-history-bg text-subject-history-text' },
    { key: 'brick', icon: Blocks, tone: 'bg-subject-french-bg text-subject-french-text' },
    { key: 'music', icon: Music, tone: 'bg-subject-languages-bg text-subject-languages-text' },
    { key: 'star', icon: Star, tone: 'bg-accent-soft text-accent-text' },
] as const;

// Un âge vide se transmet comme null (champ facultatif).
watch(
    () => form.age,
    (age) => {
        if (age === ('' as unknown)) form.age = null;
    },
);

const submit = () => {
    form.put(route('onboarding.child.update', props.onboarding.id));
};
</script>

<template>
    <OnboardingLayout
        :step="2"
        :title="$t('onboarding.child.title')"
        :description="$t('onboarding.child.description')"
        :home-href="route('home')"
    >
        <form
            id="profil-enfant"
            class="grid max-w-5xl gap-9 lg:grid-cols-[1.2fr_1fr]"
            novalidate
            @submit.prevent="submit"
        >
            <div class="flex flex-col gap-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <XInput
                        v-model="form.first_name"
                        :label="$t('onboarding.child.firstName')"
                        :hint="$t('onboarding.child.firstNameHint')"
                        :error="form.errors.first_name"
                        autocomplete="off"
                        required
                        autofocus
                    />
                    <XSelect
                        v-model="form.age"
                        :label="$t('onboarding.child.age')"
                        :optional-label="$t('onboarding.child.ageOptional')"
                        :placeholder="$t('onboarding.child.agePlaceholder')"
                        :options="ageOptions"
                        :error="form.errors.age"
                    />
                </div>
                <div class="flex flex-col gap-2">
                    <span class="text-[14px] font-bold" aria-hidden="true">{{
                        $t('onboarding.child.level')
                    }}</span>
                    <XSegmented
                        v-model="level"
                        :label="$t('onboarding.child.level')"
                        :options="levelOptions"
                    />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <XSelect
                        v-model="form.grade"
                        :label="$t('onboarding.child.grade')"
                        :options="gradeOptions"
                        :error="form.errors.grade"
                    />
                    <XSelect
                        v-model="form.language"
                        :label="$t('onboarding.child.language')"
                        :options="languageOptions"
                        :error="form.errors.language"
                    />
                </div>
                <fieldset class="flex flex-col gap-2">
                    <legend class="mb-2 text-[14px] font-bold">
                        {{ $t('onboarding.child.avatar') }}
                        <span class="font-medium text-muted">{{
                            $t('onboarding.child.avatarHint')
                        }}</span>
                    </legend>
                    <div class="flex flex-wrap gap-2.5">
                        <button
                            v-for="avatar in avatars"
                            :key="avatar.key"
                            type="button"
                            :aria-pressed="form.avatar === avatar.key"
                            :aria-label="$t(`onboarding.child.avatars.${avatar.key}`)"
                            class="flex size-16 items-center justify-center rounded-[22px] transition duration-hover"
                            :class="[
                                avatar.tone,
                                form.avatar === avatar.key
                                    ? 'ring-[3px] ring-primary'
                                    : 'ring-2 ring-line hover:ring-subtle',
                            ]"
                            @click="form.avatar = form.avatar === avatar.key ? null : avatar.key"
                        >
                            <component :is="avatar.icon" :size="28" aria-hidden="true" />
                        </button>
                    </div>
                </fieldset>
            </div>

            <div class="flex flex-col gap-4">
                <p class="text-[14px] font-bold">{{ $t('onboarding.child.comfort') }}</p>
                <div class="flex flex-col gap-2 rounded-card bg-surface p-4">
                    <div class="flex items-center gap-3">
                        <Volume2 :size="20" class="shrink-0 text-primary-text" aria-hidden="true" />
                        <XToggle
                            v-model="form.read_aloud"
                            :label="$t('onboarding.child.readAloud')"
                            :hint="$t('onboarding.child.readAloudHint')"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <Type :size="20" class="shrink-0 text-primary-text" aria-hidden="true" />
                        <XToggle
                            v-model="form.dyslexia_font"
                            :label="$t('onboarding.child.dyslexiaFont')"
                            :hint="$t('onboarding.child.dyslexiaFontHint')"
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <Timer :size="20" class="shrink-0 text-primary-text" aria-hidden="true" />
                        <XToggle
                            v-model="form.no_timer"
                            :label="$t('onboarding.child.noTimer')"
                            :hint="$t('onboarding.child.noTimerHint')"
                        />
                    </div>
                </div>
                <XCallout tone="info">{{ $t('onboarding.child.siblings') }}</XCallout>
            </div>
        </form>

        <template #footer>
            <span />
            <XButton type="submit" form="profil-enfant" size="lg" :loading="form.processing">
                {{ $t('onboarding.continue') }}
                <ArrowRight :size="20" aria-hidden="true" />
            </XButton>
        </template>
    </OnboardingLayout>
</template>
