import axe from 'axe-core';
import { expect } from 'vitest';

// jsdom ne calcule pas les couleurs : le contraste est vérifié par tokens.spec.ts, Storybook et Playwright.
export async function expectNoAxeViolations(element: Element): Promise<void> {
    const { violations } = await axe.run(element, {
        rules: { 'color-contrast': { enabled: false } },
    });

    expect(violations.map(({ id, help }) => `${id} : ${help}`)).toEqual([]);
}
