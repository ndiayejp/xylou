import {
    BookOpen,
    FileText,
    House,
    Library,
    Settings,
    Sparkles,
    TrendingUp,
    Users,
} from '@lucide/vue';
import type { Component } from 'vue';
import type { AccountLink, LayoutAction, NavItem } from '@/Layouts/navigation';

interface NavEntry {
    route: string;
    label: string;
    icon: Component;
    match?: string;
}

// Liste complète de chaque espace (maquette). Une entrée n'apparaît que lorsque sa route existe :
// la navigation se complète d'elle-même au fil des étapes.
const parentEntries: NavEntry[] = [
    { route: 'parent.dashboard', label: 'nav.adult.home', icon: House },
    {
        route: 'parent.children.index',
        label: 'nav.adult.children',
        icon: Users,
        match: 'parent.children.*',
    },
    {
        route: 'parent.activities.index',
        label: 'nav.adult.activities',
        icon: BookOpen,
        match: 'parent.activities.*',
    },
    { route: 'parent.progress', label: 'nav.adult.progress', icon: TrendingUp },
    {
        route: 'parent.reports.index',
        label: 'nav.adult.reports',
        icon: FileText,
        match: 'parent.reports.*',
    },
    { route: 'parent.library', label: 'nav.adult.library', icon: Library },
    { route: 'settings.privacy', label: 'nav.adult.settings', icon: Settings, match: 'settings.*' },
];

const proEntries: NavEntry[] = [
    { route: 'pro.dashboard', label: 'nav.adult.home', icon: House },
    {
        route: 'pro.children.index',
        label: 'nav.adult.children',
        icon: Users,
        match: 'pro.children.*',
    },
    {
        route: 'pro.activities.index',
        label: 'nav.adult.activities',
        icon: BookOpen,
        match: 'pro.activities.*',
    },
    { route: 'pro.progress', label: 'nav.adult.progress', icon: TrendingUp },
    {
        route: 'pro.reports.index',
        label: 'nav.adult.reports',
        icon: FileText,
        match: 'pro.reports.*',
    },
    { route: 'pro.library', label: 'nav.adult.library', icon: Library },
    { route: 'settings.privacy', label: 'nav.adult.settings', icon: Settings, match: 'settings.*' },
];

function toNavItems(entries: NavEntry[]): NavItem[] {
    return entries
        .filter((entry) => route().has(entry.route))
        .map((entry) => ({
            label: entry.label,
            href: route(entry.route),
            icon: entry.icon,
            current: route().current(entry.match ?? entry.route),
        }));
}

function action(name: string, label: string, icon: Component): LayoutAction | undefined {
    return route().has(name) ? { label, href: route(name), icon } : undefined;
}

function accountLinks(): AccountLink[] {
    return [
        { label: 'layout.profile', href: route('profile.edit') },
        { label: 'layout.logout', href: route('logout'), method: 'post' },
    ];
}

export function useParentNavigation() {
    return {
        nav: toNavItems(parentEntries),
        homeHref: route('parent.dashboard'),
        action: action('parent.activities.generate', 'nav.adult.generate', Sparkles),
        accountLinks: accountLinks(),
    };
}

export function useProNavigation() {
    return {
        nav: toNavItems(proEntries),
        homeHref: route('pro.dashboard'),
        action: action('pro.reports.create', 'nav.adult.newReport', FileText),
        accountLinks: accountLinks(),
    };
}
