<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import XCallout from '@/Components/ui/XCallout.vue';
import XCard from '@/Components/ui/XCard.vue';
import XInput from '@/Components/ui/XInput.vue';
import XSegmented from '@/Components/ui/XSegmented.vue';
import XSelect from '@/Components/ui/XSelect.vue';
import XTextarea from '@/Components/ui/XTextarea.vue';
import { useToasts } from '@/Composables/useToasts';
import AccountSpace from '@/Layouts/AccountSpace.vue';
import QuestionEditor from './Partials/QuestionEditor.vue';
import type { AnswerTypeKey, EditedActivity, EditorOptions, QuestionForm } from './types';

const props = defineProps<{
    activity: EditedActivity | null;
    defaultGrade: string | null;
    options: EditorOptions;
    libraryUrl: string;
}>();

const { t } = useI18n();
const { push } = useToasts();

let nextUid = 1;
function question(type: AnswerTypeKey = 'number'): QuestionForm {
    return {
        uid: nextUid++,
        answer_type: type,
        prompt: '',
        hint: '',
        explanation: '',
        value: '',
        unit: '',
        tolerance: '',
        accepted: [''],
        choices: ['', ''],
        correct: null,
        correct_many: [],
    };
}

const a = props.activity;
const form = useForm({
    intent: 'draft' as 'draft' | 'approve',
    title: a?.title ?? '',
    subject: a?.subject ?? props.options.subjects[0] ?? '',
    grade: a?.grade ?? props.defaultGrade ?? '',
    skill_id: a?.skill_id ?? (null as number | null),
    universe: a?.universe ?? '',
    format: a?.format ?? 'exercise',
    difficulty: a?.difficulty ?? 'practice',
    duration_minutes: a?.duration_minutes ?? 10,
    learning_objective: a?.learning_objective ?? '',
    items: (a?.items.map((item) => ({
        ...question(item.answer_type),
        ...item,
        value: item.value === null ? '' : String(item.value),
        tolerance: item.tolerance === null ? '' : String(item.tolerance),
        unit: item.unit ?? '',
        hint: item.hint ?? '',
        explanation: item.explanation ?? '',
        accepted: item.accepted.length > 0 ? [...item.accepted] : [''],
        choices: item.choices.length > 0 ? [...item.choices] : ['', ''],
    })) ?? [question()]) as QuestionForm[],
});

const editing = computed(() => props.activity !== null);
const approved = computed(() => props.activity?.status === 'approved');

// Compétences en vigueur de la matière, pour la classe choisie.
const skillOptions = computed(() =>
    props.options.skills
        .filter((s) => s.subject === form.subject && (!form.grade || s.grades.includes(form.grade)))
        .map((s) => ({ value: s.id, label: s.label })),
);
watch([() => form.subject, () => form.grade], () => {
    if (!skillOptions.value.some((option) => option.value === form.skill_id)) {
        form.skill_id = null;
    }
});

const label = (prefix: string) => (value: string) => ({ value, label: t(`${prefix}.${value}`) });
const subjectOptions = computed(() => props.options.subjects.map(label('subjects')));
const gradeOptions = computed(() => props.options.grades.map(label('children.grades')));
const universeOptions = computed(() => [
    { value: '', label: t('activityEditor.noUniverse') },
    ...props.options.universes.map(label('universes')),
]);
const formatOptions = computed(() => props.options.formats.map(label('activityEditor.formats')));
const difficultyOptions = computed(() =>
    props.options.difficulties.map(label('activities.difficulties')),
);
const durationOptions = computed(() =>
    props.options.durations.map((minutes) => ({
        value: minutes,
        label: t('library.filters.minutes', { count: minutes }),
    })),
);

function move(index: number, step: -1 | 1): void {
    const [item] = form.items.splice(index, 1);
    form.items.splice(index + step, 0, item);
}

const errorCount = computed(() => Object.keys(form.errors).length);

function save(intent: 'draft' | 'approve'): void {
    form.intent = intent;
    const options = {
        preserveScroll: true,
        onSuccess: () =>
            push({
                message: t(
                    intent === 'approve'
                        ? approved.value
                            ? 'activityEditor.toasts.saved'
                            : 'activityEditor.toasts.approved'
                        : 'activityEditor.toasts.draft',
                ),
            }),
    };
    if (props.activity) {
        form.put(route('activities.update', props.activity.id), options);
    } else {
        form.post(route('activities.store'), options);
    }
}
</script>

<template>
    <Head :title="editing ? $t('activityEditor.editTitle') : $t('activityEditor.createTitle')" />

    <AccountSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">
                {{ editing ? $t('activityEditor.editTitle') : $t('activityEditor.createTitle') }}
            </h1>
            <p class="mt-1 text-[14px] text-muted">{{ $t('activityEditor.subtitle') }}</p>
        </template>

        <form class="flex max-w-4xl flex-col gap-6" novalidate @submit.prevent="save('approve')">
            <XCallout v-if="errorCount > 0" tone="err" role="alert">
                {{ form.errors.items ?? $t('activityEditor.errors', errorCount) }}
            </XCallout>

            <XCard as="section" padding="lg" class="flex flex-col gap-5">
                <h2 class="text-h3">{{ $t('activityEditor.activity') }}</h2>
                <XInput
                    v-model="form.title"
                    :label="$t('activityEditor.title')"
                    :error="form.errors.title"
                    maxlength="160"
                    required
                />
                <div class="grid gap-4 sm:grid-cols-2">
                    <XSelect
                        v-model="form.subject"
                        :label="$t('library.filters.subject')"
                        :options="subjectOptions"
                    />
                    <XSelect
                        v-model="form.grade"
                        :label="$t('library.filters.grade')"
                        :placeholder="$t('library.filters.allGrades')"
                        :options="gradeOptions"
                    />
                </div>
                <XSelect
                    v-model="form.skill_id"
                    :label="$t('library.filters.skill')"
                    :placeholder="$t('activityEditor.skillPlaceholder')"
                    :options="skillOptions"
                    :error="form.errors.skill_id"
                    required
                />
                <div class="grid gap-4 sm:grid-cols-2">
                    <XSelect
                        v-model="form.format"
                        :label="$t('activityEditor.format')"
                        :options="formatOptions"
                    />
                    <XSelect
                        v-model="form.universe"
                        :label="$t('library.filters.universe')"
                        :options="universeOptions"
                        :error="form.errors.universe"
                    />
                </div>
                <XSegmented
                    v-model="form.difficulty"
                    :label="$t('library.filters.difficulty')"
                    :hide-label="false"
                    :options="difficultyOptions"
                />
                <XSegmented
                    v-model="form.duration_minutes"
                    :label="$t('library.filters.duration')"
                    :hide-label="false"
                    :options="durationOptions"
                    class="sm:max-w-md"
                />
                <XTextarea
                    v-model="form.learning_objective"
                    :label="$t('activityEditor.objective')"
                    :optional-label="$t('activityEditor.optional')"
                    :rows="2"
                    :error="form.errors.learning_objective"
                />
            </XCard>

            <section class="flex flex-col gap-4" :aria-labelledby="'questions-title'">
                <h2 id="questions-title" class="text-h3">
                    {{ $t('activityEditor.questions', form.items.length) }}
                </h2>
                <ol class="flex flex-col gap-4">
                    <QuestionEditor
                        v-for="(item, index) in form.items"
                        :key="item.uid"
                        v-model="form.items[index]"
                        :number="index + 1"
                        :count="form.items.length"
                        :answer-types="options.answerTypes"
                        :max-choices="options.maxChoices"
                        :errors="form.errors"
                        @move="move(index, $event)"
                        @remove="form.items.splice(index, 1)"
                    />
                </ol>
                <div>
                    <XButton
                        variant="secondary"
                        :icon="Plus"
                        :disabled="form.items.length >= options.maxItems"
                        @click="form.items.push(question())"
                    >
                        {{ $t('activityEditor.addQuestion') }}
                    </XButton>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line pt-5">
                <Link :href="libraryUrl" class="link mr-auto">{{
                    $t('activityEditor.cancel')
                }}</Link>
                <XButton
                    v-if="!approved"
                    variant="secondary"
                    :loading="form.processing && form.intent === 'draft'"
                    :disabled="form.processing"
                    @click="save('draft')"
                >
                    {{ $t('activityEditor.saveDraft') }}
                </XButton>
                <XButton
                    type="submit"
                    :loading="form.processing && form.intent === 'approve'"
                    :disabled="form.processing"
                >
                    {{
                        approved
                            ? $t('activityEditor.saveChanges')
                            : $t('activityEditor.saveAndApprove')
                    }}
                </XButton>
            </div>
        </form>
    </AccountSpace>
</template>
