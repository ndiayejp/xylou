<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import XLogo from '@/Components/ui/XLogo.vue';
import type { NavItem } from './navigation';
import SkipLink from './partials/SkipLink.vue';
import ToastRegion from './partials/ToastRegion.vue';

// Tablette : rail à gauche. Mobile : barre en bas.
// Jamais de texte enfant sous 18 px : sur mobile, seul l'onglet courant affiche son libellé,
// sous son icône, les autres le gardent pour les lecteurs d'écran (5 libellés à 18 px ne tiennent
// pas en 390 px).
defineProps<{ nav: NavItem[]; homeHref: string }>();
</script>

<template>
    <div class="min-h-screen bg-bg font-kid text-text md:flex">
        <SkipLink />

        <nav
            :aria-label="$t('layout.mainNav')"
            class="sticky top-0 hidden h-screen w-[136px] shrink-0 flex-col items-center gap-[22px] border-r border-line bg-surface px-2 py-6 md:flex"
        >
            <Link :href="homeHref" :aria-label="$t('layout.homeLink')" class="mb-2.5 rounded-input">
                <XLogo size="sm" />
            </Link>
            <ul class="flex flex-col gap-[22px]">
                <li v-for="item in nav" :key="item.href">
                    <Link
                        :href="item.href"
                        :aria-current="item.current ? 'page' : undefined"
                        class="flex flex-col items-center gap-1.5 text-center text-[18px] font-extrabold leading-tight"
                        :class="item.current ? 'text-primary-text' : 'text-muted hover:text-text'"
                    >
                        <span
                            class="flex h-12 w-16 items-center justify-center rounded-button transition-colors duration-hover"
                            :class="item.current && 'bg-tint'"
                        >
                            <component :is="item.icon" :size="26" aria-hidden="true" />
                        </span>
                        {{ $t(item.label) }}
                    </Link>
                </li>
            </ul>
        </nav>

        <main id="contenu" class="min-w-0 grow pb-24 md:pb-0">
            <slot />
        </main>

        <nav
            :aria-label="$t('layout.mainNav')"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface px-2 pb-3.5 pt-2.5 md:hidden"
        >
            <ul class="flex items-center justify-between gap-1">
                <li v-for="item in nav" :key="item.href">
                    <Link
                        :href="item.href"
                        :aria-current="item.current ? 'page' : undefined"
                        class="flex min-h-kid-touch flex-col items-center justify-center gap-0.5 rounded-button text-center text-[18px] font-extrabold leading-tight"
                        :class="
                            item.current
                                ? 'max-w-[128px] bg-tint px-3 py-1.5 text-primary-text'
                                : 'w-14 text-muted hover:text-text'
                        "
                    >
                        <component :is="item.icon" :size="26" class="shrink-0" aria-hidden="true" />
                        <span :class="!item.current && 'sr-only'">{{ $t(item.label) }}</span>
                    </Link>
                </li>
            </ul>
        </nav>

        <ToastRegion />
    </div>
</template>
