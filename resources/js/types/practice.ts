export type Difficulty = "beginner" | "intermediate" | "advanced";

export type SessionStatus = "active" | "completed" | "abandoned";

export type QuestionSource = "ai" | "seed" | "manual";

export interface LearningTrack {
    id: number;
    slug: string;
    name: string;
    description: string | null;
    difficulty: Difficulty;
    is_active: boolean;
    sort_order: number;
    session_count: number;
}

export interface PracticeSession {
    id: number;
    user_id: number;
    learning_track_id: number;
    status: SessionStatus;
    started_at: string;
    completed_at: string | null;
    question_count: number;
    correct_count: number;
}

export interface Question {
    id: number;
    practice_session_id: number | null;
    learning_track_id: number;
    prompt: string;
    context: string | null;
    difficulty: Difficulty;
    source: QuestionSource;
}

export interface MockAnswerFeedback {
    is_correct: boolean;
    score: number;
    feedback: string;
    sample_answer: string;
}

export interface MockQuestion extends Question {
    sample_answer: string;
}
