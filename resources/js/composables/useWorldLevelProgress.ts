import type { WorldProgressState } from "@/types/world";
import { WORLD_QUESTIONS_PER_LEVEL } from "@/types/world";
import { router } from "@inertiajs/vue3";
import { ref, toValue, watch, type MaybeRef } from "vue";

function cloneState(state: WorldProgressState): WorldProgressState {
    return {
        unlocked: [...state.unlocked],
        completed: [...state.completed],
        lockouts: { ...(state.lockouts ?? {}) },
        question_progress: { ...(state.question_progress ?? {}) },
        answered_questions: { ...(state.answered_questions ?? {}) },
        session_questions: { ...(state.session_questions ?? {}) },
    };
}

function syncState(
    unlocked: { value: number[] },
    completed: { value: number[] },
    lockouts: { value: Record<string, string> },
    questionProgress: { value: Record<string, { correct: number; total: number }> },
    answeredQuestions: { value: Record<string, number[]> },
    sessionQuestions: { value: Record<string, number[]> },
    source: WorldProgressState,
): void {
    unlocked.value = [...source.unlocked];
    completed.value = [...source.completed];
    lockouts.value = { ...(source.lockouts ?? {}) };
    questionProgress.value = { ...(source.question_progress ?? {}) };
    answeredQuestions.value = { ...(source.answered_questions ?? {}) };
    sessionQuestions.value = { ...(source.session_questions ?? {}) };
}

export function useWorldLevelProgress(initial: MaybeRef<WorldProgressState>) {
    const source = toValue(initial);
    const unlocked = ref<number[]>([...source.unlocked]);
    const completed = ref<number[]>([...source.completed]);
    const lockouts = ref<Record<string, string>>({ ...(source.lockouts ?? {}) });
    const questionProgress = ref<Record<string, { correct: number; total: number }>>({
        ...(source.question_progress ?? {}),
    });
    const answeredQuestions = ref<Record<string, number[]>>({
        ...(source.answered_questions ?? {}),
    });
    const sessionQuestions = ref<Record<string, number[]>>({
        ...(source.session_questions ?? {}),
    });
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
            value,
        ),
        { deep: true },
    );

    function currentSnapshot(): WorldProgressState {
        return {
            unlocked: [...unlocked.value],
            completed: [...completed.value],
            lockouts: { ...lockouts.value },
            question_progress: { ...questionProgress.value },
            answered_questions: { ...answeredQuestions.value },
            session_questions: { ...sessionQuestions.value },
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

    function questionProgressFor(id: number): { correct: number; total: number } | null {
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
                `/world/levels/${levelId}/session`,
                {},
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: (page) => {
                        const progress = page.props.progress as WorldProgressState | undefined;

                        if (progress) {
                            syncState(
                                unlocked,
                                completed,
                                lockouts,
                                questionProgress,
                                answeredQuestions,
                                sessionQuestions,
                                progress,
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
        options?: { response_text?: string },
    ): Promise<{ completed: boolean; correct: number; total: number }> {
        const previous = cloneState(currentSnapshot());
        syncing.value = true;

        const key = String(levelId);
        const current = questionProgress.value[key] ?? {
            correct: 0,
            total: WORLD_QUESTIONS_PER_LEVEL,
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

            const next = levelId < 18 ? levelId + 1 : null;

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
                `/world/levels/${levelId}/answer`,
                {
                    question_id: questionId,
                    response_text: options?.response_text ?? "",
                    input_mode: "choice",
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onError: () => syncState(
                        unlocked,
                        completed,
                        lockouts,
                        questionProgress,
                        answeredQuestions,
                        sessionQuestions,
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
        levelId: number,
        hours = 2,
        options?: { question_id?: number; response_text?: string },
    ): Promise<string> {
        const previous = cloneState(currentSnapshot());
        syncing.value = true;
        const until = new Date(Date.now() + hours * 60 * 60 * 1000);
        lockouts.value[String(levelId)] = until.toISOString();

        return new Promise((resolve) => {
            router.post(
                `/world/levels/${levelId}/fail`,
                {
                    question_id: options?.question_id,
                    response_text: options?.response_text ?? "",
                    input_mode: "choice",
                    hours,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onError: () => syncState(
                        unlocked,
                        completed,
                        lockouts,
                        questionProgress,
                        answeredQuestions,
                        sessionQuestions,
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

    return {
        unlocked,
        completed,
        lockouts,
        questionProgress,
        answeredQuestions,
        sessionQuestions,
        syncing,
        isUnlocked,
        isCompleted,
        isPending,
        isLockedOut,
        lockoutRemaining,
        questionProgressFor,
        answeredQuestionsFor,
        sessionQuestionsFor,
        startSession,
        markQuestionPassed,
        markFailedWithLockout,
        reloadProgress,
    };
}
