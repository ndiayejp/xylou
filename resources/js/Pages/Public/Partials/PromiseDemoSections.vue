<script setup lang="ts">
import { Check, HeartHandshake, Sparkles, Target } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import XUniverseScene, { type XUniverse } from '@/Components/ui/XUniverseScene.vue';
import SectionHeading from './SectionHeading.vue';

const pillars = [
    { key: 'personal', icon: Sparkles },
    { key: 'serious', icon: Target },
    { key: 'motivating', icon: HeartHandshake },
] as const;

const variants: { key: 'football' | 'space' | 'forest'; universe: XUniverse }[] = [
    { key: 'football', universe: 'football' },
    { key: 'space', universe: 'space' },
    { key: 'forest', universe: 'forest' },
];
</script>

<template>
    <section aria-labelledby="promesse" class="bg-surface px-4 py-16 md:px-10 lg:px-20 lg:py-20">
        <div class="flex flex-col gap-10">
            <SectionHeading
                id="promesse"
                :eyebrow="$t('landing.promise.eyebrow')"
                :title="$t('landing.promise.title')"
            />
            <ul class="grid gap-6 md:grid-cols-3">
                <li v-for="pillar in pillars" :key="pillar.key" class="flex flex-col gap-3">
                    <span class="flex size-12 items-center justify-center rounded-button bg-tint text-primary-text">
                        <component :is="pillar.icon" :size="24" aria-hidden="true" />
                    </span>
                    <h3 class="text-h3 !font-extrabold">{{ $t(`landing.promise.${pillar.key}.title`) }}</h3>
                    <p class="text-muted">{{ $t(`landing.promise.${pillar.key}.text`) }}</p>
                </li>
            </ul>
        </div>
    </section>

    <section aria-labelledby="demonstration" class="px-4 py-16 md:px-10 lg:px-20 lg:py-24">
        <div class="flex flex-col gap-10">
            <SectionHeading
                id="demonstration"
                :eyebrow="$t('landing.demo.eyebrow')"
                :title="$t('landing.demo.title')"
                :text="$t('landing.demo.text')"
            />
            <div class="grid gap-6 lg:grid-cols-4">
                <XCard padding="lg" class="flex flex-col gap-3 bg-well">
                    <p class="text-caption uppercase tracking-[.06em] text-muted">
                        {{ $t('landing.demo.classic.label') }}
                    </p>
                    <p class="text-[17px] font-semibold">{{ $t('landing.demo.classic.statement') }}</p>
                    <p class="text-caption !font-medium text-muted">{{ $t('landing.demo.classic.verdict') }}</p>
                </XCard>
                <XCard v-for="variant in variants" :key="variant.key" as="article" size="kid">
                    <template #media>
                        <XUniverseScene
                            :universe="variant.universe"
                            :label="$t(`landing.demo.${variant.key}.alt`)"
                        />
                    </template>
                    <p class="text-[18px] font-bold text-primary-text">
                        {{ $t(`landing.demo.${variant.key}.for`) }}
                    </p>
                    <h3 class="text-kid-body !font-black">{{ $t(`landing.demo.${variant.key}.title`) }}</h3>
                    <p class="text-[18px] leading-[1.45]">{{ $t(`landing.demo.${variant.key}.statement`) }}</p>
                </XCard>
            </div>
            <ul class="flex flex-wrap gap-x-6 gap-y-2 font-semibold text-success-text">
                <li v-for="key in ['skill', 'level', 'answer']" :key="key" class="flex items-center gap-2">
                    <Check :size="18" aria-hidden="true" />
                    {{ $t(`landing.demo.same.${key}`) }}
                </li>
            </ul>
        </div>
    </section>
</template>
