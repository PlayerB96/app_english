export type TierSlug = "basico" | "intermedio" | "avanzado";

export interface TierInfo {
    slug: TierSlug;
    name: string;
    description: string;
}

export interface SpeakingChallenge {
    id: number;
    tier: TierSlug;
    phase: number;
    prompt: string;
    expected_translation: string;
    hint: string | null;
}

export interface QuizChallenge {
    id: number;
    tier: TierSlug;
    phase: number;
    prompt: string;
    options: [string, string, string];
    correct_index: number;
}

export interface SpeakingFeedback {
    is_correct: boolean;
    transcription: string;
    translation: string;
    expected_translation: string;
    score: number;
    message: string;
}

export interface QuizFeedback {
    is_correct: boolean;
    correct_answer: string;
    message: string;
    locked_until: string | null;
}
