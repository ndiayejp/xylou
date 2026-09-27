// Props de la bibliothèque (LibraryController).
export type LibraryStatus =
    'generating' | 'generation_failed' | 'pending_review' | 'draft' | 'approved' | 'archived';

export interface LibraryActivity {
    id: string;
    title: string;
    subject: 'maths' | 'french';
    skill: string;
    gradeMin: string | null;
    gradeMax: string | null;
    universe: 'space' | 'football' | 'forest' | null;
    durationMinutes: number;
    status: LibraryStatus;
    source: 'ai' | 'manual' | 'duplicate' | 'pro_reco';
    purgeAt: string | null;
}

export type LibraryFilterKey =
    'subject' | 'skill' | 'grade' | 'duration' | 'difficulty' | 'universe' | 'status';

// « q » : texte cherché (Meilisearch), hors pastilles.
export type LibraryFilters = Record<LibraryFilterKey | 'q', string>;

// Ce que la recherche en langage naturel a compris (une seule fois, après l'envoi).
export type LibraryUnderstood = Partial<
    Record<Exclude<LibraryFilterKey, 'skill' | 'status'>, string>
> & {
    child?: string;
    text: string;
};

export interface LibraryOptions {
    subjects: LibraryActivity['subject'][];
    skills: { id: number; label: string }[];
    grades: string[];
    durations: number[];
    difficulties: string[];
    universes: NonNullable<LibraryActivity['universe']>[];
    statuses: LibraryStatus[];
}

export type LibraryAction = 'edit' | 'duplicate' | 'archive' | 'unarchive' | 'delete' | 'restore';
