// Indication de solidité, affichée à titre d'aide : la règle qui fait foi est côté serveur (12 caractères).
export type PasswordStrength = 'weak' | 'fair' | 'good' | 'strong';

export const MIN_PASSWORD_LENGTH = 12;

export function passwordStrength(password: string): PasswordStrength {
    if (password.length < MIN_PASSWORD_LENGTH) {
        return 'weak';
    }

    const kinds = [/[a-z]/, /[A-Z]/, /\d/, /[^a-zA-Z\d]/].filter((pattern) =>
        pattern.test(password),
    ).length;
    const long = password.length >= 16;

    if (kinds >= 3 || (long && kinds >= 2)) {
        return 'strong';
    }

    return long || kinds >= 2 ? 'good' : 'fair';
}

export const strengthLevel: Record<PasswordStrength, number> = {
    weak: 1,
    fair: 2,
    good: 3,
    strong: 4,
};
