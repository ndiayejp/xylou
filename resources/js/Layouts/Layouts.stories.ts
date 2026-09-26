import { FileText, Trash2 } from '@lucide/vue';
import type { Meta, StoryObj } from '@storybook/vue3-vite';
import { ref } from 'vue';
import XButton from '@/Components/ui/XButton.vue';
import XCard from '@/Components/ui/XCard.vue';
import XMascot from '@/Components/ui/XMascot.vue';
import { useToasts } from '@/Composables/useToasts';
import { adultNav, children, generateAction, kidNav } from './__tests__/fixtures';
import KidLayout from './KidLayout.vue';
import ParentLayout from './ParentLayout.vue';
import ProLayout from './ProLayout.vue';
import PublicLayout from './PublicLayout.vue';

// Les liens ne mènent nulle part tant que les routes des espaces n'existent pas (étape 2).
const meta = {
    title: 'Layouts',
    parameters: { layout: 'fullscreen' },
} satisfies Meta;

export default meta;
type Story = StoryObj<typeof meta>;

const mobile = { viewport: { value: 'mobile2', isRotated: false } };
const tablet = { viewport: { value: 'tablet', isRotated: false } };

function kidStory(currentIndex = 0): Story {
    const nav = kidNav.map((item, index) => ({ ...item, current: index === currentIndex }));

    return {
        render: () => ({
            components: { KidLayout, XCard, XMascot },
            setup: () => ({ kidNav: nav }),
            template: `
            <KidLayout :nav="kidNav" home-href="#">
                <div class="flex flex-col gap-6 p-6 md:p-10">
                    <div class="flex items-center gap-4">
                        <XMascot mood="happy" :size="72" />
                        <h1 class="text-kid-title">Bonjour Emma !</h1>
                    </div>
                    <XCard size="kid" class="p-6 text-kid-body">Ta mission du jour t’attend.</XCard>
                </div>
            </KidLayout>`,
        }),
    };
}

export const Enfant: Story = { ...kidStory(), globals: tablet };
export const EnfantMobile: Story = { ...kidStory(), name: 'Enfant (mobile)', globals: mobile };
export const EnfantMobileProgression: Story = {
    ...kidStory(2),
    name: 'Enfant (mobile, libellé long)',
    globals: mobile,
};

const parentStory: Story = {
    render: () => ({
        components: { ParentLayout, XButton, XCard },
        setup: () => {
            const current = ref<string | number>(1);
            const { push } = useToasts();
            const toast = () =>
                push({ message: 'Activité supprimée', icon: Trash2, actionLabel: 'Annuler' });

            return { adultNav, children, generateAction, current, toast };
        },
        template: `
            <ParentLayout
                :nav="adultNav"
                home-href="#"
                :user="{ name: 'Sophie' }"
                :children="children"
                :current-child-id="current"
                notifications-href="#"
                :unread-notifications="3"
                :action="generateAction"
                help-href="#"
                can-add-child
                @switch-child="current = $event"
            >
                <template #header>
                    <h1 class="text-h2 md:text-h1">Bonjour Sophie</h1>
                    <p class="mt-1 text-[14px] text-muted">La semaine d’Emma</p>
                </template>
                <XCard padding="lg" class="flex flex-col items-start gap-3">
                    <p>Le contenu de la page s’affiche ici.</p>
                    <XButton @click="toast">Afficher un toast</XButton>
                </XCard>
            </ParentLayout>`,
    }),
};

export const Parent: Story = parentStory;
export const ParentTablette: Story = { ...parentStory, name: 'Parent (tablette)', globals: tablet };
export const ParentMobile: Story = { ...parentStory, name: 'Parent (mobile)', globals: mobile };

export const Pro: Story = {
    render: () => ({
        components: { ProLayout, XCard },
        setup: () => ({
            adultNav,
            newReport: { label: 'nav.adult.newReport', href: '#', icon: FileText },
        }),
        template: `
            <ProLayout
                :nav="adultNav"
                home-href="#"
                :user="{ name: 'Claire Martin' }"
                user-details="Orthopédagogue · 6 profils partagés"
                notifications-href="#"
                :unread-notifications="3"
                :action="newReport"
                help-href="#"
            >
                <template #header>
                    <h1 class="text-h2 md:text-h1">Bonjour Claire</h1>
                </template>
                <XCard padding="lg">Le contenu de la page s’affiche ici.</XCard>
            </ProLayout>`,
    }),
};

export const Public: Story = {
    render: () => ({
        components: { PublicLayout },
        setup: () => ({
            sections: [
                { label: 'nav.public.how', href: '#' },
                { label: 'nav.public.parents', href: '#' },
                { label: 'nav.public.pros', href: '#' },
                { label: 'nav.public.privacy', href: '#' },
                { label: 'nav.public.faq', href: '#' },
            ],
            footer: [
                {
                    title: 'nav.public.product',
                    items: [
                        { label: 'nav.public.how', href: '#' },
                        { label: 'nav.public.forParents', href: '#' },
                        { label: 'nav.public.forPros', href: '#' },
                        { label: 'nav.public.pricing', href: '#' },
                    ],
                },
                {
                    title: 'nav.public.trust',
                    items: [
                        { label: 'nav.public.privacy', href: '#' },
                        { label: 'nav.public.ai', href: '#' },
                        { label: 'nav.public.minors', href: '#' },
                        { label: 'nav.public.accessibility', href: '#' },
                    ],
                },
                {
                    title: 'nav.public.help',
                    items: [
                        { label: 'nav.public.faq', href: '#' },
                        { label: 'nav.public.contact', href: '#' },
                        { label: 'nav.public.helpCenter', href: '#' },
                    ],
                },
            ],
        }),
        template: `
            <PublicLayout home-href="#" :sections="sections" login-href="#" register-href="#" :footer="footer" legal-href="#">
                <section class="px-4 py-16 md:px-10 lg:px-20">
                    <h1 class="max-w-[640px] text-display">L’école à la maison, dans l’univers de votre enfant.</h1>
                </section>
            </PublicLayout>`,
    }),
};
