<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import XCallout from '@/Components/ui/XCallout.vue';
import { useMessageList } from '@/Composables/useMessageList';
import PublicSpace from '@/Layouts/PublicSpace.vue';
import SeoHead from '@/Layouts/partials/SeoHead.vue';

const props = defineProps<{
    page: 'notice' | 'privacy' | 'terms' | 'accessibility';
    contactEmail: string;
    updatedAt: string;
}>();

const { tm, rt, d } = useI18n();
const { list } = useMessageList();

interface Section {
    id: string;
    title: string;
    paragraphs: string[];
}

const sections = computed<Section[]>(() => {
    const raw: unknown = tm(`legal.${props.page}.sections`);
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw.map((section: Record<string, never>, index: number) => ({
        id: rt(section.id),
        title: rt(section.title),
        paragraphs: list(`legal.${props.page}.sections.${index}.paragraphs`),
    }));
});

const updated = computed(() => d(new Date(props.updatedAt), { dateStyle: 'long' }));
</script>

<template>
    <SeoHead :title="$t(`legal.${page}.title`)" :description="$t(`legal.${page}.description`)" />

    <PublicSpace :contact-email="contactEmail">
        <article class="mx-auto flex max-w-3xl flex-col gap-8 px-4 pb-20 pt-6 md:px-10">
            <header class="flex flex-col gap-3">
                <h1 class="text-h1">{{ $t(`legal.${page}.title`) }}</h1>
                <p class="text-caption text-muted">{{ $t('legal.updated', { date: updated }) }}</p>
                <p class="text-[17px] leading-[1.55] text-muted">{{ $t(`legal.${page}.intro`) }}</p>
            </header>

            <XCallout tone="warn">{{ $t('legal.draft') }}</XCallout>

            <nav :aria-label="$t('legal.toc')" class="rounded-card bg-surface p-5">
                <p class="mb-2 font-bold">{{ $t('legal.toc') }}</p>
                <ul class="flex flex-col gap-1.5">
                    <li v-for="section in sections" :key="section.id">
                        <a :href="`#${section.id}`" class="link">{{ section.title }}</a>
                    </li>
                </ul>
            </nav>

            <section
                v-for="section in sections"
                :id="section.id"
                :key="section.id"
                :aria-labelledby="`${section.id}-titre`"
                class="flex scroll-mt-6 flex-col gap-3"
            >
                <h2 :id="`${section.id}-titre`" class="text-h2">{{ section.title }}</h2>
                <p v-for="paragraph in section.paragraphs" :key="paragraph" class="leading-[1.65]">
                    {{ paragraph }}
                </p>
            </section>

            <p class="font-semibold">
                {{ $t('legal.contact', { email: contactEmail }) }}
            </p>
        </article>
    </PublicSpace>
</template>
