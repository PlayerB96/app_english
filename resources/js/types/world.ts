export type WorldChallengeType =
    | "roleplay"
    | "writing"
    | "dialogue"
    | "feedback"
    | "presentation";

export type WorldTierSlug = "basico" | "intermedio" | "avanzado";

export interface WorldInfo {
    tier: WorldTierSlug;
    name: string;
    description: string;
}

export interface WorldLevel {
    id: number;
    tier: WorldTierSlug;
    phase: number;
    title: string;
    type: WorldChallengeType;
    scenario: string;
    objective: string;
    duration_minutes: number;
}

export interface WorldAccessState {
    unlocked: boolean;
    unlock_cost: number;
    unlocked_at: string | null;
}

export interface WorldProgressState {
    unlocked: number[];
    completed: number[];
}
