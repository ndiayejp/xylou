import { useI18n } from 'vue-i18n';

// Listes de messages (tableaux dans i18n) : chaque entrée est rendue par rt().
export function useMessageList() {
    const { tm, rt } = useI18n();

    function list(key: string): string[] {
        const raw: unknown = tm(key);
        return Array.isArray(raw) ? raw.map((message) => rt(message)) : [];
    }

    function records<T extends string>(key: string, fields: readonly T[]): Record<T, string>[] {
        const raw: unknown = tm(key);
        if (!Array.isArray(raw)) {
            return [];
        }

        return raw.map((item) => {
            const entries = fields.map((field) => [
                field,
                rt((item as Record<string, never>)[field]),
            ]);
            return Object.fromEntries(entries) as Record<T, string>;
        });
    }

    return { list, records };
}
