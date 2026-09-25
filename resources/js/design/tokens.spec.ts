import { describe, expect, it } from 'vitest';
import { colors, fontSize, mastery, subjects } from './tokens';

function luminance(hex: string): number {
    const [r, g, b] = [1, 3, 5].map((i) => {
        const c = parseInt(hex.slice(i, i + 2), 16) / 255;
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function contrast(a: string, b: string): number {
    const [light, dark] = [luminance(a), luminance(b)].sort((x, y) => y - x);
    return (light + 0.05) / (dark + 0.05);
}

const AA = 4.5;

describe('jetons de couleur', () => {
    it.each([
        ['text', colors.text],
        ['muted', colors.muted],
        ['primary.text', colors.primary.text],
        ['primary.strong', colors.primary.strong],
        ['success.text', colors.success.text],
        ['accent.text', colors.accent.text],
        ['danger', colors.danger],
    ])('%s est lisible (AA) sur le fond et sur blanc', (_, color) => {
        expect(contrast(color, colors.bg)).toBeGreaterThanOrEqual(AA);
        expect(contrast(color, colors.surface)).toBeGreaterThanOrEqual(AA);
    });

    it('le texte blanc est lisible sur les boutons pleins', () => {
        expect(contrast(colors.surface, colors.primary.DEFAULT)).toBeGreaterThanOrEqual(AA);
        expect(contrast(colors.surface, colors.primary.text)).toBeGreaterThanOrEqual(AA);
        expect(contrast(colors.surface, colors.danger)).toBeGreaterThanOrEqual(AA);
    });

    it("l'accent n'est jamais un texte sur blanc, mais le texte principal l'est sur l'accent", () => {
        expect(contrast(colors.accent.DEFAULT, colors.surface)).toBeLessThan(AA);
        expect(contrast(colors.text, colors.accent.DEFAULT)).toBeGreaterThanOrEqual(AA);
    });

    it('les fonds doux (ambre, indigo) gardent un texte lisible', () => {
        expect(contrast(colors.accent.text, colors.accent.soft)).toBeGreaterThanOrEqual(AA);
        expect(contrast(colors.primary.strong, colors.tint)).toBeGreaterThanOrEqual(AA);
    });

    it.each(Object.entries(subjects))(
        'la matière %s est lisible sur son fond',
        (_, { text, bg }) => {
            expect(contrast(text, bg)).toBeGreaterThanOrEqual(AA);
        },
    );

    it.each(Object.entries(mastery))(
        'le niveau %s est lisible sur sa pastille',
        (_, { text, bg }) => {
            expect(contrast(text, bg)).toBeGreaterThanOrEqual(AA);
        },
    );
});

describe('typographie', () => {
    it("aucune taille enfant n'est sous 18 px", () => {
        const kid = Object.entries(fontSize).filter(([name]) => name.startsWith('kid-'));

        expect(kid.length).toBeGreaterThan(0);
        kid.forEach(([, [size]]) => expect(parseFloat(size)).toBeGreaterThanOrEqual(18));
    });
});
