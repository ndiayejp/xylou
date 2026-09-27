<script setup lang="ts">
import {
    Archive,
    ArchiveRestore,
    Clock,
    Copy,
    Globe,
    Pencil,
    RotateCcw,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import XAiBadge from '@/Components/ui/XAiBadge.vue';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XMenu, { type XMenuItem } from '@/Components/ui/XMenu.vue';
import XSubjectTag from '@/Components/ui/XSubjectTag.vue';
import XTag, { type XTagTone } from '@/Components/ui/XTag.vue';
import XUniverseScene from '@/Components/ui/XUniverseScene.vue';
import type { LibraryAction, LibraryActivity, LibraryStatus } from '../types';

const props = defineProps<{ activity: LibraryActivity; layout: 'grid' | 'list' }>();
const emit = defineEmits<{ action: [action: LibraryAction] }>();

const { t, locale } = useI18n();

const tones: Record<LibraryStatus, XTagTone> = {
    generating: 'neutral',
    generation_failed: 'neutral',
    pending_review: 'amber',
    draft: 'neutral',
    approved: 'green',
    archived: 'neutral',
};

const trashed = computed(() => props.activity.purgeAt !== null);

const purgeDate = computed(() =>
    props.activity.purgeAt === null
        ? null
        : new Intl.DateTimeFormat(locale.value, { dateStyle: 'long' }).format(
              new Date(props.activity.purgeAt),
          ),
);

const grades = computed(() => {
    const { gradeMin, gradeMax } = props.activity;
    if (gradeMin === null || gradeMax === null) {
        return null;
    }
    const min = t(`children.grades.${gradeMin}`);

    return gradeMin === gradeMax ? min : `${min}–${t(`children.grades.${gradeMax}`)}`;
});

// Seules les actions possibles dans l'état de l'activité.
const menu = computed<XMenuItem[]>(() => {
    const { status } = props.activity;
    const items: XMenuItem[] = [];
    if (['draft', 'approved'].includes(status)) {
        items.push({ key: 'edit', label: t('library.actions.edit'), icon: Pencil });
    }
    if (!['generating', 'generation_failed'].includes(status)) {
        items.push({ key: 'duplicate', label: t('library.actions.duplicate'), icon: Copy });
    }
    if (status === 'approved') {
        items.push({ key: 'archive', label: t('library.actions.archive'), icon: Archive });
    }
    if (status === 'archived') {
        items.push({
            key: 'unarchive',
            label: t('library.actions.unarchive'),
            icon: ArchiveRestore,
        });
    }
    items.push({ key: 'delete', label: t('library.actions.delete'), icon: Trash2, danger: true });

    return items;
});
</script>

<template>
    <XCard
        as="article"
        :class="[
            'flex gap-3',
            layout === 'grid' ? 'flex-col' : 'flex-row items-start',
            { 'opacity-90': activity.status === 'archived' },
        ]"
    >
        <div
            v-if="layout === 'grid'"
            class="h-28 overflow-hidden rounded-[14px] bg-mastery-discover-bg"
            aria-hidden="true"
        >
            <XUniverseScene v-if="activity.universe" :universe="activity.universe" />
        </div>

        <div class="flex min-w-0 flex-1 flex-col gap-2">
            <div class="flex items-start justify-between gap-2">
                <div class="flex flex-wrap gap-1.5 pt-2">
                    <XSubjectTag
                        :subject="activity.subject"
                        :label="$t(`subjects.${activity.subject}`)"
                        size="sm"
                    />
                    <XTag :tone="tones[activity.status]" size="sm">
                        {{ $t(`activities.statuses.${activity.status}`) }}
                    </XTag>
                    <XAiBadge v-if="activity.source === 'ai'" :label="$t('activities.ai')" />
                </div>
                <XMenu
                    v-if="!trashed"
                    :label="$t('library.card.actions', { title: activity.title })"
                    :items="menu"
                    class="-mr-2"
                    @select="emit('action', $event as LibraryAction)"
                />
            </div>
            <h3 class="text-[16px] font-extrabold">{{ activity.title }}</h3>
            <p class="text-[13px] text-muted">{{ activity.skill }}</p>
            <p class="flex flex-wrap gap-x-3.5 gap-y-1 text-[13px] text-muted">
                <span class="inline-flex items-center gap-1.5">
                    <Clock :size="15" aria-hidden="true" />
                    {{ $t('library.card.duration', { count: activity.durationMinutes }) }}
                </span>
                <span v-if="activity.universe" class="inline-flex items-center gap-1.5">
                    <Globe :size="15" aria-hidden="true" />
                    {{ $t(`universes.${activity.universe}`) }}
                </span>
                <span v-if="grades">{{ grades }}</span>
            </p>
            <div v-if="purgeDate" class="mt-1 flex flex-wrap items-center justify-between gap-2">
                <p class="text-[13px] font-semibold text-muted">
                    {{ $t('library.card.purge', { date: purgeDate }) }}
                </p>
                <XButton
                    variant="secondary"
                    size="sm"
                    :icon="RotateCcw"
                    @click="emit('action', 'restore')"
                >
                    {{ $t('library.card.restore') }}
                </XButton>
            </div>
        </div>
    </XCard>
</template>
