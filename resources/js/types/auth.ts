export type UserRole = "cashier" | "administrator";

export interface AuthUser {
    code: string;
    name: string;
    companyCode: string;
    branchCode: string;
    exchangeRate: number;
    roleCode: string;
    roleName: string;
    branchName: string;
    branchSigla: string;
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
