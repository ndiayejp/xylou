import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8000';

export default defineConfig({
    testDir: './tests/Browser',
    // `php artisan serve` traite une requête à la fois (pas de workers sous Windows) : en parallèle,
    // les tests s'attendent et les redirections dépassent les délais d'attente.
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL,
        trace: 'on-first-retry',
    },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: baseURL,
        reuseExistingServer: !process.env.CI,
    },
});
