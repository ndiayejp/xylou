import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { useParentNavigation, useProNavigation } from '../useSpaceNavigation';

// Faux Ziggy : seules les routes listées existent, `current` compare au motif (avec *).
function fakeRoutes(existing: string[], currentRoute: string) {
    const matches = (pattern: string) =>
        new RegExp(`^${pattern.replace('.', '\\.').replace('*', '.*')}$`).test(currentRoute);
    const router = {
        has: (name: string) => existing.includes(name),
        current: (pattern: string) => matches(pattern),
    };

    return vi.fn((name?: string) =>
        name === undefined ? router : `/${name.replaceAll('.', '/')}`,
    );
}

describe('useSpaceNavigation', () => {
    beforeEach(() => {
        vi.stubGlobal(
            'route',
            fakeRoutes(
                [
                    'parent.dashboard',
                    'pro.dashboard',
                    'profile.edit',
                    'settings.security',
                    'logout',
                    'parent.children.index',
                ],
                'parent.children.show',
            ),
        );
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('ne garde que les entrées dont la route existe, dans l’ordre de la maquette', () => {
        const { nav, homeHref } = useParentNavigation();

        expect(nav.map((item) => item.label)).toEqual(['nav.adult.home', 'nav.adult.children']);
        expect(homeHref).toBe('/parent/dashboard');
    });

    it('marque l’entrée courante, sous-pages comprises', () => {
        const { nav } = useParentNavigation();

        expect(nav.map((item) => item.current)).toEqual([false, true]);
    });

    it('n’affiche l’action principale que si sa route existe', () => {
        expect(useParentNavigation().action).toBeUndefined();
        expect(useProNavigation().action).toBeUndefined();
    });

    it('propose le profil et la déconnexion (en POST)', () => {
        expect(useProNavigation().accountLinks).toEqual([
            { label: 'layout.profile', href: '/profile/edit' },
            { label: 'layout.security', href: '/settings/security' },
            { label: 'layout.logout', href: '/logout', method: 'post' },
        ]);
    });
});
