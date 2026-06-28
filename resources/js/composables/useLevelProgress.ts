import type {
    LevelProgressMode,
    LevelProgressState,
    LevelQuestionProgress,
    TierResetInfo,
    TierSlug,
} from "@/types/levels";
import { router } from "@inertiajs/vue3";
import { computed, ref, toValue, watch, type MaybeRef } from "vue";

const TIER_ORDER: TierSlug[] = ["basico", "intermedio", "avanzado"];
const PHASES_PER_TIER = 5;
const TOTAL_LEVELS = TIER_ORDER.length * PHASES_PER_TIER;

const DEFAULT_TIER_RESETS: Record<TierSlug, TierResetInfo> = {
    basico: { count: 0, max: 2, cost: 30 },
    intermedio: { count: 0, max: 2, cost: 30 },
    avanzado: { count: 0, max: 2, cost: 30 },
};

function normalizeTierResets(
    tierResets: LevelProgressState["tier_resets"] | undefined,
): Record<TierSlug, TierResetInfo> {
    if (!tierResets) {
        return { ...DEFAULT_TIER_RESETS };
    }

    return {
        basico: tierResets.basico ?? DEFAULT_TIER_RESETS.basico,
        intermedio: tierResets.intermedio ?? DEFAULT_TIER_RESETS.intermedio,
        avanzado: tierResets.avanzado ?? DEFAULT_TIER_RESETS.avanzado,
    };
}

export function levelId(tier: TierSlug, phase: number): number {
    const tierIndex = TIER_ORDER.indexOf(tier);

    return tierIndex * PHASES_PER_TIER + phase;
}

export function tierPhaseFromId(id: number): { tier: TierSlug; phase: number } {
    const tierIndex = Math.floor((id - 1) / PHASES_PER_TIER);
    const phase = ((id - 1) % PHASES_PER_TIER) + 1;

    return { tier: TIER_ORDER[tierIndex], phase };
}

export function levelIdsForTier(tier: TierSlug): number[] {
    const tierIndex = TIER_ORDER.indexOf(tier);

    return Array.from({ length: PHASES_PER_TIER }, (_, index) => (
        tierIndex * PHASES_PER_TIER + index + 1
    ));
}

function cloneState(state: LevelProgressState): LevelProgressState {
    return {
        unlocked: [...state.unlocked],
        completed: [...state.completed],
        lockouts: { ...state.lockouts },
        question_progress: { ...state.question_progress },
        answered_questions: { ...state.answered_questions ?? {} },
        session_questions: { ...state.session_questions ?? {} },
        tier_resets: normalizeTierResets(state.tier_resets),
    };
}

function syncState(
    unlocked: { value: number[] },
    completed: { value: number[] },
    lockouts: { value: Record<string, string> },
    questionProgress: { value: Record<string, LevelQuestionProgress> },
    answeredQuestions: { value: Record<string, number[]> },
    sessionQuestions: { value: Record<string, number[]> },
    tierResets: { value: Record<TierSlug, TierResetInfo> },
    source: LevelProgressState,
): void {
    unlocked.value = [...source.unlocked];
    completed.value = [...source.completed];
    lockouts.value = { ...source.lockouts };
    questionProgress.value = { ...source.question_progress };
    answeredQuestions.value = { ...source.answered_questions };
    sessionQuestions.value = { ...source.session_questions ?? {} };
    tierResets.value = normalizeTierResets(source.tier_resets);
}

export function useLevelProgress(
    mode: LevelProgressMode,
    initial: MaybeRef<LevelProgressState>,
) {
    const source = toValue(initial);
    const unlocked = ref<number[]>([...source.unlocked]);
    const completed = ref<number[]>([...source.completed]);
    const lockouts = ref<Record<string, string>>({ ...source.lockouts });
    const questionProgress = ref<Record<string, LevelQuestionProgress>>({
        ...(source.question_progress ?? {}),
    });
    const answeredQuestions = ref<Record<string, number[]>>({
        ...(source.answered_questions ?? {}),
    });
    const sessionQuestions = ref<Record<string, number[]>>({
        ...(source.session_questions ?? {}),
    });
    const tierResets = ref<Record<TierSlug, TierResetInfo>>(
        normalizeTierResets(source.tier_resets),
    );
    const syncing = ref(false);

    watch(
        () => toValue(initial),
        (value) => syncState(
            unlocked,
            completed,
            lockouts,
            questionProgress,
            answeredQuestions,
            sessionQuestions,
            tierResets,
            {
                ...value,
                question_progress: value.question_progress ?? {},
                answered_questions: value.answered_questions ?? {},
                session_questions: value.session_questions ?? {},
                tier_resets: value.tier_resets,
            },
        ),
        { deep: true },
    );

    function currentSnapshot(): LevelProgressState {
        return {
            unlocked: [...unlocked.value],
            completed: [...completed.value],
            lockouts: { ...lockouts.value },
            question_progress: { ...questionProgress.value },
            answered_questions: { ...answeredQuestions.value },
            session_questions: { ...sessionQuestions.value },
            tier_resets: normalizeTierResets(tierResets.value),
        };
    }

    function isUnlocked(id: number): boolean {
        return unlocked.value.includes(id);
    }

    function isCompleted(id: number): boolean {
        return completed.value.includes(id);
    }

    function isPending(id: number): boolean {
        if (isCompleted(id)) {
            return false;
        }

        const progress = questionProgress.value[String(id)];

        return progress !== undefined && progress.correct > 0;
    }

    function questionProgressFor(id: number): LevelQuestionProgress | null {
        return questionProgress.value[String(id)] ?? null;
    }

    function answeredQuestionsFor(id: number): number[] {
        return answeredQuestions.value[String(id)] ?? [];
    }

    function sessionQuestionsFor(id: number): number[] {
        return sessionQuestions.value[String(id)] ?? [];
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

    function reloadProgress(): Promise<void> {
        return new Promise((resolve) => {
            router.reload({
                only: ["progress", "auth"],
                onFinish: () => resolve(),
            });
        });
    }

    function startSession(levelId: number): Promise<void> {
        syncing.value = true;

        return new Promise((resolve, reject) => {
            router.post(
                `/level-progress/${mode}/start-session`,
                { level_id: levelId },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: (page) => {
                        const progress = page.props.progress as LevelProgressState | undefined;

                        if (progress) {
                            syncState(
                                unlocked,
                                completed,
                                lockouts,
                                questionProgress,
                                answeredQuestions,
                                sessionQuestions,
                                tierResets,
                                {
                                    ...progress,
                                    question_progress: progress.question_progress ?? {},
                                    answered_questions: progress.answered_questions ?? {},
                                    session_questions: progress.session_questions ?? {},
                                    tier_resets: progress.tier_resets,
                                },
                            );
                        }

                        resolve();
                    },
                    onError: () => reject(new Error("start_session_failed")),
                    onFinish: () => {
                        syncing.value = false;
                    },
                },
            );
        });
    }

    function markQuestionPassed(
        levelId: number,
        questionId: number,
        options?: {
            response_text?: string;
            input_mode?: string;
        },
    ): Promise<{ completed: boolean; correct: number; total: number }> {
        const previous = cloneState(currentSnapshot());
        syncing.value = true;

        const key = String(levelId);
        const current = questionProgress.value[key] ?? {
            correct: 0,
            total: 3,
        };
        const nextCorrect = Math.min(current.correct + 1, current.total);

        questionProgress.value[key] = {
            correct: nextCorrect,
            total: current.total,
        };

        if (!answeredQuestions.value[key]?.includes(questionId)) {
            answeredQuestions.value[key] = [
                ...(answeredQuestions.value[key] ?? []),
                questionId,
            ];
        }

        if (nextCorrect >= current.total) {
            if (!completed.value.includes(levelId)) {
                completed.value.push(levelId);
            }

            const next = levelId < TOTAL_LEVELS ? levelId + 1 : null;

            if (next && !unlocked.value.includes(next)) {
                unlocked.value.push(next);
            }

            delete questionProgress.value[key];
            delete answeredQuestions.value[key];
            delete sessionQuestions.value[key];
        }

        delete lockouts.value[key];

        return new Promise((resolve) => {
            router.post(
                `/level-progress/${mode}/question-pass`,
                {
                    level_id: levelId,
                    question_id: questionId,
                    response_text: options?.response_text ?? "",
                    input_mode: options?.input_mode ?? "text",
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onError: () =>
                        syncState(
                            unlocked,
                            completed,
                            lockouts,
                            questionProgress,
                            answeredQuestions,
                            sessionQuestions,
                            tierResets,
                            previous,
                        ),
                    onFinish: () => {
                        syncing.value = false;
                        reloadProgress().then(() => {
                            const progress = questionProgressFor(levelId);

                            resolve({
                                completed: isCompleted(levelId),
                                correct: progress?.correct ?? nextCorrect,
                                total: progress?.total ?? current.total,
                            });
                        });
                    },
                },
            );
        });
    }

    function markFailedWithLockout(
        id: number,
        hours = 24,
        options?: {
            question_id?: number;
            response_text?: string;
            input_mode?: string;
        },
    ): Promise<string> {
        const previous = cloneState(currentSnapshot());
        syncing.value = true;
        const until = new Date(Date.now() + hours * 60 * 60 * 1000);
        lockouts.value[String(id)] = until.toISOString();

        return new Promise((resolve) => {
            router.post(
                `/level-progress/${mode}/fail`,
                {
                    level_id: id,
                    hours,
                    question_id: options?.question_id,
                    response_text: options?.response_text ?? "",
                    input_mode: options?.input_mode ?? "text",
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onError: () =>
                        syncState(
                            unlocked,
                            completed,
                            lockouts,
                            questionProgress,
                            answeredQuestions,
                            sessionQuestions,
                            tierResets,
                            previous,
                        ),
                    onFinish: () => {
                        syncing.value = false;
                        reloadProgress().then(() => resolve(until.toISOString()));
                    },
                },
            );
        });
    }

    function recordPracticeAttempt(
        levelId: number,
        questionId: number,
        isCorrect: boolean,
        options?: {
            response_text?: string;
            input_mode?: string;
            close_session?: boolean;
        },
    ): Promise<void> {
        return new Promise((resolve) => {
            router.post(
                `/level-progress/${mode}/attempt`,
                {
                    level_id: levelId,
                    question_id: questionId,
                    is_correct: isCorrect,
                    response_text: options?.response_text ?? "",
                    input_mode: options?.input_mode ?? "text",
                    close_session: options?.close_session ?? false,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onFinish: () => resolve(),
                },
            );
        });
    }

    function skipLockout(id: number): Promise<void> {
        const previous = cloneState(currentSnapshot());
        syncing.value = true;
        delete lockouts.value[String(id)];

        return new Promise((resolve, reject) => {
            router.post(
                `/level-progress/${mode}/skip-lockout`,
                { level_id: id },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onError: () => {
                        syncState(
                            unlocked,
                            completed,
                            lockouts,
                            questionProgress,
                            answeredQuestions,
                            sessionQuestions,
                            tierResets,
                            previous,
                        );
                        reject(new Error("skip_lockout_failed"));
                    },
                    onFinish: () => {
                        syncing.value = false;
                        reloadProgress().then(() => resolve()).catch(() => resolve());
                    },
                },
            );
        });
    }

    function hasProgress(id: number): boolean {
        return (
            isCompleted(id)
            || isPending(id)
            || isLockedOut(id)
        );
    }

    function hasTierProgress(tier: TierSlug): boolean {
        return levelIdsForTier(tier).some((id) => hasProgress(id));
    }

    function tierResetFor(tier: TierSlug): TierResetInfo {
        return tierResets.value[tier] ?? DEFAULT_TIER_RESETS[tier];
    }

    function isTierFullyCompleted(tier: TierSlug): boolean {
        return levelIdsForTier(tier).every((id) => isCompleted(id));
    }

    function tierResetLabel(tier: TierSlug): string | null {
        const info = tierResetFor(tier);

        if (!isTierFullyCompleted(tier) && info.count === 0) {
            return null;
        }

        if (info.count >= info.max && !isTierFullyCompleted(tier)) {
            return null;
        }

        if (isTierFullyCompleted(tier) || info.count > 0) {
            return `Reinicios: ${info.count}/${info.max}`;
        }

        return null;
    }

    function canResetTier(tier: TierSlug): boolean {
        if (!isTierFullyCompleted(tier)) {
            return false;
        }

        const info = tierResetFor(tier);

        return info.count < info.max;
    }

    function resetTier(tier: TierSlug): Promise<void> {
        const previous = cloneState(currentSnapshot());
        const ids = levelIdsForTier(tier);
        const resetInfo = tierResetFor(tier);
        syncing.value = true;

        completed.value = completed.value.filter((id) => !ids.includes(id));

        for (const id of ids) {
            const key = String(id);
            delete lockouts.value[key];
            delete questionProgress.value[key];
            delete answeredQuestions.value[key];
            delete sessionQuestions.value[key];
        }

        tierResets.value = {
            ...tierResets.value,
            [tier]: {
                ...resetInfo,
                count: Math.min(resetInfo.count + 1, resetInfo.max),
            },
        };

        return new Promise((resolve) => {
            router.post(
                `/level-progress/${mode}/reset-tier`,
                { tier },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onError: () =>
                        syncState(
                            unlocked,
                            completed,
                            lockouts,
                            questionProgress,
                            answeredQuestions,
                            sessionQuestions,
                            tierResets,
                            previous,
                        ),
                    onFinish: () => {
                        syncing.value = false;
                        reloadProgress().then(resolve);
                    },
                },
            );
        });
    }

    function resetProgress(): Promise<void> {
        syncing.value = true;

        return new Promise((resolve) => {
            router.post(
                `/level-progress/${mode}/reset`,
                {},
                {
                    preserveScroll: true,
                    onFinish: () => {
                        syncing.value = false;
                        reloadProgress().then(resolve);
                    },
                },
            );
        });
    }

    const progressPercent = computed(() =>
        Math.round((completed.value.length / TOTAL_LEVELS) * 100),
    );

    const completedCount = computed(() => completed.value.length);

    return {
        unlocked,
        completed,
        completedCount,
        lockouts,
        questionProgress,
        answeredQuestions,
        sessionQuestions,
        syncing,
        isUnlocked,
        isCompleted,
        isPending,
        questionProgressFor,
        answeredQuestionsFor,
        sessionQuestionsFor,
        isLockedOut,
        lockoutRemaining,
        hasProgress,
        hasTierProgress,
        isTierFullyCompleted,
        tierResetFor,
        tierResetLabel,
        canResetTier,
        markQuestionPassed,
        startSession,
        markFailedWithLockout,
        skipLockout,
        recordPracticeAttempt,
        resetTier,
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

function levenshteinDistance(a: string, b: string): number {
    const rows = a.length + 1;
    const cols = b.length + 1;
    const matrix = Array.from({ length: rows }, () => Array<number>(cols).fill(0));

    for (let row = 0; row < rows; row++) {
        matrix[row][0] = row;
    }

    for (let col = 0; col < cols; col++) {
        matrix[0][col] = col;
    }

    for (let row = 1; row < rows; row++) {
        for (let col = 1; col < cols; col++) {
            const cost = a[row - 1] === b[col - 1] ? 0 : 1;

            matrix[row][col] = Math.min(
                matrix[row - 1][col] + 1,
                matrix[row][col - 1] + 1,
                matrix[row - 1][col - 1] + cost,
            );
        }
    }

    return matrix[a.length][b.length];
}

export const SPEAKING_PASS_SCORE_MIN = 95;

export function scoreSpokenPhrase(spoken: string, expected: string): number {
    const a = normalizeText(spoken);
    const b = normalizeText(expected);

    if (!a || !b) {
        return 0;
    }

    if (a === b) {
        return 100;
    }

    const aWords = a.split(" ").filter((word) => word.length > 0);
    const bWords = b.split(" ").filter((word) => word.length > 0);

    if (bWords.length === 1 && aWords.length === 1) {
        const spokenWord = aWords[0];
        const expectedWord = bWords[0];
        const maxLength = Math.max(spokenWord.length, expectedWord.length);
        const distance = levenshteinDistance(spokenWord, expectedWord);

        return Math.max(0, Math.round((1 - distance / maxLength) * 100));
    }

    const significant = (words: string[]) => words.filter((word) => word.length >= 2);
    const aSig = significant(aWords);
    const bSig = significant(bWords);

    if (bSig.length === 0) {
        return a === b ? 100 : 0;
    }

    const matches = aSig.filter((word) => bSig.includes(word)).length;

    return Math.round((matches / bSig.length) * 100);
}

export function compareSpokenPhrase(spoken: string, expected: string): boolean {
    return scoreSpokenPhrase(spoken, expected) >= SPEAKING_PASS_SCORE_MIN;
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
