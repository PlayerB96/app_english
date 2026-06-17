import type { TierSlug } from "@/types/levels";
import { computed, ref } from "vue";

const TIER_ORDER: TierSlug[] = ["basico", "intermedio", "avanzado"];
const PHASES_PER_TIER = 5;
const TOTAL_LEVELS = TIER_ORDER.length * PHASES_PER_TIER;

export function levelId(tier: TierSlug, phase: number): number {
    const tierIndex = TIER_ORDER.indexOf(tier);

    return tierIndex * PHASES_PER_TIER + phase;
}

export function tierPhaseFromId(id: number): { tier: TierSlug; phase: number } {
    const tierIndex = Math.floor((id - 1) / PHASES_PER_TIER);
    const phase = ((id - 1) % PHASES_PER_TIER) + 1;

    return { tier: TIER_ORDER[tierIndex], phase };
}

function nextLevelId(currentId: number): number | null {
    if (currentId >= TOTAL_LEVELS) {
        return null;
    }

    return currentId + 1;
}

export function useLevelProgress(storageKey: string) {
    const unlocked = ref<number[]>([1]);
    const completed = ref<number[]>([]);
    const lockouts = ref<Record<string, string>>({});

    function load(): void {
        try {
            const raw = localStorage.getItem(storageKey);

            if (!raw) {
                return;
            }

            const data = JSON.parse(raw) as {
                unlocked?: number[];
                completed?: number[];
                lockouts?: Record<string, string>;
            };

            if (Array.isArray(data.unlocked) && data.unlocked.length > 0) {
                unlocked.value = data.unlocked;
            }

            if (Array.isArray(data.completed)) {
                completed.value = data.completed;
            }

            if (data.lockouts) {
                lockouts.value = data.lockouts;
            }
        } catch {
            // ignore corrupt storage
        }
    }

    function save(): void {
        localStorage.setItem(
            storageKey,
            JSON.stringify({
                unlocked: unlocked.value,
                completed: completed.value,
                lockouts: lockouts.value,
            }),
        );
    }

    function isUnlocked(id: number): boolean {
        return unlocked.value.includes(id);
    }

    function isCompleted(id: number): boolean {
        return completed.value.includes(id);
    }

    function isLockedOut(id: number): boolean {
        const until = lockouts.value[String(id)];

        if (!until) {
            return false;
        }

        return new Date(until).getTime() > Date.now();
    }

    function lockoutRemaining(id: number): string | null {
        const until = lockouts.value[String(id)];

        if (!until || !isLockedOut(id)) {
            return null;
        }

        return until;
    }

    function markPassed(id: number): void {
        if (!completed.value.includes(id)) {
            completed.value.push(id);
        }

        const next = nextLevelId(id);

        if (next && !unlocked.value.includes(next)) {
            unlocked.value.push(next);
        }

        delete lockouts.value[String(id)];
        save();
    }

    function markFailedWithLockout(id: number, hours = 24): string {
        const until = new Date(Date.now() + hours * 60 * 60 * 1000);
        lockouts.value[String(id)] = until.toISOString();
        save();

        return until.toISOString();
    }

    function resetProgress(): void {
        unlocked.value = [1];
        completed.value = [];
        lockouts.value = {};
        save();
    }

    const progressPercent = computed(() =>
        Math.round((completed.value.length / TOTAL_LEVELS) * 100),
    );

    const completedCount = computed(() => completed.value.length);

    load();

    return {
        unlocked,
        completed,
        completedCount,
        lockouts,
        isUnlocked,
        isCompleted,
        isLockedOut,
        lockoutRemaining,
        markPassed,
        markFailedWithLockout,
        resetProgress,
        progressPercent,
        totalLevels: TOTAL_LEVELS,
    };
}

export function normalizeText(value: string): string {
    return value
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^\w\s]/g, " ")
        .replace(/\s+/g, " ")
        .trim();
}

export function compareTranslation(
    spoken: string,
    expected: string,
): boolean {
    const a = normalizeText(spoken);
    const b = normalizeText(expected);

    if (a === b) {
        return true;
    }

    const aWords = a.split(" ").filter((w) => w.length > 2);
    const bWords = b.split(" ").filter((w) => w.length > 2);
    const matches = aWords.filter((w) => bWords.includes(w)).length;

    return matches >= Math.max(1, Math.ceil(bWords.length * 0.6));
}
