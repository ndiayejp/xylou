<script setup lang="ts">
import { Lock, Share2, Sparkles, Trash2 } from '@lucide/vue';
import XAiBadge from '@/Components/ui/XAiBadge.vue';
import XAvatar from '@/Components/ui/XAvatar.vue';
import XCard from '@/Components/ui/XCard.vue';
import XLevelBadge from '@/Components/ui/XLevelBadge.vue';
import XProgressBar from '@/Components/ui/XProgressBar.vue';
import XTag from '@/Components/ui/XTag.vue';
import SectionHeading from './SectionHeading.vue';

const promises = [
    { key: 'collect', icon: Lock },
    { key: 'share', icon: Share2 },
    { key: 'ai', icon: Sparkles },
    { key: 'delete', icon: Trash2 },
] as const;
</script>

<template>
    <section
        aria-labelledby="exemple"
        class="flex flex-col gap-10 px-4 py-16 md:px-10 lg:flex-row lg:items-center lg:gap-14 lg:px-20 lg:py-24"
    >
        <div class="flex flex-1 flex-col gap-5">
            <SectionHeading
                id="exemple"
                :eyebrow="$t('landing.example.eyebrow')"
                :title="$t('landing.example.title')"
                :text="$t('landing.example.text')"
            />
            <div class="flex flex-wrap gap-2">
                <XTag tone="indigo">{{ $t('landing.example.tags.level') }}</XTag>
                <XTag tone="green">{{ $t('landing.example.tags.passions') }}</XTag>
                <XTag tone="amber">{{ $t('landing.example.tags.steps') }}</XTag>
            </div>
        </div>

        <XCard as="figure" padding="lg" class="flex w-full max-w-[480px] flex-col gap-5 shadow-card">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <XAvatar name="Lucas" color="teal" decorative />
                    <p class="font-extrabold">{{ $t('landing.example.preview.title') }}</p>
                </div>
                <XAiBadge :label="$t('landing.example.preview.ai')" />
            </div>
            <p class="leading-[1.55]">{{ $t('landing.example.preview.summary') }}</p>
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold">{{ $t('landing.example.preview.fractions') }}</span>
                    <XLevelBadge level="progressing" :label="$t('landing.example.preview.progressing')" />
                </div>
                <XProgressBar
                    :value="80"
                    tone="progressing"
                    :label="$t('landing.example.preview.fractions')"
                    value-text="80 %"
                />
            </div>
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold">{{ $t('landing.example.preview.problems') }}</span>
                    <XLevelBadge level="consolidate" :label="$t('landing.example.preview.consolidate')" />
                </div>
                <XProgressBar
                    :value="60"
                    tone="consolidate"
                    :label="$t('landing.example.preview.problems')"
                    value-text="60 %"
                />
            </div>
            <figcaption class="text-caption !font-medium text-muted">
                {{ $t('landing.example.preview.caption') }}
            </figcaption>
        </XCard>
    </section>

    <section
        id="confiance"
        aria-labelledby="confiance-titre"
        class="mx-4 flex scroll-mt-6 flex-col gap-10 rounded-[36px] bg-[#2B2C7A] p-8 text-surface md:mx-10 md:p-12 lg:mx-20 lg:flex-row lg:items-center lg:gap-14 lg:p-16"
    >
        <div class="flex flex-1 flex-col gap-4">
            <h2 id="confiance-titre" class="text-[30px] font-extrabold leading-[1.15] tracking-[-0.02em] md:text-[36px]">
                {{ $t('landing.trust.title') }}
            </h2>
            <p class="text-[17px] leading-[1.55] text-[#D9DAFB]">{{ $t('landing.trust.text') }}</p>
        </div>
        <ul class="grid flex-1 gap-6 sm:grid-cols-2">
            <li v-for="promise in promises" :key="promise.key" class="flex flex-col gap-2">
                <component :is="promise.icon" :size="24" class="text-accent" aria-hidden="true" />
                <h3 class="text-[17px] font-extrabold">{{ $t(`landing.trust.${promise.key}.title`) }}</h3>
                <p class="text-[15px] leading-[1.55] text-[#D9DAFB]">{{ $t(`landing.trust.${promise.key}.text`) }}</p>
            </li>
        </ul>
    </section>
</template>
