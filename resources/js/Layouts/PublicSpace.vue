<script setup lang="ts">
import type { NavGroup, NavItem } from './navigation';
import PublicLayout from './PublicLayout.vue';

// PublicLayout branché : sections de la landing, connexion, inscription, pied de page.
defineProps<{ contactEmail: string }>();

const home = route('home');
const section = (id: string) => `${home}#${id}`;

const sections: Omit<NavItem, 'icon'>[] = [
    { label: 'nav.public.how', href: section('comment-ca-marche') },
    { label: 'nav.public.parents', href: section('pour-qui') },
    { label: 'nav.public.pros', href: section('pour-qui') },
    { label: 'nav.public.privacy', href: section('confiance') },
    { label: 'nav.public.faq', href: section('faq') },
];

function footer(contactEmail: string): NavGroup[] {
    return [
        {
            title: 'nav.public.product',
            items: [
                { label: 'nav.public.how', href: section('comment-ca-marche') },
                { label: 'nav.public.forParents', href: section('pour-qui') },
                { label: 'nav.public.forPros', href: section('pour-qui') },
            ],
        },
        {
            title: 'nav.public.trust',
            items: [
                { label: 'nav.public.privacy', href: route('legal.privacy') },
                { label: 'nav.public.ai', href: `${route('legal.privacy')}#ia` },
                { label: 'nav.public.minors', href: `${route('legal.privacy')}#mineurs` },
                { label: 'nav.public.terms', href: route('legal.terms') },
                { label: 'nav.public.accessibility', href: route('legal.accessibility') },
            ],
        },
        {
            title: 'nav.public.help',
            items: [
                { label: 'nav.public.faq', href: section('faq') },
                { label: 'nav.public.contact', href: `mailto:${contactEmail}` },
            ],
        },
    ];
}
</script>

<template>
    <PublicLayout
        :home-href="home"
        :sections="sections"
        :login-href="route('login')"
        :register-href="route('register')"
        :footer="footer(contactEmail)"
        :legal-href="route('legal.notice')"
    >
        <slot />
    </PublicLayout>
</template>
