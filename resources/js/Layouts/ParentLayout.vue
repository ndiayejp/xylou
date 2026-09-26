<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CircleHelp, Ellipsis } from '@lucide/vue';
import { computed, ref, useId } from 'vue';
import type { XChildSwitcherItem } from '@/Components/ui/XChildSwitcher.vue';
import XLogo from '@/Components/ui/XLogo.vue';
import type { AccountLink, LayoutAction, LayoutUser, NavItem } from './navigation';
import AccountLinkList from './partials/AccountLinkList.vue';
import AccountMenu from './partials/AccountMenu.vue';
import ChildMenu from './partials/ChildMenu.vue';
import NotificationsLink from './partials/NotificationsLink.vue';
import SideNav from './partials/SideNav.vue';
import SkipLink from './partials/SkipLink.vue';
import NavigationProgress from './partials/NavigationProgress.vue';
import ToastRegion from './partials/ToastRegion.vue';

// Bureau : barre latérale. Tablette : rail. Mobile : en-tête + barre en bas (4 liens + « Plus »).
const props = withDefaults(
    defineProps<{
        nav: NavItem[];
        homeHref: string;
        user: LayoutUser;
        children: XChildSwitcherItem[];
        currentChildId?: string | number;
        notificationsHref?: string;
        unreadNotifications?: number;
        action?: LayoutAction;
        helpHref?: string;
        canAddChild?: boolean;
        accountLinks?: AccountLink[];
    }>(),
    {
        currentChildId: undefined,
        notificationsHref: undefined,
        unreadNotifications: 0,
        action: undefined,
        helpHref: undefined,
        canAddChild: false,
        accountLinks: () => [],
    },
);

defineEmits<{ switchChild: [id: string | number]; addChild: [] }>();

const MOBILE_LINKS = 4;
const mobileNav = computed(() => props.nav.slice(0, MOBILE_LINKS));
const moreNav = computed(() => props.nav.slice(MOBILE_LINKS));
const moreOpen = ref(false);
const moreId = useId();
</script>

<template>
    <div class="min-h-screen bg-bg text-text md:flex">
        <SkipLink />

        <!-- Bureau -->
        <nav
            :aria-label="$t('layout.mainNav')"
            class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col gap-[22px] overflow-y-auto border-r border-line bg-surface px-[18px] py-6 lg:flex"
        >
            <Link
                :href="homeHref"
                :aria-label="$t('layout.homeLink')"
                class="self-start rounded-input px-1.5"
            >
                <XLogo />
            </Link>
            <ChildMenu
                v-if="children.length"
                variant="sidebar"
                :items="children"
                :current-id="currentChildId"
                :can-add="canAddChild"
                @switch="$emit('switchChild', $event)"
                @add="$emit('addChild')"
            />
            <SideNav :items="nav" />
            <div class="grow" />
            <Link
                v-if="action"
                :href="action.href"
                class="flex h-12 items-center justify-center gap-2 rounded-[14px] bg-primary px-5 text-[15px] font-bold text-surface transition duration-hover hover:-translate-y-px hover:shadow-lift"
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

        <!-- Tablette -->
        <nav
            :aria-label="$t('layout.mainNav')"
            class="sticky top-0 hidden h-screen w-[88px] shrink-0 flex-col items-center gap-2.5 overflow-y-auto border-r border-line bg-surface px-2.5 py-5 md:flex lg:hidden"
        >
            <Link :href="homeHref" :aria-label="$t('layout.homeLink')" class="mb-2.5 rounded-input">
                <XLogo size="sm" compact />
            </Link>
            <ChildMenu
                v-if="children.length"
                variant="rail"
                class="mb-2.5"
                :items="children"
                :current-id="currentChildId"
                :can-add="canAddChild"
                @switch="$emit('switchChild', $event)"
                @add="$emit('addChild')"
            />
            <ul class="flex flex-col gap-1">
                <li v-for="item in nav" :key="item.href">
                    <Link
                        :href="item.href"
                        :aria-current="item.current ? 'page' : undefined"
                        class="flex w-[68px] flex-col items-center gap-1 rounded-input py-2 text-[12px] font-bold"
                        :class="
                            item.current
                                ? 'bg-tint text-primary-strong'
                                : 'text-muted hover:bg-bg hover:text-text'
                        "
                    >
                        <component :is="item.icon" :size="20" aria-hidden="true" />
                        {{ $t(item.label) }}
                    </Link>
                </li>
            </ul>
        </nav>

        <div class="flex min-w-0 grow flex-col">
            <!-- Mobile -->
            <header
                class="flex items-center justify-between border-b border-line bg-surface px-4 py-3.5 md:hidden"
            >
                <Link :href="homeHref" :aria-label="$t('layout.homeLink')" class="rounded-input">
                    <XLogo size="sm" />
                </Link>
                <div class="flex items-center gap-2">
                    <ChildMenu
                        v-if="children.length"
                        variant="pill"
                        :items="children"
                        :current-id="currentChildId"
                        :can-add="canAddChild"
                        align="end"
                        @switch="$emit('switchChild', $event)"
                        @add="$emit('addChild')"
                    />
                    <NotificationsLink
                        v-if="notificationsHref"
                        :href="notificationsHref"
                        :unread="unreadNotifications"
                    />
                </div>
            </header>

            <!-- Tablette et bureau -->
            <div
                class="hidden items-center justify-between gap-6 px-6 pt-7 md:flex lg:px-10 lg:pt-8"
            >
                <div class="min-w-0"><slot name="header" /></div>
                <div class="flex shrink-0 items-center gap-3">
                    <slot name="actions" />
                    <NotificationsLink
                        v-if="notificationsHref"
                        :href="notificationsHref"
                        :unread="unreadNotifications"
                    />
                    <AccountMenu :user="user" :links="accountLinks" />
                </div>
            </div>

            <main id="contenu" class="min-w-0 grow px-4 pb-28 pt-5 md:px-6 md:pb-10 lg:px-10">
                <div class="mb-[18px] md:hidden"><slot name="header" /></div>
                <slot />
            </main>
        </div>

        <nav
            :aria-label="$t('layout.mainNav')"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-surface px-1.5 pb-4 pt-2.5 md:hidden"
        >
            <div
                v-if="moreOpen"
                :id="moreId"
                class="absolute inset-x-3 bottom-full mb-2 rounded-card border border-line bg-surface p-2.5 shadow-card"
            >
                <SideNav :items="moreNav" />
                <Link
                    v-if="helpHref"
                    :href="helpHref"
                    class="mt-1 flex min-h-touch items-center gap-3 rounded-input px-3.5 text-[15px] font-semibold text-muted"
                >
                    <CircleHelp :size="20" aria-hidden="true" />
                    {{ $t('layout.help') }}
                </Link>
                <div v-if="accountLinks.length" class="mt-1 border-t border-line pt-1">
                    <AccountLinkList :links="accountLinks" @navigate="moreOpen = false" />
                </div>
            </div>
            <ul class="flex">
                <li v-for="item in mobileNav" :key="item.href" class="grow basis-0">
                    <Link
                        :href="item.href"
                        :aria-current="item.current ? 'page' : undefined"
                        class="flex min-h-touch flex-col items-center gap-1 text-[12px] font-bold"
                        :class="item.current ? 'text-primary-text' : 'text-muted'"
                    >
                        <component :is="item.icon" :size="22" aria-hidden="true" />
                        {{ $t(item.label) }}
                    </Link>
                </li>
                <li v-if="moreNav.length || helpHref || accountLinks.length" class="grow basis-0">
                    <button
                        type="button"
                        :aria-expanded="moreOpen"
                        :aria-controls="moreId"
                        class="flex min-h-touch w-full flex-col items-center gap-1 text-[12px] font-bold"
                        :class="
                            moreOpen || moreNav.some((item) => item.current)
                                ? 'text-primary-text'
                                : 'text-muted'
                        "
                        @click="moreOpen = !moreOpen"
                    >
                        <Ellipsis :size="22" aria-hidden="true" />
                        {{ $t('layout.more') }}
                    </button>
                </li>
            </ul>
        </nav>

        <ToastRegion />
        <NavigationProgress />
    </div>
</template>
