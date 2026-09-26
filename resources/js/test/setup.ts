import { config } from '@vue/test-utils';
import { i18n } from '@/i18n';

config.global.plugins = [i18n];

// <Head> d'Inertia attend le gestionnaire d'en-tête d'une application Inertia.
config.global.mocks = {
    $headManager: {
        createProvider: () => ({
            preferredAttribute: () => 'inertia',
            update: () => {},
            disconnect: () => {},
            reconnect: () => {},
        }),
    },
};
