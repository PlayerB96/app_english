export type UserRole = "learner" | "administrator";

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: UserRole;
}

export interface AuthProps {
    user: AuthUser | null;
}

export interface PageProps {
    auth: AuthProps;
    flash: {
        status?: string;
    };
}

export interface DevAccount {
    email: string;
    password: string;
    role: UserRole;
}
