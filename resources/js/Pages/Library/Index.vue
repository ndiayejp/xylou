<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    CloudOff,
    RotateCw,
    ChevronRight,
    Library,
    Plus,
    Search,
    SearchX,
    Sparkles,
    Trash2,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XEmptyState from '@/Components/ui/XEmptyState.vue';
import XFilterSelect, { type XFilterOption } from '@/Components/ui/XFilterSelect.vue';
import XInput from '@/Components/ui/XInput.vue';
import XSegmented from '@/Components/ui/XSegmented.vue';
import XSkeleton from '@/Components/ui/XSkeleton.vue';
import { useFailedVisit } from '@/Composables/useFailedVisit';
import { useToasts } from '@/Composables/useToasts';
import AccountSpace from '@/Layouts/AccountSpace.vue';
import ActivityCard from './Partials/ActivityCard.vue';
import type {
    LibraryAction,
    LibraryActivity,
    LibraryFilterKey,
    LibraryFilters,
    LibraryOptions,
    LibraryUnderstood,
} from './types';

const props = defineProps<{
    activities: {
        data: LibraryActivity[];
        total: number;
        prev: string | null;
        next: string | null;
    };
    total: number;
    filters: LibraryFilters;
    options: LibraryOptions;
    space: 'parent' | 'pro';
    understood: LibraryUnderstood | null;
}>();

const { t } = useI18n();
const { push } = useToasts();

const TRASH = 'deleted';
const layout = ref<'grid' | 'list'>('grid');
const loading = ref(false);

// Filtres en cours : à jour dès le choix, pour que deux changements rapides se cumulent.
const state = ref<LibraryFilters>({ ...props.filters });
watch(
    () => props.filters,
    (filters) => (state.value = { ...filters }),
);

// Filtres : chaque changement recharge la page (URL partageable). « grade » est toujours envoyé :
// vide, il annule le niveau pré-rempli avec la classe de l'enfant.
const { failed, track } = useFailedVisit();

// Chargement de la liste : « Réessayer » rejoue le dernier.
let lastLoad: () => void = () => router.reload(track(loadOptions()));

function loadOptions() {
    return {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    };
}

function load(url: string, data: Record<string, string> = {}): void {
    lastLoad = () => router.get(url, data, track(loadOptions()));
    lastLoad();
}

function retry(): void {
    lastLoad();
}

function visit(extra: Record<string, string> = {}): void {
    const query = Object.fromEntries(
        Object.entries(state.value).filter(([key, value]) => value !== '' || key === 'grade'),
    );
    load(route(`${props.space}.library`), { ...query, ...extra });
}

function apply(next: Partial<LibraryFilters>): void {
    state.value = { ...state.value, ...next };
    visit();
}

// Recherche en langage naturel : le serveur remplit les pastilles avec ce qu'il comprend.
const sentence = ref(props.filters.q);
function ask(): void {
    const text = sentence.value.trim();
    if (text === '') {
        apply({ q: '' });
    } else {
        state.value = { ...state.value, q: '' };
        visit({ ask: text });
    }
}

const understoodText = computed(() => {
    const u = props.understood;
    if (!u) {
        return null;
    }
    const parts = [
        u.subject && t(`subjects.${u.subject}`),
        u.grade &&
            (u.child
                ? t('library.search.gradeOf', {
                      grade: t(`children.grades.${u.grade}`),
                      name: u.child,
                  })
                : t(`children.grades.${u.grade}`)),
        u.duration && durationLabel(u.duration),
        u.difficulty && t(`activities.difficulties.${u.difficulty}`),
        u.universe && t(`universes.${u.universe}`),
        u.text && t('library.search.text', { text: u.text }),
    ].filter(Boolean);

    return parts.join(' · ');
});

function durationLabel(value: string): string {
    return ['short', 'long'].includes(value)
        ? t(`library.filters.durations.${value}`)
        : t('library.filters.minutes', { count: Number(value) });
}

function model(key: LibraryFilterKey) {
    return computed({
        get: () => (state.value[key] === '' ? null : state.value[key]),
        set: (value: string | null) =>
            // Une compétence n'a de sens que dans sa matière.
            apply(key === 'subject' ? { subject: value ?? '', skill: '' } : { [key]: value ?? '' }),
    });
}

const filterModels = {
    subject: model('subject'),
    skill: model('skill'),
    grade: model('grade'),
    duration: model('duration'),
    difficulty: model('difficulty'),
    universe: model('universe'),
    status: model('status'),
};

function choice(value: string, label: string): XFilterOption {
    return { value, label };
}

const filterBar = computed(() => {
    const o = props.options;
    const entries: {
        key: LibraryFilterKey;
        all: string;
        options: XFilterOption[];
    }[] = [
        {
            key: 'subject',
            all: t('library.filters.allSubjects'),
            options: o.subjects.map((s) => choice(s, t(`subjects.${s}`))),
        },
        {
            key: 'skill',
            all: t('library.filters.allSkills'),
            options: o.skills.map((s) => choice(String(s.id), s.label)),
        },
        {
            key: 'grade',
            all: t('library.filters.allGrades'),
            options: o.grades.map((g) => choice(g, t(`children.grades.${g}`))),
        },
        {
            key: 'duration',
            all: t('library.filters.allDurations'),
            options: [...o.durations.map(String), 'short', 'long'].map((m) =>
                choice(m, durationLabel(m)),
            ),
        },
        {
            key: 'difficulty',
            all: t('library.filters.allDifficulties'),
            options: o.difficulties.map((d) => choice(d, t(`activities.difficulties.${d}`))),
        },
        {
            key: 'universe',
            all: t('library.filters.allUniverses'),
            options: o.universes.map((u) => choice(u, t(`universes.${u}`))),
        },
        {
            key: 'status',
            all: t('library.filters.allStatuses'),
            options: [
                ...o.statuses.map((s) => choice(s, t(`activities.statuses.${s}`))),
                choice(TRASH, t('library.filters.deleted')),
            ],
        },
    ];

    return entries.map((entry) => {
        const label = t(`library.filters.${entry.key}`);
        const selected = entry.options.find((option) => option.value === state.value[entry.key]);

        return {
            ...entry,
            label,
            summary: selected
                ? t('library.filters.summary', { label, value: selected.label })
                : undefined,
        };
    });
});

const hasFilters = computed(() => Object.values(state.value).some((value) => value !== ''));
const inTrash = computed(() => props.filters.status === TRASH);

function clearFilters(): void {
    apply({
        subject: '',
        skill: '',
        grade: '',
        duration: '',
        difficulty: '',
        universe: '',
        status: '',
        q: '',
    });
    sentence.value = '';
}

// Actions sur une carte : la liste se recharge, un toast confirme (avec « Annuler » si réversible).
function act(activity: LibraryActivity, action: LibraryAction): void {
    if (action === 'edit') {
        router.visit(route('activities.edit', activity.id));
        return;
    }
    const requests: Record<
        Exclude<LibraryAction, 'edit'>,
        { method: 'post' | 'delete'; route: string }
    > = {
        duplicate: { method: 'post', route: 'activities.duplicate' },
        archive: { method: 'post', route: 'activities.archive' },
        unarchive: { method: 'delete', route: 'activities.unarchive' },
        delete: { method: 'delete', route: 'activities.destroy' },
        restore: { method: 'post', route: 'activities.restore' },
    };
    const undo: Partial<Record<LibraryAction, Exclude<LibraryAction, 'edit'>>> = {
        archive: 'unarchive',
        delete: 'restore',
    };
    const toasts: Record<Exclude<LibraryAction, 'edit'>, string> = {
        duplicate: 'library.toasts.duplicated',
        archive: 'library.toasts.archived',
        unarchive: 'library.toasts.unarchived',
        delete: 'library.toasts.deleted',
        restore: 'library.toasts.restored',
    };
    const { method, route: name } = requests[action];
    const reverse = undo[action];

    router.visit(
        route(name, activity.id),
        track(
            {
                method,
                preserveScroll: true,
                onSuccess: () =>
                    push({
                        message: t(toasts[action]),
                        ...(reverse && {
                            actionLabel: t('library.toasts.undo'),
                            onAction: () => act(activity, reverse),
                        }),
                    }),
                onError: (errors) =>
                    push({ message: errors.activity ?? t('library.toasts.error') }),
            },
            () => push({ message: t('library.toasts.error') }),
        ),
    );
}
</script>

<template>
    <Head :title="$t('library.title')" />

    <AccountSpace>
        <template #header>
            <h1 class="text-h2 md:text-h1">{{ $t('library.title') }}</h1>
            <p class="mt-1 text-[14px] text-muted">
                {{ $t('library.count', total) }} · {{ $t('library.subtitle') }}
            </p>
        </template>
        <template #actions>
            <XButton :icon="Plus" @click="router.visit(route('activities.create'))">
                {{ $t('library.create') }}
            </XButton>
        </template>

        <XCard v-if="total === 0 && !inTrash" padding="lg" class="flex min-h-[300px] items-center">
            <XEmptyState
                :icon="Library"
                :title="$t('library.empty.title')"
                :description="$t('library.empty.text')"
                class="mx-auto"
            />
        </XCard>

        <template v-else>
            <form role="search" class="mb-3" @submit.prevent="ask">
                <XInput
                    v-model="sentence"
                    type="search"
                    :icon="Search"
                    :label="$t('library.search.label')"
                    hide-label
                    :placeholder="$t('library.search.placeholder')"
                    enterkeyhint="search"
                />
            </form>
            <p
                v-if="understoodText"
                class="mb-4 flex items-center gap-2 text-[14px] text-muted"
                role="status"
            >
                <Sparkles :size="16" aria-hidden="true" class="text-primary-text" />
                {{ $t('library.search.understood', { summary: understoodText }) }}
            </p>

            <div class="mb-5">
                <div
                    role="group"
                    :aria-label="$t('library.filters.label')"
                    class="flex flex-wrap items-center gap-2"
                >
                    <XFilterSelect
                        v-for="filter in filterBar"
                        :key="filter.key"
                        v-model="filterModels[filter.key].value"
                        :label="filter.label"
                        :all-label="filter.all"
                        :summary="filter.summary"
                        :options="filter.options"
                    />
                    <XButton v-if="hasFilters" variant="ghost" size="sm" @click="clearFilters">
                        {{ $t('library.filters.clear') }}
                    </XButton>
                </div>
            </div>

            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[14px] font-semibold text-muted" aria-live="polite">
                    {{
                        loading
                            ? $t('library.loading')
                            : filters.q
                              ? $t('library.resultsFor', { text: filters.q }, activities.total)
                              : $t('library.results', activities.total)
                    }}
                </p>
                <XSegmented
                    v-model="layout"
                    class="w-44 shrink-0"
                    :label="$t('library.view.label')"
                    :options="[
                        { value: 'grid', label: $t('library.view.grid') },
                        { value: 'list', label: $t('library.view.list') },
                    ]"
                />
            </div>

            <div v-if="loading" aria-busy="true" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <XCard v-for="n in 6" :key="n" class="flex flex-col gap-3">
                    <XSkeleton shape="block" />
                    <XSkeleton class="w-1/2" />
                    <XSkeleton class="w-3/4" />
                </XCard>
            </div>

            <XCard v-else-if="failed" padding="lg" class="flex min-h-[260px] items-center">
                <XEmptyState
                    :icon="CloudOff"
                    tone="red"
                    :title="$t('library.failed.title')"
                    :description="$t('library.failed.text')"
                    class="mx-auto"
                    role="alert"
                >
                    <template #actions>
                        <XButton :icon="RotateCw" @click="retry">
                            {{ $t('library.failed.retry') }}
                        </XButton>
                    </template>
                </XEmptyState>
            </XCard>

            <XCard
                v-else-if="activities.data.length === 0"
                padding="lg"
                class="flex min-h-[260px] items-center"
            >
                <XEmptyState
                    v-if="inTrash"
                    :icon="Trash2"
                    tone="neutral"
                    :title="$t('library.trashEmpty.title')"
                    :description="$t('library.trashEmpty.text')"
                    class="mx-auto"
                />
                <XEmptyState
                    v-else
                    :icon="SearchX"
                    tone="neutral"
                    :title="$t('library.noResults.title')"
                    :description="$t('library.noResults.text')"
                    class="mx-auto"
                >
                    <template #actions>
                        <XButton variant="secondary" @click="clearFilters">
                            {{ $t('library.filters.clear') }}
                        </XButton>
                    </template>
                </XEmptyState>
            </XCard>

            <ul
                v-else
                :class="
                    layout === 'grid'
                        ? 'grid gap-4 sm:grid-cols-2 xl:grid-cols-3'
                        : 'flex flex-col gap-3'
                "
            >
                <li v-for="activity in activities.data" :key="activity.id">
                    <ActivityCard
                        :activity="activity"
                        :layout="layout"
                        class="h-full"
                        @action="act(activity, $event)"
                    />
                </li>
            </ul>

            <nav
                v-if="activities.prev || activities.next"
                :aria-label="$t('library.pagination.label')"
                class="mt-6 flex justify-between"
            >
                <a
                    v-if="activities.prev"
                    :href="activities.prev"
                    class="link inline-flex items-center gap-1"
                    @click.prevent="load(activities.prev)"
                >
                    <ChevronLeft :size="18" aria-hidden="true" />
                    {{ $t('library.pagination.previous') }}
                </a>
                <span v-else />
                <a
                    v-if="activities.next"
                    :href="activities.next"
                    class="link inline-flex items-center gap-1"
                    @click.prevent="load(activities.next)"
                >
                    {{ $t('library.pagination.next') }}
                    <ChevronRight :size="18" aria-hidden="true" />
                </a>
            </nav>
        </template>
    </AccountSpace>
</template>
