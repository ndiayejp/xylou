<script setup lang="ts">
import { ArrowDown, ArrowUp, Plus, Trash2, X } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XCheckbox from '@/Components/ui/XCheckbox.vue';
import XIconButton from '@/Components/ui/XIconButton.vue';
import XInput from '@/Components/ui/XInput.vue';
import XSelect from '@/Components/ui/XSelect.vue';
import XTextarea from '@/Components/ui/XTextarea.vue';
import type { AnswerTypeKey, QuestionForm } from '../types';

const props = defineProps<{
    number: number;
    count: number;
    answerTypes: AnswerTypeKey[];
    maxChoices: number;
    errors: Partial<Record<string, string>>;
}>();

const emit = defineEmits<{ move: [step: -1 | 1]; remove: [] }>();

const question = defineModel<QuestionForm>({ required: true });
const { t } = useI18n();

const error = (field: string) => props.errors[`items.${props.number - 1}.${field}`];
const isChoice = computed(() =>
    ['single_choice', 'multiple_choice'].includes(question.value.answer_type),
);

const typeOptions = computed(() =>
    props.answerTypes.map((type) => ({
        value: type,
        label: t(`activityEditor.answerTypes.${type}`),
    })),
);
const correctOptions = computed(() =>
    question.value.choices.map((choice, index) => ({
        value: index,
        label: t('activityEditor.choice', { number: index + 1, text: choice }),
    })),
);

function removeChoice(index: number): void {
    const q = question.value;
    q.choices.splice(index, 1);
    // Les indices des bonnes réponses suivent la suppression.
    if (q.correct !== null) {
        q.correct = q.correct === index ? null : q.correct > index ? q.correct - 1 : q.correct;
    }
    q.correct_many = q.correct_many.filter((i) => i !== index).map((i) => (i > index ? i - 1 : i));
}

function toggleCorrect(index: number, checked: boolean): void {
    const q = question.value;
    q.correct_many = checked
        ? [...new Set([...q.correct_many, index])].sort((a, b) => a - b)
        : q.correct_many.filter((i) => i !== index);
}
</script>

<template>
    <XCard as="li" class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-[16px] font-extrabold">
                {{ $t('activityEditor.question', { number }) }}
            </h3>
            <div class="flex gap-1">
                <XIconButton
                    :icon="ArrowUp"
                    variant="ghost"
                    :label="$t('activityEditor.moveUp', { number })"
                    :disabled="number === 1"
                    @click="emit('move', -1)"
                />
                <XIconButton
                    :icon="ArrowDown"
                    variant="ghost"
                    :label="$t('activityEditor.moveDown', { number })"
                    :disabled="number === count"
                    @click="emit('move', 1)"
                />
                <XIconButton
                    :icon="Trash2"
                    variant="ghost"
                    :label="$t('activityEditor.removeQuestion', { number })"
                    @click="emit('remove')"
                />
            </div>
        </div>

        <XTextarea
            v-model="question.prompt"
            :label="$t('activityEditor.prompt')"
            :rows="2"
            :error="error('prompt')"
        />
        <div class="sm:max-w-xs">
            <XSelect
                v-model="question.answer_type"
                :label="$t('activityEditor.answerType')"
                :options="typeOptions"
            />
        </div>

        <div v-if="question.answer_type === 'number'" class="grid gap-4 sm:grid-cols-3">
            <XInput
                v-model="question.value"
                type="number"
                step="any"
                inputmode="decimal"
                :label="$t('activityEditor.value')"
                :error="error('value')"
            />
            <XInput
                v-model="question.unit"
                :label="$t('activityEditor.unit')"
                :optional-label="$t('activityEditor.optional')"
                :hint="$t('activityEditor.unitHint')"
            />
            <XInput
                v-model="question.tolerance"
                type="number"
                step="any"
                min="0"
                inputmode="decimal"
                :label="$t('activityEditor.tolerance')"
                :optional-label="$t('activityEditor.optional')"
                :hint="$t('activityEditor.toleranceHint')"
                :error="error('tolerance')"
            />
        </div>

        <fieldset v-else-if="question.answer_type === 'text'" class="flex flex-col gap-3">
            <legend class="mb-2 text-[14px] font-bold">{{ $t('activityEditor.accepted') }}</legend>
            <p class="-mt-1 text-[13px] text-muted">{{ $t('activityEditor.acceptedHint') }}</p>
            <div v-for="(_, index) in question.accepted" :key="index" class="flex items-end gap-2">
                <div class="flex-1">
                    <XInput
                        v-model="question.accepted[index]"
                        :label="$t('activityEditor.acceptedItem', { number: index + 1 })"
                    />
                </div>
                <XIconButton
                    v-if="question.accepted.length > 1"
                    :icon="X"
                    variant="ghost"
                    :label="$t('activityEditor.removeAccepted', { number: index + 1 })"
                    @click="question.accepted.splice(index, 1)"
                />
            </div>
            <p v-if="error('accepted')" class="text-[13px] font-semibold text-danger">
                {{ error('accepted') }}
            </p>
            <div>
                <XButton
                    variant="ghost"
                    size="sm"
                    :icon="Plus"
                    :disabled="question.accepted.length >= 10"
                    @click="question.accepted.push('')"
                >
                    {{ $t('activityEditor.addAccepted') }}
                </XButton>
            </div>
        </fieldset>

        <fieldset v-else-if="isChoice" class="flex flex-col gap-3">
            <legend class="mb-2 text-[14px] font-bold">{{ $t('activityEditor.choices') }}</legend>
            <div v-for="(_, index) in question.choices" :key="index" class="flex items-end gap-2">
                <div class="flex-1">
                    <XInput
                        v-model="question.choices[index]"
                        :label="$t('activityEditor.choiceLabel', { number: index + 1 })"
                    />
                </div>
                <XCheckbox
                    v-if="question.answer_type === 'multiple_choice'"
                    :model-value="question.correct_many.includes(index)"
                    class="mb-3"
                    @update:model-value="toggleCorrect(index, $event)"
                >
                    {{ $t('activityEditor.isCorrect') }}
                    <span class="sr-only">{{
                        $t('activityEditor.ofChoice', { number: index + 1 })
                    }}</span>
                </XCheckbox>
                <XIconButton
                    v-if="question.choices.length > 2"
                    :icon="X"
                    variant="ghost"
                    :label="$t('activityEditor.removeChoice', { number: index + 1 })"
                    @click="removeChoice(index)"
                />
            </div>
            <p v-if="error('choices')" class="text-[13px] font-semibold text-danger">
                {{ error('choices') }}
            </p>
            <p v-if="error('correct_many')" class="text-[13px] font-semibold text-danger">
                {{ error('correct_many') }}
            </p>
            <div>
                <XButton
                    variant="ghost"
                    size="sm"
                    :icon="Plus"
                    :disabled="question.choices.length >= maxChoices"
                    @click="question.choices.push('')"
                >
                    {{ $t('activityEditor.addChoice') }}
                </XButton>
            </div>
            <div v-if="question.answer_type === 'single_choice'" class="sm:max-w-md">
                <XSelect
                    v-model="question.correct"
                    :label="$t('activityEditor.correct')"
                    :placeholder="$t('activityEditor.correctPlaceholder')"
                    :options="correctOptions"
                    :error="error('correct')"
                />
            </div>
        </fieldset>

        <XTextarea
            v-model="question.explanation"
            :label="$t('activityEditor.explanation')"
            :hint="$t('activityEditor.explanationHint')"
            :rows="2"
            :error="error('explanation')"
        />
        <XInput
            v-model="question.hint"
            :label="$t('activityEditor.hint')"
            :optional-label="$t('activityEditor.optional')"
        />
    </XCard>
</template>
