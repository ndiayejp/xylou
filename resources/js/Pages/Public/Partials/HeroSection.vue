<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Clock, EyeOff, Play, ShieldCheck, UserCheck } from '@lucide/vue';
import XCard from '@/Components/ui/XCard.vue';
import XSubjectTag from '@/Components/ui/XSubjectTag.vue';
import XTag from '@/Components/ui/XTag.vue';
import XUniverseScene from '@/Components/ui/XUniverseScene.vue';

defineProps<{ registerHref: string }>();

const badges = [
    { key: 'noAds', icon: EyeOff },
    { key: 'validated', icon: UserCheck },
    { key: 'control', icon: ShieldCheck },
] as const;
</script>

<template>
    <section
        aria-labelledby="hero-titre"
        class="flex flex-col items-center gap-10 px-4 pb-16 pt-6 md:px-10 lg:flex-row lg:justify-between lg:px-20 lg:pb-20 lg:pt-10"
    >
        <div class="flex max-w-[600px] flex-col gap-6">
            <p class="text-[12px] font-extrabold uppercase tracking-[.08em] text-primary-text">
                {{ $t('landing.hero.eyebrow') }}
            </p>
            <h1
                id="hero-titre"
                class="text-[40px] font-extrabold leading-[1.05] tracking-[-0.035em] md:text-[60px]"
            >
                {{ $t('landing.hero.titleStart') }}
                <span class="text-primary">{{ $t('landing.hero.titleHighlight') }}</span
                >{{ $t('landing.hero.titleEnd') }}
            </h1>
            <p class="text-[18px] leading-[1.55] text-muted">{{ $t('landing.hero.text') }}</p>
            <div class="flex flex-wrap gap-3">
                <Link
                    :href="registerHref"
                    class="inline-flex h-14 items-center rounded-button bg-primary px-7 text-[16px] font-bold text-surface transition duration-hover hover:-translate-y-px hover:shadow-lift"
                >
                    {{ $t('landing.hero.cta') }}
                </Link>
                <a
                    href="#comment-ca-marche"
                    class="inline-flex h-14 items-center rounded-button px-5 text-[16px] font-bold text-primary-strong hover:bg-tint"
                >
                    {{ $t('landing.hero.secondary') }}
                </a>
            </div>
            <ul class="flex flex-wrap gap-x-5 gap-y-2 text-[14px] font-semibold text-muted">
                <li v-for="badge in badges" :key="badge.key" class="flex items-center gap-2">
                    <component :is="badge.icon" :size="18" class="text-success-text" aria-hidden="true" />
                    {{ $t(`landing.hero.badges.${badge.key}`) }}
                </li>
            </ul>
        </div>

        <!-- Aperçu illustratif de l'espace enfant (décoratif pour la structure, lisible pour le texte). -->
        <XCard size="kid" class="w-full max-w-[400px] rotate-[1.5deg] shadow-card">
            <template #media>
                <XUniverseScene universe="space" />
            </template>
            <p class="text-[18px] font-bold text-muted">{{ $t('landing.hero.preview.greeting') }}</p>
            <p class="text-kid-body !font-black">{{ $t('landing.hero.preview.today') }}</p>
            <div class="flex flex-wrap items-center gap-2">
                <XSubjectTag subject="maths" :label="$t('landing.hero.preview.subject')" />
                <XTag tone="neutral" :icon="Clock">{{ $t('landing.hero.preview.duration') }}</XTag>
            </div>
            <p class="text-[22px] font-black">{{ $t('landing.hero.preview.mission') }}</p>
            <!-- Aperçu : pas de vrai bouton, rien à activer. -->
            <span
                class="flex min-h-kid-touch items-center justify-center gap-2 rounded-card bg-primary text-kid-button text-surface"
            >
                <Play :size="24" aria-hidden="true" />
                {{ $t('landing.hero.preview.start') }}
            </span>
        </XCard>
    </section>
</template>
