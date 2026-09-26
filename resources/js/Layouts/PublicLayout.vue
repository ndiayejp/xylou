<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { ref, useId } from 'vue';
import XLogo from '@/Components/ui/XLogo.vue';
import type { NavGroup, NavItem } from './navigation';
import SkipLink from './partials/SkipLink.vue';
import NavigationProgress from './partials/NavigationProgress.vue';
import ToastRegion from './partials/ToastRegion.vue';

withDefaults(
    defineProps<{
        homeHref: string;
        sections?: Omit<NavItem, 'icon'>[];
        loginHref?: string;
        registerHref?: string;
        footer?: NavGroup[];
        legalHref?: string;
    }>(),
    {
        sections: () => [],
        loginHref: undefined,
        registerHref: undefined,
        footer: () => [],
        legalHref: undefined,
    },
);

const year = new Date().getFullYear();
const menuOpen = ref(false);
const menuId = useId();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-bg text-text">
        <SkipLink />

        <header class="relative px-4 py-4 md:px-10 lg:px-20 lg:py-[22px]">
            <div class="flex items-center justify-between gap-6">
                <Link :href="homeHref" :aria-label="$t('layout.homeLink')" class="rounded-input">
                    <XLogo size="lg" />
                </Link>
                <nav
                    v-if="sections.length"
                    :aria-label="$t('layout.sections')"
                    class="hidden lg:block"
                >
                    <ul class="flex gap-8 text-[15px] font-semibold">
                        <li v-for="item in sections" :key="item.label">
                            <a :href="item.href" class="rounded-tag hover:text-primary-text">
                                {{ $t(item.label) }}
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="hidden items-center gap-2.5 md:flex">
                    <Link
                        v-if="loginHref"
                        :href="loginHref"
                        class="inline-flex h-12 items-center rounded-[14px] px-5 text-[15px] font-bold text-primary-strong hover:bg-tint"
                    >
                        {{ $t('layout.login') }}
                    </Link>
                    <Link
                        v-if="registerHref"
                        :href="registerHref"
                        class="inline-flex h-12 items-center rounded-[14px] bg-primary px-5 text-[15px] font-bold text-surface transition duration-hover hover:-translate-y-px hover:shadow-lift"
                    >
                        {{ $t('layout.register') }}
                    </Link>
                </div>
                <button
                    type="button"
                    :aria-expanded="menuOpen"
                    :aria-controls="menuId"
                    :aria-label="menuOpen ? $t('layout.closeMenu') : $t('layout.openMenu')"
                    class="inline-flex size-11 items-center justify-center rounded-input text-muted hover:bg-surface lg:hidden"
                    @click="menuOpen = !menuOpen"
                >
                    <component :is="menuOpen ? X : Menu" :size="24" aria-hidden="true" />
                </button>
            </div>
            <div
                v-if="menuOpen"
                :id="menuId"
                class="mt-3 flex flex-col gap-2 rounded-card border border-line bg-surface p-4 shadow-card lg:hidden"
            >
                <nav v-if="sections.length" :aria-label="$t('layout.sections')">
                    <ul class="flex flex-col">
                        <li v-for="item in sections" :key="item.label">
                            <a
                                :href="item.href"
                                class="flex min-h-touch items-center rounded-input px-3 text-[15px] font-semibold hover:bg-bg"
                                @click="menuOpen = false"
                            >
                                {{ $t(item.label) }}
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="flex flex-col gap-2 border-t border-line pt-3 md:hidden">
                    <Link
                        v-if="registerHref"
                        :href="registerHref"
                        class="inline-flex h-12 items-center justify-center rounded-[14px] bg-primary px-5 text-[15px] font-bold text-surface"
                    >
                        {{ $t('layout.register') }}
                    </Link>
                    <Link
                        v-if="loginHref"
                        :href="loginHref"
                        class="inline-flex h-12 items-center justify-center rounded-[14px] px-5 text-[15px] font-bold text-primary-strong"
                    >
                        {{ $t('layout.login') }}
                    </Link>
                </div>
            </div>
        </header>

        <main id="contenu" class="grow">
            <slot />
        </main>

        <footer
            class="flex flex-col justify-between gap-10 border-t border-line bg-surface px-4 py-14 md:px-10 lg:flex-row lg:px-20"
        >
            <div class="flex max-w-[320px] flex-col gap-3.5">
                <XLogo />
                <p class="text-[14px] leading-[1.55] text-muted">{{ $t('layout.tagline') }}</p>
                <p class="text-caption !font-medium text-muted">
                    {{ $t('layout.copyright', { year }) }}
                    <template v-if="legalHref">
                        ·
                        <Link :href="legalHref" class="underline-offset-2 hover:underline">
                            {{ $t('nav.public.legal') }}
                        </Link>
                    </template>
                </p>
            </div>
            <div v-if="footer.length" class="grid grid-cols-2 gap-10 sm:flex sm:gap-16">
                <nav v-for="group in footer" :key="group.title" :aria-label="$t(group.title)">
                    <p class="mb-2.5 text-[14px] font-extrabold">{{ $t(group.title) }}</p>
                    <ul class="flex flex-col gap-2.5">
                        <li v-for="item in group.items" :key="item.label">
                            <a :href="item.href" class="text-[14px] text-muted hover:text-text">
                                {{ $t(item.label) }}
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </footer>

        <ToastRegion />
        <NavigationProgress />
    </div>
</template>
