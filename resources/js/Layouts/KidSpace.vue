<script setup lang="ts">
import { BookOpen, House, Star, TrendingUp, User } from '@lucide/vue';
import type { NavItem } from './navigation';
import KidLayout from './KidLayout.vue';

// KidLayout branché : navigation de l'espace enfant (entrées affichées si leur route existe).
const entries = [
    { route: 'kid.home', label: 'nav.kid.home', icon: House },
    { route: 'kid.activities', label: 'nav.kid.activities', icon: BookOpen },
    { route: 'kid.progress', label: 'nav.kid.progress', icon: TrendingUp },
    { route: 'kid.rewards', label: 'nav.kid.rewards', icon: Star },
    { route: 'kid.profile', label: 'nav.kid.profile', icon: User },
];

const nav: NavItem[] = entries
    .filter((entry) => route().has(entry.route))
    .map((entry) => ({
        label: entry.label,
        href: route(entry.route),
        icon: entry.icon,
        current: route().current(entry.route),
    }));
</script>

<template>
    <KidLayout :nav="nav" :home-href="route('kid.home')" :exit-href="route('kid.exit')">
        <slot />
    </KidLayout>
</template>
