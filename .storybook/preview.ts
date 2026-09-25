import type { Preview } from '@storybook/vue3-vite';
import '../resources/css/app.css';

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
