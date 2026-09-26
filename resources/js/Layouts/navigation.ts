import type { Component } from 'vue';
import type { XAvatarColor } from '@/Components/ui/XAvatar.vue';

// Construits par les pages (étape 2) à partir des routes nommées ; label est une clé vue-i18n.
export interface NavItem {
    label: string;
    href: string;
    icon: Component;
    current?: boolean;
}

export interface NavGroup {
    title: string;
    items: Omit<NavItem, 'icon'>[];
}

export interface LayoutUser {
    name: string;
    color?: XAvatarColor;
}

export interface AccountLink {
    label: string;
    href: string;
    method?: 'get' | 'post';
}

export interface LayoutAction {
    label: string;
    href: string;
    icon: Component;
}
