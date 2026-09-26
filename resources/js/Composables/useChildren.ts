import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import type { XChildSwitcherItem } from '@/Components/ui/XChildSwitcher.vue';
import type { ChildSummary } from '@/types';

// Enfants de l'espace parent (props partagées) et changement d'enfant courant.
export function useChildren() {
    const page = usePage();
    const { t } = useI18n();

    const children = computed<ChildSummary[]>(() => page.props.parent?.children ?? []);
    const currentChildId = computed(() => page.props.parent?.currentChildId ?? undefined);
    const currentChild = computed(() =>
        children.value.find((child) => child.id === currentChildId.value),
    );

    // « 8 ans · CE2 » ; l'âge est approximatif, seule l'année de naissance est connue (§11.1).
    function details(child: ChildSummary): string {
        const grade = t(`children.grades.${child.grade}`);
        if (child.birthYear === null) {
            return grade;
        }
        const age = new Date().getFullYear() - child.birthYear;

        return `${t('children.age', age)} · ${grade}`;
    }

    const switcherItems = computed<XChildSwitcherItem[]>(() =>
        children.value.map((child) => ({
            id: child.id,
            name: child.firstName,
            details: details(child),
        })),
    );

    function switchTo(id: string | number): void {
        router.post(route('parent.current-child'), { child_id: id }, { preserveScroll: true });
    }

    return { children, currentChild, currentChildId, switcherItems, switchTo };
}
