import type { MessageSchema } from '@/i18n';

// Clés de traduction vérifiées par vue-tsc (l'augmentation de module exige une interface).
declare module 'vue-i18n' {
    // eslint-disable-next-line @typescript-eslint/no-empty-object-type
    export interface DefineLocaleMessage extends MessageSchema {}
}
