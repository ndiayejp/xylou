// Éditeur d'activité (ActivityController::editor). Une question arrive « à plat » ;
// seuls les champs de son type comptent (ActivityRequest).
export type AnswerTypeKey = 'number' | 'text' | 'single_choice' | 'multiple_choice';

export interface QuestionForm {
    uid: number;
    answer_type: AnswerTypeKey;
    prompt: string;
    hint: string;
    explanation: string;
    value: string;
    unit: string;
    tolerance: string;
    accepted: string[];
    choices: string[];
    correct: number | null;
    correct_many: number[];
}

export interface EditedActivity {
    id: string;
    status: string;
    title: string;
    subject: string;
    grade: string | null;
    skill_id: number;
    universe: string | null;
    format: string;
    difficulty: string;
    duration_minutes: number;
    learning_objective: string | null;
    items: (Omit<QuestionForm, 'uid' | 'value' | 'tolerance' | 'hint' | 'explanation'> & {
        value: number | null;
        tolerance: number | null;
        hint: string | null;
        explanation: string | null;
        unit: string | null;
    })[];
}

export interface EditorOptions {
    subjects: string[];
    skills: { id: number; label: string; subject: string; grades: string[] }[];
    grades: string[];
    universes: string[];
    formats: string[];
    difficulties: string[];
    durations: number[];
    answerTypes: AnswerTypeKey[];
    maxItems: number;
    maxChoices: number;
}
