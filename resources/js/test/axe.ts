import axe from 'axe-core';
import { expect } from 'vitest';

// jsdom ne calcule pas les couleurs : le contraste est vérifié par tokens.spec.ts, Storybook et Playwright.
export async function expectNoAxeViolations(
    element: Element,
    ignore: string[] = [],
): Promise<void> {
    const rules = Object.fromEntries(
        ['color-contrast', ...ignore].map((rule) => [rule, { enabled: false }]),
    );
    const { violations } = await axe.run(element, { rules });

    expect(violations.map(({ id, help }) => `${id} : ${help}`)).toEqual([]);
}
