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
}

export const WORLD_TOTAL_LEVELS = 18;
