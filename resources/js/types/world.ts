export type WorldChallengeType =
    | "quest"
    | "command_lab"
    | "puzzle"
    | "boss_interview";

export type WorldTierSlug = "basico" | "intermedio" | "avanzado";

export type WorldStatus = "available" | "coming_soon";

export interface WorldZone {
    slug: string;
    emoji: string;
    name: string;
    level_range: string;
    curriculum?: string[];
    commands?: string[];
    english?: string[];
    gameplay?: string;
}

export interface WorldBoss {
    emoji: string;
    title: string;
    description: string;
}

export interface WorldInfo {
    tier: WorldTierSlug;
    emoji: string;
    name: string;
    subtitle: string;
    description: string;
    status: WorldStatus;
    zones: WorldZone[];
    boss?: WorldBoss;
}

export interface WorldLevel {
    id: number;
    tier: WorldTierSlug;
    zone: string;
    phase: number;
    title: string;
    type: WorldChallengeType;
    scenario: string;
    objective: string;
    gameplay: string;
    duration_minutes: number;
    is_boss?: boolean;
}

export interface WorldAccessState {
    unlocked: boolean;
    unlock_cost: number;
    unlocked_at: string | null;
}

export interface WorldProgressState {
    unlocked: number[];
    completed: number[];
    lockouts?: Record<string, string>;
    question_progress?: Record<string, { correct: number; total: number }>;
    answered_questions?: Record<string, number[]>;
    session_questions?: Record<string, number[]>;
}

export type WorldQuestionType =
    | "translation"
    | "sentence_completion"
    | "term_meaning"
    | "command_context"
    | "scenario";

export type WorldQuestionDifficulty = "facil" | "medio" | "dificil";

export interface WorldQuestion {
    question_id: number;
    world_level_id: number;
    question_index: number;
    type: WorldQuestionType;
    difficulty: WorldQuestionDifficulty;
    prompt: string;
    context: string | null;
    options: [string, string, string];
    correct_index: number;
}

export interface WorldQuizFeedback {
    is_correct: boolean;
    correct_answer: string;
    message: string;
    locked_until: string | null;
    level_completed?: boolean;
    questions_correct?: number;
    questions_total?: number;
}

export const WORLD_TOTAL_LEVELS = 18;
export const WORLD_QUESTIONS_PER_LEVEL = 3;
