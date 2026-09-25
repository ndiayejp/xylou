import type { StorybookConfig } from '@storybook/vue3-vite';
import type { PluginOption } from 'vite';

function withoutLaravelPlugin(plugins: PluginOption[] = []): PluginOption[] {
    return plugins
        .flat()
        .filter(
            (plugin) =>
                !(
                    plugin &&
                    typeof plugin === 'object' &&
                    'name' in plugin &&
                    plugin.name.startsWith('laravel')
                ),
        );
}

export default {
    stories: ['../resources/js/Components/ui/**/*.stories.ts'],
    addons: ['@storybook/addon-docs', '@storybook/addon-a11y'],
    framework: '@storybook/vue3-vite',
    core: { disableTelemetry: true },
    viteFinal: (config) => ({ ...config, plugins: withoutLaravelPlugin(config.plugins) }),
} satisfies StorybookConfig;
