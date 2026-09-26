import {
    BookOpen,
    FileText,
    House,
    Library,
    Settings,
    Sparkles,
    Star,
    TrendingUp,
    User,
    Users,
} from '@lucide/vue';
import type { LayoutAction, NavItem } from '../navigation';

export const kidNav: NavItem[] = [
    { label: 'nav.kid.home', href: '/enfant', icon: House, current: true },
    { label: 'nav.kid.activities', href: '/enfant/activites', icon: BookOpen },
    { label: 'nav.kid.progress', href: '/enfant/progression', icon: TrendingUp },
    { label: 'nav.kid.rewards', href: '/enfant/reussites', icon: Star },
    { label: 'nav.kid.profile', href: '/enfant/profil', icon: User },
];

export const adultNav: NavItem[] = [
    { label: 'nav.adult.home', href: '/parent', icon: House, current: true },
    { label: 'nav.adult.children', href: '/parent/enfants', icon: Users },
    { label: 'nav.adult.activities', href: '/parent/activites', icon: BookOpen },
    { label: 'nav.adult.progress', href: '/parent/progression', icon: TrendingUp },
    { label: 'nav.adult.reports', href: '/parent/bilans', icon: FileText },
    { label: 'nav.adult.library', href: '/parent/bibliotheque', icon: Library },
    { label: 'nav.adult.settings', href: '/parametres', icon: Settings },
];

export const generateAction: LayoutAction = {
    label: 'nav.adult.generate',
    href: '/parent/activites/generer',
    icon: Sparkles,
};

export const children = [
    { id: 1, name: 'Emma', details: '8 ans · CE2', color: 'coral' as const },
    { id: 2, name: 'Lucas', details: '11 ans · 6e', color: 'teal' as const },
];

// jsdom n'applique pas le CSS : rail et barre mobile (même libellé, un seul visible à la fois)
// paraissent présents ensemble. Playwright et Storybook vérifient ces repères au vrai rendu.
export const RESPONSIVE_DUPLICATES = ['landmark-unique'];
