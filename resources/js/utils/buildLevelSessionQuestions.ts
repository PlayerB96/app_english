import type { QuizQuestion, SpeakingQuestion, StepDifficulty } from "@/types/levels";

const STEP_DIFFICULTIES: StepDifficulty[] = ["facil", "medio", "dificil"];

export function buildLevelSessionQuestions<T extends QuizQuestion | SpeakingQuestion>(
    pool: T[],
    sessionQuestionIds: number[],
): T[] {
    return sessionQuestionIds
        .map((questionId, index) => {
            const question = pool.find((item) => item.question_id === questionId);

            if (!question) {
                return null;
            }

            return {
                ...question,
                question_index: index + 1,
                step_difficulty: STEP_DIFFICULTIES[index] ?? "dificil",
            };
        })
        .filter((question): question is T => question !== null);
}
