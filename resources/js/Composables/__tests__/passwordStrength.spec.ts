import { describe, expect, it } from 'vitest';
import { passwordStrength } from '../passwordStrength';

describe('passwordStrength', () => {
    it.each([
        ['', 'weak'],
        ['court123', 'weak'],
        ['abcdefghijkl', 'fair'],
        ['abcdefghij12', 'good'],
        ['unephrasetrespluslongue', 'good'],
        ['une phrase facile à retenir', 'strong'],
        ['Abcdefgh12!x', 'strong'],
    ] as const)('« %s » : %s', (password, expected) => {
        expect(passwordStrength(password)).toBe(expected);
    });
});
