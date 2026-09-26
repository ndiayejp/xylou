import { describe, expect, it } from 'vitest';
import { avatar, colors, fontSize, mastery, reward, subjects } from './tokens';

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
        ['danger', colors.danger.DEFAULT],
    ])('%s est lisible (AA) sur le fond et sur blanc', (_, color) => {
        expect(contrast(color, colors.bg)).toBeGreaterThanOrEqual(AA);
        expect(contrast(color, colors.surface)).toBeGreaterThanOrEqual(AA);
    });

    it('le texte blanc est lisible sur les boutons pleins', () => {
        expect(contrast(colors.surface, colors.primary.DEFAULT)).toBeGreaterThanOrEqual(AA);
        expect(contrast(colors.surface, colors.primary.text)).toBeGreaterThanOrEqual(AA);
        expect(contrast(colors.surface, colors.danger.DEFAULT)).toBeGreaterThanOrEqual(AA);
    });

    it("l'accent n'est jamais un texte sur blanc, mais le texte principal l'est sur l'accent", () => {
        expect(contrast(colors.accent.DEFAULT, colors.surface)).toBeLessThan(AA);
        expect(contrast(colors.text, colors.accent.DEFAULT)).toBeGreaterThanOrEqual(AA);
    });

    it.each([
        ['ambre', colors.accent.text, colors.accent.soft],
        ['indigo', colors.primary.strong, colors.tint],
        ['vert', colors.success.text, colors.success.soft],
        ['rouge', colors.danger.DEFAULT, colors.danger.soft],
        ['bleu', colors.info.text, colors.info.soft],
    ])('le fond doux %s garde un texte lisible', (_, text, bg) => {
        expect(contrast(text, bg)).toBeGreaterThanOrEqual(AA);
    });

    it('le toast sombre garde un texte et une action lisibles', () => {
        expect(contrast(colors.surface, colors.text)).toBeGreaterThanOrEqual(AA);
        expect(contrast(colors.accent.DEFAULT, colors.text)).toBeGreaterThanOrEqual(AA);
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

    it.each(Object.entries(avatar))("l'initiale blanche est lisible sur l'avatar %s", (_, bg) => {
        expect(contrast(colors.surface, bg)).toBeGreaterThanOrEqual(AA);
    });

    it.each(Object.entries(reward))(
        'la récompense %s est lisible sur son fond',
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
