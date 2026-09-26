<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Check } from '@lucide/vue';
import { computed } from 'vue';
import XLogo from '@/Components/ui/XLogo.vue';
import XMascot from '@/Components/ui/XMascot.vue';
import XProgressBar from '@/Components/ui/XProgressBar.vue';
import NavigationProgress from './partials/NavigationProgress.vue';
import SkipLink from './partials/SkipLink.vue';

// Onboarding : colonne des 7 étapes (bureau), barre de progression (mobile), pied de page d'actions.
const props = withDefaults(
    defineProps<{
        step: number;
        title: string;
        description?: string;
        homeHref: string;
        backHref?: string;
    }>(),
    { description: undefined, backHref: undefined },
);

const TOTAL = 7;
const steps = Array.from({ length: TOTAL }, (_, index) => index + 1);
const percent = computed(() => Math.round((props.step / TOTAL) * 100));
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen bg-bg text-text">
        <SkipLink />
        <NavigationProgress />

        <aside
            class="relative hidden w-[340px] shrink-0 flex-col gap-9 overflow-hidden bg-primary-strong px-8 py-9 text-surface lg:flex"
        >
            <span
                class="absolute -bottom-20 -right-20 size-64 rounded-full bg-primary"
                aria-hidden="true"
            />
            <Link
                :href="homeHref"
                :aria-label="$t('layout.homeLink')"
                class="self-start rounded-input"
            >
                <XLogo inverse />
            </Link>
            <nav :aria-label="$t('onboarding.stepsLabel')">
                <ol class="flex flex-col gap-[18px]">
                    <li
                        v-for="item in steps"
                        :key="item"
                        :aria-current="item === step ? 'step' : undefined"
                        class="flex items-center gap-3 text-[15px]"
                        :class="
                            item <= step ? 'font-bold text-surface' : 'font-semibold text-[#D9DAFB]'
                        "
                    >
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full text-[13px] font-extrabold"
                            :class="{
                                'bg-success text-text': item < step,
                                'bg-surface text-primary-text': item === step,
                                'border-2 border-[#D9DAFB]/60': item > step,
                            }"
                            aria-hidden="true"
                        >
                            <Check v-if="item < step" :size="15" :stroke-width="3" />
                            <template v-else>{{ item }}</template>
                        </span>
                        {{ $t(`onboarding.steps.${item}`) }}
                        <span v-if="item < step" class="sr-only"
                            >({{ $t('onboarding.done') }})</span
                        >
                    </li>
                </ol>
            </nav>
            <div class="grow" />
            <div class="relative flex items-end gap-3.5">
                <XMascot :size="72" />
                <p
                    class="rounded-[18px] rounded-bl-[4px] bg-surface px-3.5 py-3 text-caption text-text"
                >
                    {{ $t('onboarding.reassurance') }}
                </p>
            </div>
        </aside>

        <div class="flex min-w-0 grow flex-col">
            <div class="flex flex-col gap-2.5 px-4 pt-4 md:px-10 lg:px-14 lg:pt-7">
                <div class="flex items-center justify-between gap-3">
                    <Link
                        v-if="backHref"
                        :href="backHref"
                        :aria-label="$t('onboarding.back')"
                        class="inline-flex size-11 items-center justify-center rounded-input text-muted hover:bg-surface lg:hidden"
                    >
                        <ArrowLeft :size="20" aria-hidden="true" />
                    </Link>
                    <span class="text-caption text-muted">
                        {{ $t('onboarding.progress', { step, total: 7 }) }}
                    </span>
                    <span class="text-caption text-muted" aria-hidden="true">{{ percent }} %</span>
                </div>
                <XProgressBar
                    :value="step"
                    :max="7"
                    :label="$t('onboarding.progress', { step, total: 7 })"
                    :value-text="$t('onboarding.progress', { step, total: 7 })"
                    hide-value
                />
            </div>

            <main id="contenu" class="flex grow flex-col gap-7 px-4 py-8 md:px-10 lg:px-14 lg:py-9">
                <div class="flex max-w-3xl flex-col gap-2">
                    <h1
                        class="text-[28px] font-extrabold leading-[1.2] tracking-[-0.02em] md:text-[34px]"
                    >
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-[16px] leading-[1.55] text-muted">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </main>

            <footer
                v-if="$slots.footer"
                class="sticky bottom-0 flex flex-wrap items-center justify-between gap-3 border-t border-line bg-surface px-4 py-4 md:px-10 lg:px-14 lg:py-5"
            >
                <slot name="footer" />
            </footer>
        </div>
    </div>
</template>
