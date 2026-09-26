<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Pencil, UserPlus } from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import XAvatar from '@/Components/ui/XAvatar.vue';
import XButton from '@/Components/ui/XButton.vue';
import XCallout from '@/Components/ui/XCallout.vue';
import XTag from '@/Components/ui/XTag.vue';
import XUniverseScene, { type XUniverse } from '@/Components/ui/XUniverseScene.vue';
import { elides } from '@/i18n/elision';
import OnboardingLayout from '@/Layouts/OnboardingLayout.vue';
import { interestIcons } from './Partials/interestIcons';
import StepFooter from './Partials/StepFooter.vue';

// Écran 7 « Résumé » : chaque crayon ouvre l'écran de la section, qui ramène ici.
const props = defineProps<{
    onboarding: { id: number; reachedStep: number };
    childName: string | null;
    summary: {
        avatar: string | null;
        grade: string;
        age: number | null;
        interests: string[];
        customInterests: string[];
        goals: { goal: string; primary: boolean; note: string | null }[];
        difficulties: string[];
        styles: string[];
        sessionMinutes: number | null;
        gentleReminder: boolean;
        comfort: string[];
    };
}>();

const { t } = useI18n();
const name = computed(() => props.childName ?? '');
const universeTitle = computed(() =>
    t(elides(name.value) ? 'onboarding.summary.universeElided' : 'onboarding.summary.universe', {
        name: name.value,
    }),
);

// Décor : la première passion qui a sa scène, sinon la forêt.
const scene = computed<XUniverse>(
    () =>
        props.summary.interests.find(
            (key): key is XUniverse => key === 'space' || key === 'football',
        ) ?? 'forest',
);

const id = props.onboarding.id;
const sections = {
    profile: route('onboarding.child', id),
    interests: route('onboarding.interests', id),
    goals: route('onboarding.goals', id),
    difficulties: route('onboarding.difficulties', id),
    learning: route('onboarding.preferences', id),
    comfort: route('onboarding.child', id),
} as const;

const form = useForm({ add_child: false });
const submit = () => form.put(route('onboarding.summary.update', id));
function addChild(): void {
    form.add_child = true;
    submit();
}
</script>

<template>
    <OnboardingLayout
        :step="7"
        :title="$t('onboarding.summary.title')"
        :description="$t('onboarding.summary.description')"
        :home-href="route('home')"
        :back-href="sections.learning"
    >
        <form id="resume" class="flex max-w-5xl flex-col gap-6" novalidate @submit.prevent="submit">
            <section
                aria-labelledby="univers-titre"
                class="flex overflow-hidden rounded-[28px] border border-line bg-surface shadow-lift"
            >
                <div class="hidden w-[300px] shrink-0 lg:block">
                    <XUniverseScene :universe="scene" class="h-full w-full" />
                </div>
                <div class="flex min-w-0 grow flex-col gap-4 p-6 sm:px-8 sm:py-7">
                    <div class="flex items-center gap-3.5">
                        <XAvatar :name="name" size="lg" decorative />
                        <div>
                            <p
                                class="text-[12px] font-extrabold uppercase tracking-[0.08em] text-primary-strong"
                            >
                                {{ $t('onboarding.summary.created') }}
                            </p>
                            <h2 id="univers-titre" class="text-h2">{{ universeTitle }}</h2>
                        </div>
                    </div>

                    <dl>
                        <div
                            v-for="(href, section) in sections"
                            :key="section"
                            class="grid grid-cols-[minmax(0,1fr)_40px] items-center gap-x-3 gap-y-2 border-t border-line py-3 sm:grid-cols-[150px_minmax(0,1fr)_40px]"
                        >
                            <dt class="text-[13px] font-bold text-muted">
                                {{ $t(`onboarding.summary.sections.${section}`) }}
                            </dt>
                            <dd
                                class="col-span-2 row-start-2 flex flex-wrap gap-2 sm:col-span-1 sm:row-start-auto"
                            >
                                <template v-if="section === 'profile'">
                                    <XTag tone="indigo">{{
                                        $t(`children.grades.${summary.grade}`)
                                    }}</XTag>
                                    <XTag v-if="summary.age !== null" tone="neutral">
                                        {{ $t('children.age', summary.age) }}
                                    </XTag>
                                </template>
                                <template v-else-if="section === 'interests'">
                                    <XTag
                                        v-for="key in summary.interests"
                                        :key="key"
                                        tone="green"
                                        :icon="interestIcons[key]"
                                    >
                                        {{ $t(`interests.${key}`) }}
                                    </XTag>
                                    <XTag
                                        v-for="label in summary.customInterests"
                                        :key="label"
                                        tone="green"
                                    >
                                        {{ label }}
                                    </XTag>
                                </template>
                                <template v-else-if="section === 'goals'">
                                    <XTag
                                        v-for="goal in summary.goals"
                                        :key="goal.goal"
                                        :tone="goal.primary ? 'indigo' : 'neutral'"
                                    >
                                        {{
                                            goal.goal === 'other' && goal.note
                                                ? goal.note
                                                : $t(`goals.${goal.goal}`)
                                        }}
                                        <span v-if="goal.primary" class="font-semibold">
                                            · {{ $t('onboarding.summary.primary') }}
                                        </span>
                                    </XTag>
                                </template>
                                <template v-else-if="section === 'difficulties'">
                                    <XTag
                                        v-for="difficulty in summary.difficulties"
                                        :key="difficulty"
                                        tone="amber"
                                    >
                                        {{ $t(`difficulties.${difficulty}`) }}
                                    </XTag>
                                    <span
                                        v-if="summary.difficulties.length === 0"
                                        class="text-[15px] text-muted"
                                    >
                                        {{ $t('onboarding.summary.none.difficulties') }}
                                    </span>
                                </template>
                                <template v-else-if="section === 'learning'">
                                    <XTag
                                        v-for="style in summary.styles"
                                        :key="style"
                                        tone="indigo"
                                    >
                                        {{
                                            $t(`onboarding.preferences.styleOptions.${style}.title`)
                                        }}
                                    </XTag>
                                    <XTag v-if="summary.sessionMinutes !== null" tone="neutral">
                                        {{
                                            $t('onboarding.preferences.minutes', {
                                                count: summary.sessionMinutes,
                                            })
                                        }}
                                    </XTag>
                                    <XTag v-if="summary.gentleReminder" tone="neutral">
                                        {{ $t('onboarding.summary.reminder') }}
                                    </XTag>
                                    <span
                                        v-if="summary.styles.length === 0"
                                        class="text-[15px] text-muted"
                                    >
                                        {{ $t('onboarding.summary.none.learning') }}
                                    </span>
                                </template>
                                <template v-else>
                                    <XTag
                                        v-for="option in summary.comfort"
                                        :key="option"
                                        tone="neutral"
                                    >
                                        {{ $t(`onboarding.summary.comfort.${option}`) }}
                                    </XTag>
                                    <span
                                        v-if="summary.comfort.length === 0"
                                        class="text-[15px] text-muted"
                                    >
                                        {{ $t('onboarding.summary.none.comfort') }}
                                    </span>
                                </template>
                            </dd>
                            <dd class="col-start-2 row-start-1 justify-self-end sm:col-start-3">
                                <Link
                                    :href="href"
                                    :aria-label="
                                        $t('onboarding.summary.edit', {
                                            section: $t(`onboarding.summary.sections.${section}`),
                                        })
                                    "
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-button text-primary-strong hover:bg-tint"
                                >
                                    <Pencil :size="18" aria-hidden="true" />
                                </Link>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <XCallout tone="ai">{{ $t('onboarding.summary.approval', { name }) }}</XCallout>
        </form>

        <template #footer>
            <StepFooter
                :back-href="sections.learning"
                form="resume"
                :processing="form.processing"
                :submit-label="$t('onboarding.summary.submit')"
            >
                <template #secondary>
                    <XButton
                        variant="secondary"
                        size="lg"
                        :icon="UserPlus"
                        :disabled="form.processing"
                        @click="addChild"
                    >
                        {{ $t('onboarding.summary.addChild') }}
                    </XButton>
                </template>
            </StepFooter>
        </template>
    </OnboardingLayout>
</template>
