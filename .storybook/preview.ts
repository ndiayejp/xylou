import { setup, type Preview } from '@storybook/vue3-vite';
import '../resources/css/app.css';
import { i18n } from '../resources/js/i18n';

setup((app) => {
    app.use(i18n);
});

export default {
    parameters: {
        a11y: { test: 'error' },
        backgrounds: {
            options: {
                app: { name: 'Fond Xylou', value: '#F7F8FC' },
                surface: { name: 'Blanc', value: '#FFFFFF' },
            },
        },
    },
    initialGlobals: { backgrounds: { value: 'app' } },
    tags: ['autodocs'],
} satisfies Preview;
