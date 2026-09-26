import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { useToasts } from '../useToasts';

describe('useToasts', () => {
    const { toasts, push, dismiss, clear } = useToasts();

    beforeEach(() => {
        vi.useFakeTimers();
        clear();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('empile les toasts et les retire après 5 s', () => {
        push({ message: 'Activité envoyée à Emma' });
        push({ message: 'Rapport disponible' });

        expect(toasts.value.map((toast) => toast.message)).toEqual([
            'Activité envoyée à Emma',
            'Rapport disponible',
        ]);

        vi.advanceTimersByTime(5000);

        expect(toasts.value).toEqual([]);
    });

    it('laisse 8 s quand une action est proposée', () => {
        push({ message: 'Activité supprimée', actionLabel: 'Annuler' });

        vi.advanceTimersByTime(5000);
        expect(toasts.value).toHaveLength(1);

        vi.advanceTimersByTime(3000);
        expect(toasts.value).toEqual([]);
    });

    it('respecte une durée personnalisée et la fermeture manuelle', () => {
        const kept = push({ message: 'Hors ligne', duration: 60_000 });
        const closed = push({ message: 'Enregistré' });

        dismiss(closed);

        expect(toasts.value.map((toast) => toast.id)).toEqual([kept]);
    });
});
