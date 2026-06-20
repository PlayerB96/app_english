const STORAGE_KEY = "rememberLogin";
const TTL_MS = 30 * 24 * 60 * 60 * 1000;

interface RememberLoginPayload {
    email: string;
    password: string;
    expiresAt: number;
}

export function loadRememberLogin(): RememberLoginPayload | null {
    if (typeof window === "undefined") {
        return null;
    }

    const raw = localStorage.getItem(STORAGE_KEY);

    if (!raw) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw) as RememberLoginPayload;

        if (
            typeof parsed.email !== "string"
            || typeof parsed.password !== "string"
            || typeof parsed.expiresAt !== "number"
            || parsed.expiresAt <= Date.now()
        ) {
            localStorage.removeItem(STORAGE_KEY);

            return null;
        }

        return parsed;
    } catch {
        localStorage.removeItem(STORAGE_KEY);

        return null;
    }
}

export function saveRememberLogin(email: string, password: string): void {
    if (typeof window === "undefined") {
        return;
    }

    const payload: RememberLoginPayload = {
        email,
        password,
        expiresAt: Date.now() + TTL_MS,
    };

    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
}

export function clearRememberLogin(): void {
    if (typeof window === "undefined") {
        return;
    }

    localStorage.removeItem(STORAGE_KEY);
}
