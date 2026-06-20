export type TierSlug = "basico" | "intermedio" | "avanzado";

export interface TierInfo {
    slug: TierSlug;
    name: string;
    description: string;
}

export interface SpeakingQuestion {
    question_id: number;
    level_id: number;
    question_index: number;
    step_difficulty: StepDifficulty;
    sublevel_intensity: number;
    prompt: string;
    expected_translation: string;
    hint: string | null;
}

export interface QuizQuestion {
    question_id: number;
    level_id: number;
    question_index: number;
    step_difficulty: StepDifficulty;
    sublevel_intensity: number;
    prompt: string;
    options: [string, string, string];
    correct_index: number;
}

export type StepDifficulty = "facil" | "medio" | "dificil";

export interface SpeakingLevel {
    id: number;
    tier: TierSlug;
    phase: number;
    sublevel_intensity: number;
    questions: SpeakingQuestion[];
}

export interface QuizLevel {
    id: number;
    tier: TierSlug;
    phase: number;
    sublevel_intensity: number;
    questions: QuizQuestion[];
}

export interface SpeakingFeedback {
    is_correct: boolean;
    transcription: string;
    expected_prompt: string;
    expected_translation: string;
    score: number;
    message: string;
    level_completed?: boolean;
    questions_correct?: number;
    questions_total?: number;
}

export interface QuizFeedback {
    is_correct: boolean;
    correct_answer: string;
    message: string;
    locked_until: string | null;
    level_completed?: boolean;
    questions_correct?: number;
    questions_total?: number;
}

export interface LevelQuestionProgress {
    correct: number;
    total: number;
}

export interface LevelProgressState {
    unlocked: number[];
    completed: number[];
    lockouts: Record<string, string>;
    question_progress: Record<string, LevelQuestionProgress>;
    answered_questions: Record<string, number[]>;
    session_questions: Record<string, number[]>;
}

export type LevelProgressMode = "speaking" | "quiz";
