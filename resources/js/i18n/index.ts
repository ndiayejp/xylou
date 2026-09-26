import { createI18n } from 'vue-i18n';
import fr from './fr';

export type MessageSchema = typeof fr;

export const i18n = createI18n<[MessageSchema], 'fr'>({
    legacy: false,
    locale: 'fr',
    fallbackLocale: 'fr',
    messages: { fr },
});
