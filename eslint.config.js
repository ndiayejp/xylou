import pluginVue from 'eslint-plugin-vue';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';

export default defineConfigWithVueTs(
    {
        ignores: [
            'bootstrap/ssr',
            'node_modules',
            'playwright-report',
            'public',
            'storage',
            'storybook-static',
            'test-results',
            'vendor',
            'resources/js/types/generated.d.ts',
        ],
    },
    pluginVue.configs['flat/recommended'],
    vueTsConfigs.recommended,
    {
        // Composants Breeze, remplacés par Components/ui à l’étape 1
        rules: {
            'vue/multi-word-component-names': [
                'error',
                { ignores: ['Checkbox', 'Dropdown', 'Modal'] },
            ],
        },
    },
    {
        files: ['resources/js/Pages/**/*.vue'],
        rules: { 'vue/multi-word-component-names': 'off' },
    },
    skipFormatting,
);
