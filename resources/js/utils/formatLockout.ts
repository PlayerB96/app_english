export interface LockoutRemainingParts {
    hours: number;
    minutes: number;
    seconds: number;
}

export function lockoutRemainingParts(
    untilIso: string | null,
): LockoutRemainingParts | null {
    if (!untilIso) {
        return null;
    }

    const remainingMs = new Date(untilIso).getTime() - Date.now();

    if (remainingMs <= 0) {
        return null;
    }

    const hours = Math.floor(remainingMs / (1000 * 60 * 60));
    const minutes = Math.floor((remainingMs % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remainingMs % (1000 * 60)) / 1000);

    return { hours, minutes, seconds };
}

export function formatLockoutRemainingDetailed(
    untilIso: string | null,
): string | null {
    const parts = lockoutRemainingParts(untilIso);

    if (!parts) {
        return null;
    }

    const { hours, minutes, seconds } = parts;

    if (hours > 0) {
        return `${hours}h ${minutes}m ${seconds}s`;
    }

    if (minutes > 0) {
        return `${minutes}m ${seconds}s`;
    }

    return `${seconds}s`;
}

function pluralize(value: number, singular: string, plural: string): string {
    return `${value} ${value === 1 ? singular : plural}`;
}

function padTime(value: number): string {
    return String(value).padStart(2, "0");
}

export function formatLockoutGameTimer(untilIso: string | null): string | null {
    const parts = lockoutRemainingParts(untilIso);

    if (!parts) {
        return null;
    }

    const { hours, minutes, seconds } = parts;

    if (hours > 0) {
        return `${padTime(hours)}:${padTime(minutes)}:${padTime(seconds)}`;
    }

    return `${padTime(minutes)}:${padTime(seconds)}`;
}

export function formatLockoutRemainingFriendly(
    untilIso: string | null,
): string | null {
    const parts = lockoutRemainingParts(untilIso);

    if (!parts) {
        return null;
    }

    const segments: string[] = [];

    if (parts.hours > 0) {
        segments.push(pluralize(parts.hours, "hora", "horas"));
    }

    if (parts.minutes > 0) {
        segments.push(pluralize(parts.minutes, "minuto", "minutos"));
    }

    if (parts.seconds > 0 || segments.length === 0) {
        segments.push(pluralize(parts.seconds, "segundo", "segundos"));
    }

    if (segments.length === 1) {
        return segments[0];
    }

    if (segments.length === 2) {
        return `${segments[0]} y ${segments[1]}`;
    }

    return `${segments[0]}, ${segments[1]} y ${segments[2]}`;
}

export function formatLockoutRemaining(untilIso: string | null): string | null {
    return formatLockoutGameTimer(untilIso);
}

export function formatLockoutUnlockAt(untilIso: string): string {
    return new Date(untilIso).toLocaleString("es-ES", {
        dateStyle: "medium",
        timeStyle: "short",
    });
}
