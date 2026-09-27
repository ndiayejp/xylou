import type { VisitOptions } from '@inertiajs/core';
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

// Échec d'une visite Inertia (réponse qui n'est pas une page, ex. erreur 500, ou panne réseau) :
// au lieu de la fenêtre d'erreur d'Inertia, la page affiche son propre état « erreur » (§10.4).
// Seules les visites passées par track() sont concernées.
export function useFailedVisit() {
    const failed = ref(false);
    let tracking = 0;
    let onFail: (() => void) | undefined;

    const intercept = (event: Event): void => {
        if (tracking > 0) {
            event.preventDefault();
            if (onFail) {
                onFail();
            } else {
                failed.value = true;
            }
        }
    };
    const stops = [router.on('invalid', intercept), router.on('exception', intercept)];
    onBeforeUnmount(() => stops.forEach((stop) => stop()));

    // Options d'une visite à surveiller. Sans « fail », l'échec passe « failed » à vrai (état erreur de
    // la page) ; avec, la visite réagit elle-même (ex. un toast après une action).
    function track(options: VisitOptions = {}, fail?: () => void): VisitOptions {
        return {
            ...options,
            onStart: (visit) => {
                tracking++;
                failed.value = false;
                onFail = fail;
                options.onStart?.(visit);
            },
            onFinish: (visit) => {
                tracking = Math.max(0, tracking - 1);
                options.onFinish?.(visit);
            },
        };
    }

    return { failed, track };
}
