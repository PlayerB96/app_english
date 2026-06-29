export type UserRole = "learner" | "administrator";

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    tokens?: number;
    world_unlocked?: boolean;
}

export interface AuthProps {
    user: AuthUser | null;
}

export interface PageProps {
    auth: AuthProps;
    flash: {
        status?: string;
    };
    game: PagePropsGame;
    errors?: Record<string, string>;
    [key: string]: unknown;
}

export interface PagePropsGame {
    skip_lockout_cost: number;
    max_tier_resets: number;
    tier_reset_cost: number;
    sublevel_complete_reward: number;
    world_unlock_cost: number;
    world_skip_lockout_cost: number;
    world_lockout_hours: number;
    power_shop: import("@/types/powerShop").PowerShopConfig;
}

export interface DevAccount {
    email: string;
    password: string;
    role: UserRole;
}

export function roleLabel(role: UserRole): string {
    return role === "administrator" ? "Administrador" : "Aprendiz";
}
