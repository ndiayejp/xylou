import type { ActiveVisit } from '@inertiajs/core';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent } from 'vue';
import { useFailedVisit } from '../useFailedVisit';

// Faux routeur Inertia : on garde les écouteurs pour déclencher « invalid » et « exception » à la main.
const listeners = vi.hoisted(() => new Map<string, (event: Event) => void>());
vi.mock('@inertiajs/vue3', () => ({
    router: {
        on: (type: string, callback: (event: Event) => void) => {
            listeners.set(type, callback);

            return () => listeners.delete(type);
        },
    },
}));

function fire(type: 'invalid' | 'exception'): Event {
    const event = new Event(type, { cancelable: true });
    listeners.get(type)?.(event);

    return event;
}

function setup() {
    let api!: ReturnType<typeof useFailedVisit>;
    const wrapper = mount(
        defineComponent({
            setup() {
                api = useFailedVisit();

                return () => null;
            },
        }),
    );

    return { api, wrapper };
}

const visit = {} as ActiveVisit;

describe('useFailedVisit', () => {
    beforeEach(() => listeners.clear());

    it('ignore les visites qu’il ne suit pas', () => {
        const { api } = setup();

        expect(fire('invalid').defaultPrevented).toBe(false);
        expect(api.failed.value).toBe(false);
    });

    it('remplace la fenêtre d’Inertia par l’état erreur pendant une visite suivie', () => {
        const { api } = setup();
        const onStart = vi.fn();
        const options = api.track({ onStart });

        options.onStart?.(visit);
        const event = fire('exception');

        expect(event.defaultPrevented).toBe(true);
        expect(api.failed.value).toBe(true);
        expect(onStart).toHaveBeenCalled();
    });

    it('laisse la visite réagir elle-même si elle le demande', () => {
        const { api } = setup();
        const fail = vi.fn();

        api.track({}, fail).onStart?.(visit);
        fire('invalid');

        expect(fail).toHaveBeenCalledOnce();
        expect(api.failed.value).toBe(false);
    });

    it('repart de zéro à la visite suivante, et s’arrête à la fin de la visite', () => {
        const { api } = setup();
        const options = api.track();

        options.onStart?.(visit);
        fire('invalid');
        options.onFinish?.(visit);
        expect(fire('invalid').defaultPrevented).toBe(false);

        options.onStart?.(visit);
        expect(api.failed.value).toBe(false);
    });

    it('se désabonne quand la page disparaît', () => {
        const { wrapper } = setup();

        wrapper.unmount();

        expect(listeners.size).toBe(0);
    });
});
