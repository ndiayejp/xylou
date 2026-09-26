<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CircleHelp, Menu, X } from '@lucide/vue';
import { ref, useId } from 'vue';
import XLogo from '@/Components/ui/XLogo.vue';
import type { AccountLink, LayoutAction, LayoutUser, NavItem } from './navigation';
import AccountLinkList from './partials/AccountLinkList.vue';
import AccountMenu from './partials/AccountMenu.vue';
import NotificationsLink from './partials/NotificationsLink.vue';
import SideNav from './partials/SideNav.vue';
import SkipLink from './partials/SkipLink.vue';
import NavigationProgress from './partials/NavigationProgress.vue';
import ToastRegion from './partials/ToastRegion.vue';

// Espace professionnel, sobre : barre latérale sur grand écran, menu déroulant en dessous.
withDefaults(
    defineProps<{
        nav: NavItem[];
        homeHref: string;
        user: LayoutUser;
        userDetails?: string;
        notificationsHref?: string;
        unreadNotifications?: number;
        action?: LayoutAction;
        helpHref?: string;
        accountLinks?: AccountLink[];
    }>(),
    {
        userDetails: undefined,
        notificationsHref: undefined,
        unreadNotifications: 0,
        action: undefined,
        helpHref: undefined,
        accountLinks: () => [],
    },
);

const menuOpen = ref(false);
const menuId = useId();
</script>

<template>
    <div class="min-h-screen bg-bg text-text lg:flex">
        <SkipLink />

        <nav
            :aria-label="$t('layout.mainNav')"
            class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col gap-[22px] overflow-y-auto border-r border-line bg-surface px-[18px] py-6 lg:flex"
        >
            <div class="flex items-center justify-between px-1.5">
                <Link :href="homeHref" :aria-label="$t('layout.homeLink')" class="rounded-input">
                    <XLogo />
                </Link>
                <span
                    class="rounded-[6px] border border-line px-2 py-[3px] text-[11px] font-extrabold uppercase tracking-[.06em] text-muted"
                >
                    {{ $t('layout.proBadge') }}
                </span>
            </div>
            <div class="rounded-input bg-well px-3.5 py-3 text-caption !font-medium text-muted">
                <p class="text-[14px] font-extrabold text-text">{{ user.name }}</p>
                <p v-if="userDetails">{{ userDetails }}</p>
            </div>
            <SideNav :items="nav" />
            <div class="grow" />
            <Link
                v-if="action"
                :href="action.href"
                class="flex h-12 items-center justify-center gap-2 rounded-[14px] bg-text px-5 text-[15px] font-bold text-surface transition duration-hover hover:-translate-y-px"
            >
                <component :is="action.icon" :size="19" aria-hidden="true" />
                {{ $t(action.label) }}
            </Link>
            <Link
                v-if="helpHref"
                :href="helpHref"
                class="flex items-center gap-2.5 rounded-input px-2 text-[14px] font-semibold text-muted hover:text-text"
            >
                <CircleHelp :size="18" aria-hidden="true" />
                {{ $t('layout.help') }}
            </Link>
        </nav>

        <header class="relative border-b border-line bg-surface px-4 py-3 lg:hidden">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Link
                        :href="homeHref"
                        :aria-label="$t('layout.homeLink')"
                        class="rounded-input"
                    >
                        <XLogo size="sm" />
                    </Link>
                    <span
                        class="rounded-[6px] border border-line px-2 py-[3px] text-[11px] font-extrabold uppercase tracking-[.06em] text-muted"
                    >
                        {{ $t('layout.proBadge') }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <NotificationsLink
                        v-if="notificationsHref"
                        :href="notificationsHref"
                        :unread="unreadNotifications"
                    />
                    <button
                        type="button"
                        :aria-expanded="menuOpen"
                        :aria-controls="menuId"
                        :aria-label="menuOpen ? $t('layout.closeMenu') : $t('layout.openMenu')"
                        class="inline-flex size-10 items-center justify-center rounded-input text-muted hover:bg-bg"
                        @click="menuOpen = !menuOpen"
                    >
                        <component :is="menuOpen ? X : Menu" :size="22" aria-hidden="true" />
                    </button>
                </div>
            </div>
            <nav
                v-if="menuOpen"
                :id="menuId"
                :aria-label="$t('layout.mainNav')"
                class="mt-3 flex flex-col gap-3 border-t border-line pt-3"
            >
                <SideNav :items="nav" />
                <Link
                    v-if="action"
                    :href="action.href"
                    class="flex h-12 items-center justify-center gap-2 rounded-[14px] bg-text px-5 text-[15px] font-bold text-surface"
                >
                    <component :is="action.icon" :size="19" aria-hidden="true" />
                    {{ $t(action.label) }}
                </Link>
                <div v-if="accountLinks.length" class="border-t border-line pt-2">
                    <AccountLinkList :links="accountLinks" @navigate="menuOpen = false" />
                </div>
            </nav>
        </header>

        <div class="flex min-w-0 grow flex-col">
            <div class="flex items-center justify-between gap-6 px-4 pt-6 md:px-6 lg:px-10 lg:pt-8">
                <div class="min-w-0"><slot name="header" /></div>
                <div class="hidden shrink-0 items-center gap-3 lg:flex">
                    <slot name="actions" />
                    <NotificationsLink
                        v-if="notificationsHref"
                        :href="notificationsHref"
                        :unread="unreadNotifications"
                    />
                    <AccountMenu :user="user" :links="accountLinks" />
                </div>
            </div>
            <main id="contenu" class="min-w-0 grow px-4 pb-10 pt-5 md:px-6 lg:px-10">
                <slot />
            </main>
        </div>

        <ToastRegion />
        <NavigationProgress />
    </div>
</template>
