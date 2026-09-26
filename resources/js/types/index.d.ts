export type Role = 'parent' | 'professional' | 'admin';

export type Grade = 'cp' | 'ce1' | 'ce2' | 'cm1' | 'cm2' | '6e' | '5e' | '4e' | '3e';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    roles: Role[];
}

export interface ChildSummary {
    id: number;
    firstName: string;
    grade: Grade;
    birthYear: number | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    // Session enfant uniquement (null ailleurs) : seulement le prénom.
    kid: { id: number; firstName: string } | null;
    // Espace parent uniquement (null ailleurs).
    parent: {
        children: ChildSummary[];
        currentChildId: number | null;
    } | null;
};
