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
        // Composants Breeze encore utilisés par les pages d’authentification et de profil (étape 2)
        rules: {
            'vue/multi-word-component-names': ['error', { ignores: ['Checkbox', 'Modal'] }],
        },
    },
    {
        files: ['resources/js/Pages/**/*.vue'],
        rules: { 'vue/multi-word-component-names': 'off' },
    },
    skipFormatting,
);
