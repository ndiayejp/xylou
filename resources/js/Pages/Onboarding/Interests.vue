<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import XChip from '@/Components/ui/XChip.vue';
import XInput from '@/Components/ui/XInput.vue';
import XSelectCard from '@/Components/ui/XSelectCard.vue';
import { elides } from '@/i18n/elision';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import { interestIcons } from './Partials/interestIcons';
import StepFooter from './Partials/StepFooter.vue';

// Écran 3 « Son univers ».
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    childName: string | null;
    catalog: { key: string; category: string }[];
    selected: string[];
    custom: string[];
}>();

const MAX_CUSTOM = 5;
const { t } = useI18n();

const form = useForm({ interests: [...props.selected], custom: [...props.custom] });

const categories = computed(() => [...new Set(props.catalog.map((item) => item.category))]);
const category = ref<string | null>(null);
const visible = computed(() =>
    props.catalog.filter((item) => category.value === null || item.category === category.value),
);
const count = computed(() => form.interests.length + form.custom.length);

const title = computed(() => {
    const name = props.childName ?? '';
    return t(elides(name) ? 'onboarding.interests.titleElided' : 'onboarding.interests.title', {
        name,
    });
});

function toggle(key: string, on: boolean): void {
    form.interests = on ? [...form.interests, key] : form.interests.filter((item) => item !== key);
}

const draft = ref('');
function addCustom(): void {
    const label = draft.value.trim();
    if (label !== '' && !form.custom.includes(label) && form.custom.length < MAX_CUSTOM) {
        form.custom = [...form.custom, label];
    }
    draft.value = '';
}

const submit = () => form.put(route('onboarding.interests.update', props.onboarding.id));
</script>

<template>
    <OnboardingLayout
        :step="3"
        :title="title"
        :description="$t('onboarding.interests.description')"
        :home-href="route('home')"
        :back-href="route('onboarding.child', onboarding.id)"
    >
        <form
            id="univers"
            class="flex max-w-5xl flex-col gap-6"
            novalidate
            @submit.prevent="submit"
        >
            <div
                role="group"
                :aria-label="$t('onboarding.interests.filter')"
                class="flex flex-wrap gap-2"
            >
                <XChip
                    variant="filter"
                    :label="$t('onboarding.interests.all')"
                    :model-value="category === null"
                    @update:model-value="category = null"
                />
                <XChip
                    v-for="item in categories"
                    :key="item"
                    variant="filter"
                    :label="$t(`onboarding.interests.categories.${item}`)"
                    :model-value="category === item"
                    @update:model-value="category = category === item ? null : item"
                />
            </div>

            <p class="text-[14px] font-extrabold text-primary-text" aria-live="polite">
                {{ $t('onboarding.interests.count', count) }}
            </p>
            <p v-if="form.errors.interests" class="text-caption text-danger" role="alert">
                {{ form.errors.interests }}
            </p>

            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <li v-for="item in visible" :key="item.key">
                    <XSelectCard
                        class="h-full w-full"
                        :title="$t(`interests.${item.key}`)"
                        :icon="interestIcons[item.key]"
                        :model-value="form.interests.includes(item.key)"
                        @update:model-value="toggle(item.key, $event)"
                    />
                </li>
            </ul>

            <div class="flex max-w-xl flex-col gap-3">
                <div class="flex items-end gap-2">
                    <XInput
                        v-model="draft"
                        class="grow"
                        :label="$t('onboarding.interests.customLabel')"
                        :placeholder="$t('onboarding.interests.customPlaceholder')"
                        :error="form.errors.custom"
                        :disabled="form.custom.length >= MAX_CUSTOM"
                        maxlength="40"
                        @keydown.enter.prevent="addCustom"
                    />
                    <XButton
                        variant="secondary"
                        :icon="Plus"
                        :disabled="draft.trim() === '' || form.custom.length >= MAX_CUSTOM"
                        @click="addCustom"
                    >
                        {{ $t('onboarding.interests.customAdd') }}
                    </XButton>
                </div>
                <p v-if="form.custom.length >= MAX_CUSTOM" class="text-caption text-muted">
                    {{ $t('onboarding.interests.customLimit') }}
                </p>
                <ul v-if="form.custom.length" class="flex flex-wrap gap-2">
                    <li
                        v-for="label in form.custom"
                        :key="label"
                        class="inline-flex items-center gap-1 rounded-full bg-tint py-1 pl-3.5 pr-1 text-[14px] font-bold text-primary-strong"
                    >
                        {{ label }}
                        <button
                            type="button"
                            class="inline-flex size-8 items-center justify-center rounded-full hover:bg-surface"
                            :aria-label="$t('onboarding.interests.customRemove', { label })"
                            @click="form.custom = form.custom.filter((item) => item !== label)"
                        >
                            <X :size="16" aria-hidden="true" />
                        </button>
                    </li>
                </ul>
            </div>
        </form>

        <template #footer>
            <StepFooter
                :back-href="route('onboarding.child', onboarding.id)"
                form="univers"
                :processing="form.processing"
            />
        </template>
    </OnboardingLayout>
</template>
