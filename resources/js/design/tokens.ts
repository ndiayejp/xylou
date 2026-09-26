// Jetons de design Xylou (SPECIFICATIONS §10.1, planche « Fondations »).
// Source unique : lus par tailwind.config.ts et vérifiés par tokens.spec.ts.

export const colors = {
    primary: {
        DEFAULT: '#5B5CE2',
        text: '#4546C4',
        strong: '#34359E',
    },
    secondary: '#7C83FD',
    accent: {
        DEFAULT: '#FFB84D',
        text: '#8A5300',
        soft: '#FFF4E0',
    },
    success: {
        DEFAULT: '#55C98A',
        text: '#1C7A4C',
        soft: '#E6F7EE',
    },
    danger: {
        DEFAULT: '#C23B3B',
        line: '#F2C4C4',
    },
    disabled: {
        DEFAULT: '#E3E4EE',
        text: '#6E7089',
    },
    bg: '#F7F8FC',
    surface: '#FFFFFF',
    text: '#202238',
    muted: '#5A5C75',
    subtle: '#8A8CA3',
    line: '#E6E7F2',
    tint: '#EEEEFF',
    track: '#ECEDF5',
    well: '#EEEFF6',
    switch: '#CFD1E0',
} as const;

export const subjects = {
    maths: { text: '#4546C4', bg: '#EEEEFF' },
    french: { text: '#B23A26', bg: '#FDECE8' },
    sciences: { text: '#16775C', bg: '#E3F6EF' },
    history: { text: '#8A560B', bg: '#FBF0DC' },
    geography: { text: '#1F6AAF', bg: '#E4F0FB' },
    languages: { text: '#9C3470', bg: '#FAE8F2' },
} as const;

// Niveaux de maîtrise : pastille (texte / fond) et remplissage de barre.
export const mastery = {
    discover: { text: '#5A5C75', bg: '#F0F1F6', bar: '#B9BBCD' },
    consolidate: { text: '#8A5300', bg: '#FFF4E0', bar: '#FFB84D' },
    progressing: { text: '#34359E', bg: '#EEEEFF', bar: '#5B5CE2' },
    mastered: { text: '#1C7A4C', bg: '#E6F7EE', bar: '#55C98A' },
} as const;

type FontSize = [string, { lineHeight: string; fontWeight: string; letterSpacing?: string }];

export const fontSize: Record<string, FontSize> = {
    display: ['48px', { lineHeight: '1.2', fontWeight: '800', letterSpacing: '-0.02em' }],
    h1: ['32px', { lineHeight: '1.25', fontWeight: '800', letterSpacing: '-0.02em' }],
    h2: ['24px', { lineHeight: '1.25', fontWeight: '800', letterSpacing: '-0.02em' }],
    h3: ['18px', { lineHeight: '1.45', fontWeight: '700' }],
    body: ['15px', { lineHeight: '1.55', fontWeight: '500' }],
    caption: ['13px', { lineHeight: '1.45', fontWeight: '600' }],
    'kid-display': ['44px', { lineHeight: '1.2', fontWeight: '900', letterSpacing: '-0.02em' }],
    'kid-title': ['30px', { lineHeight: '1.2', fontWeight: '900', letterSpacing: '-0.02em' }],
    'kid-question': ['26px', { lineHeight: '1.25', fontWeight: '700' }],
    'kid-body': ['20px', { lineHeight: '1.45', fontWeight: '700' }],
    'kid-button': ['20px', { lineHeight: '1.2', fontWeight: '800' }],
};

export const fontFamily = {
    sans: 'Plus Jakarta Sans Variable',
    kid: 'Nunito Variable',
} as const;

export const borderRadius = {
    tag: '8px',
    input: '12px',
    button: '16px',
    card: '20px',
    'kid-card': '28px',
} as const;

export const motion = {
    duration: {
        hover: '160ms',
        slide: '280ms',
        validate: '500ms',
        fill: '900ms',
        breathe: '2400ms',
    },
    easing: {
        'out-expo': 'cubic-bezier(.2, .8, .2, 1)',
        spring: 'cubic-bezier(.2, 1.4, .4, 1)',
    },
} as const;

export const boxShadow = {
    rest: '0 1px 2px rgba(32, 34, 56, .04), 0 8px 24px rgba(32, 34, 56, .06)',
    lift: '0 6px 16px rgba(91, 92, 226, .18)',
    card: '0 14px 32px rgba(32, 34, 56, .10)',
} as const;

export const minTouchTarget = {
    adult: '44px',
    kid: '64px',
} as const;

// Fonds d'avatar : texte blanc lisible (AA). Plus foncés que la maquette (#FF8A65, #2FA58B), trop clairs.
export const avatar = {
    coral: '#C4491F',
    teal: '#1D7F6A',
    blue: '#2468A8',
    indigo: '#5B5CE2',
    violet: '#7A3FB0',
    pink: '#9C3470',
} as const;

export const reward = {
    indigo: { text: '#4546C4', bg: '#EEEEFF' },
    amber: { text: '#8A5300', bg: '#FFE9C4' },
    red: { text: '#B23A26', bg: '#FDECE8' },
    green: { text: '#1C7A4C', bg: '#E6F7EE' },
    blue: { text: '#1F6AAF', bg: '#E4F0FB' },
    pink: { text: '#9C3470', bg: '#FAE8F2' },
} as const;
