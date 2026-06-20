export type UserRole = "learner" | "administrator";

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    tokens?: number;
}

export interface AuthProps {
    user: AuthUser | null;
}

export interface PageProps {
    auth: AuthProps;
    flash: {
        status?: string;
    };
    game: {
        skip_lockout_cost: number;
    };
}

export interface DevAccount {
    email: string;
    password: string;
    role: UserRole;
}

export function roleLabel(role: UserRole): string {
    return role === "administrator" ? "Administrador" : "Aprendiz";
}
