<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronDown, Mail } from '@lucide/vue';
import XAvatar from '@/Components/ui/XAvatar.vue';
import XCard from '@/Components/ui/XCard.vue';
import XTag from '@/Components/ui/XTag.vue';
import { useMessageList } from '@/Composables/useMessageList';
import SectionHeading from './SectionHeading.vue';

defineProps<{ registerHref: string; contactEmail: string }>();

const { records } = useMessageList();
const testimonials = records('landing.testimonials.items', ['quote', 'name', 'role'] as const);
const questions = records('landing.faq.items', ['question', 'answer'] as const);
</script>

<template>
    <section aria-labelledby="temoignages" class="px-4 pb-16 pt-16 md:px-10 lg:px-20 lg:pt-24">
        <div class="flex flex-col gap-8">
            <SectionHeading
                id="temoignages"
                :eyebrow="$t('landing.testimonials.eyebrow')"
                :title="$t('landing.testimonials.title')"
                :text="$t('landing.testimonials.disclaimer')"
            />
            <ul class="grid gap-6 md:grid-cols-3">
                <li v-for="item in testimonials" :key="item.name">
                    <XCard as="figure" padding="lg" class="flex h-full flex-col gap-4">
                        <XTag tone="neutral" size="sm" class="self-start">
                            {{ $t('landing.testimonials.badge') }}
                        </XTag>
                        <blockquote class="grow text-[16px] leading-[1.55]">{{ item.quote }}</blockquote>
                        <figcaption class="flex items-center gap-3">
                            <XAvatar :name="item.name" decorative />
                            <span>
                                <span class="block font-bold">{{ item.name }}</span>
                                <span class="block text-caption !font-medium text-muted">{{ item.role }}</span>
                            </span>
                        </figcaption>
                    </XCard>
                </li>
            </ul>
        </div>
    </section>

    <section
        id="faq"
        aria-labelledby="faq-titre"
        class="flex scroll-mt-6 flex-col gap-10 px-4 py-16 md:px-10 lg:flex-row lg:gap-16 lg:px-20"
    >
        <div class="flex flex-col gap-5 lg:w-[360px] lg:shrink-0">
            <SectionHeading
                id="faq-titre"
                :eyebrow="$t('landing.faq.eyebrow')"
                :title="$t('landing.faq.title')"
                :text="$t('landing.faq.text')"
            />
            <a
                :href="`mailto:${contactEmail}`"
                class="inline-flex h-12 items-center gap-2 self-start rounded-[14px] border-[1.5px] border-line bg-surface px-5 font-bold hover:bg-bg"
            >
                <Mail :size="18" aria-hidden="true" />
                {{ $t('landing.faq.contact') }}
            </a>
        </div>
        <div class="flex grow flex-col gap-3">
            <details
                v-for="(item, index) in questions"
                :key="item.question"
                class="group rounded-card border border-line bg-surface px-5 py-4"
                :open="index === 0"
            >
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-4 text-[17px] font-bold [&::-webkit-details-marker]:hidden"
                >
                    {{ item.question }}
                    <ChevronDown
                        :size="20"
                        class="shrink-0 transition-transform duration-hover group-open:rotate-180"
                        aria-hidden="true"
                    />
                </summary>
                <p class="mt-3 leading-[1.55] text-muted">{{ item.answer }}</p>
            </details>
        </div>
    </section>

    <section
        aria-labelledby="appel-final"
        class="mx-4 mb-16 mt-8 flex flex-col gap-6 rounded-[36px] bg-accent p-8 md:mx-10 md:p-12 lg:mx-20 lg:mb-20 lg:flex-row lg:items-center lg:justify-between lg:p-16"
    >
        <div class="flex flex-col gap-3">
            <h2 id="appel-final" class="text-[28px] font-extrabold leading-[1.2] tracking-[-0.02em] md:text-[36px]">
                {{ $t('landing.final.title') }}
            </h2>
            <p class="text-[17px] font-semibold">{{ $t('landing.final.text') }}</p>
        </div>
        <Link
            :href="registerHref"
            class="inline-flex h-14 shrink-0 items-center self-start rounded-button bg-text px-7 text-[16px] font-bold text-surface transition duration-hover hover:-translate-y-px lg:self-auto"
        >
            {{ $t('landing.final.cta') }}
        </Link>
    </section>
</template>
