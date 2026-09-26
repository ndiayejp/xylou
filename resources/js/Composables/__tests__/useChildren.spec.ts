import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { useChildren } from '../useChildren';

const page = {
    props: {
        parent: {
            children: [
                { id: 1, firstName: 'Emma', grade: 'ce2', birthYear: 2018 },
                { id: 2, firstName: 'Lucas', grade: '6e', birthYear: null },
            ],
            currentChildId: 2,
        },
    },
};
const post = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => page,
    router: { post: (...args: unknown[]) => post(...args) },
}));

vi.mock('vue-i18n', async () => {
    const { i18n } = await import('@/i18n');
    return { useI18n: () => i18n.global };
});

describe('useChildren', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.setSystemTime(new Date('2026-09-26'));
        vi.stubGlobal('route', (name: string) => `/${name}`);
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.unstubAllGlobals();
    });

    it('prépare les éléments du sélecteur : âge approximatif et niveau traduit', () => {
        const { switcherItems } = useChildren();

        expect(switcherItems.value).toEqual([
            { id: 1, name: 'Emma', details: '8 ans · CE2' },
            { id: 2, name: 'Lucas', details: '6e' },
        ]);
    });

    it('expose l’enfant courant', () => {
        expect(useChildren().currentChild.value?.firstName).toBe('Lucas');
    });

    it('change d’enfant par la route dédiée', () => {
        useChildren().switchTo(1);

        expect(post).toHaveBeenCalledWith(
            '/parent.current-child',
            { child_id: 1 },
            { preserveScroll: true },
        );
    });
});
