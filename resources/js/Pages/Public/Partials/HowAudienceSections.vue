<script setup lang="ts">
import { Check } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import { useMessageList } from '@/Composables/useMessageList';
import SectionHeading from './SectionHeading.vue';

const { list } = useMessageList();
const steps = ['profile', 'universe', 'validate', 'follow'] as const;
const audiences = ['kid', 'parents', 'pros'] as const;
</script>

<template>
    <section
        id="comment-ca-marche"
        aria-labelledby="comment-titre"
        class="scroll-mt-6 bg-tint px-4 py-16 md:px-10 lg:px-20 lg:py-24"
    >
        <div class="flex flex-col gap-10">
            <SectionHeading
                id="comment-titre"
                :eyebrow="$t('landing.how.eyebrow')"
                :title="$t('landing.how.title')"
            />
            <ol class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <li v-for="(step, index) in steps" :key="step">
                    <XCard padding="lg" class="flex h-full flex-col gap-3">
                        <span class="text-[32px] font-extrabold text-primary" aria-hidden="true">
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>
                        <h3 class="text-h3 !font-extrabold">{{ $t(`landing.how.steps.${step}.title`) }}</h3>
                        <p class="text-muted">{{ $t(`landing.how.steps.${step}.text`) }}</p>
                    </XCard>
                </li>
            </ol>
        </div>
    </section>

    <section
        id="pour-qui"
        aria-labelledby="pour-qui-titre"
        class="scroll-mt-6 bg-surface px-4 py-16 md:px-10 lg:px-20 lg:py-24"
    >
        <div class="flex flex-col gap-10">
            <SectionHeading
                id="pour-qui-titre"
                :eyebrow="$t('landing.audiences.eyebrow')"
                :title="$t('landing.audiences.title')"
            />
            <div class="grid gap-6 md:grid-cols-3">
                <XCard v-for="audience in audiences" :key="audience" padding="lg" class="flex flex-col gap-4">
                    <h3 class="text-h3 !font-extrabold">{{ $t(`landing.audiences.${audience}.title`) }}</h3>
                    <ul class="flex flex-col gap-3">
                        <li
                            v-for="item in list(`landing.audiences.${audience}.items`)"
                            :key="item"
                            class="flex gap-2.5"
                        >
                            <Check :size="20" class="mt-0.5 shrink-0 text-success-text" aria-hidden="true" />
                            {{ item }}
                        </li>
                    </ul>
                </XCard>
            </div>
        </div>
    </section>
</template>
