import { computed, shallowRef, type Component } from 'vue';

export interface Toast {
    id: number;
    message: string;
    icon?: Component;
    variant?: 'dark' | 'light';
    actionLabel?: string;
    onAction?: () => void;
}

export type ToastInput = Omit<Toast, 'id'> & { duration?: number };

// 8 s quand une action est proposée (ex. « Annuler » une suppression, §10.4), 5 s sinon.
const DEFAULT_DURATION = 5000;
const ACTION_DURATION = 8000;

// shallowRef : la liste est remplacée à chaque changement, les icônes ne deviennent pas réactives.
const toasts = shallowRef<Toast[]>([]);
let nextId = 1;

function dismiss(id: number): void {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function push({ duration, ...toast }: ToastInput): number {
    const id = nextId++;
    toasts.value = [...toasts.value, { ...toast, id }];
    setTimeout(
        () => dismiss(id),
        duration ?? (toast.actionLabel ? ACTION_DURATION : DEFAULT_DURATION),
    );

    return id;
}

function clear(): void {
    toasts.value = [];
}

export function useToasts() {
    return { toasts: computed(() => toasts.value), push, dismiss, clear };
}
