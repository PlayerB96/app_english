<script setup lang="ts">
import DifficultyBadge from "@/Components/DifficultyBadge.vue";
import LevelGrid from "@/Components/LevelGrid.vue";
import { levelId, useLevelProgress } from "@/composables/useLevelProgress";
import { useInitialLevelQuery } from "@/composables/useInitialLevelQuery";
import { confirmResetTier } from "@/utils/confirmResetTier";
import { formatLockoutRemaining } from "@/utils/formatLockout";
import { showCompletedQuizLevel } from "@/utils/showCompletedLevel";
import { showLockoutLevel } from "@/utils/showLockoutLevel";
import { shuffleQuizOptions } from "@/utils/shuffleQuizOptions";
import { buildLevelSessionQuestions } from "@/utils/buildLevelSessionQuestions";
import { sublevelLabel } from "@/utils/learningLabels";
import type {
    LevelProgressState,
    QuizFeedback,
    QuizLevel,
    TierInfo,
} from "@/types/levels";
import {
    ArrowLeft,
    CheckCircle2,
    Lock,
    XCircle,
} from "@lucide/vue";
import { usePage } from "@inertiajs/vue3";
import { computed, ref, toRef, watch } from "vue";
import type { PageProps } from "@/types/auth";

const props = defineProps<{
    tiers: TierInfo[];
    levels: QuizLevel[];
    progress: LevelProgressState;
}>();

type Step = "map" | "quiz" | "feedback";

const QUESTIONS_PER_LEVEL = 3;

const step = ref<Step>("map");
const selectedLevelId = ref<number | null>(null);
const selectedOption = ref<number | null>(null);
const feedback = ref<QuizFeedback | null>(null);
const shuffledOptions = ref<{
    options: [string, string, string];
    correct_index: number;
} | null>(null);

const progress = useLevelProgress("quiz", toRef(props, "progress"));

const page = usePage<{ auth: PageProps["auth"]; game: PageProps["game"] }>();

const selectedLevel = computed(
    () => props.levels.find((level) => level.id === selectedLevelId.value) ?? null,
);

const sessionQuestionIds = computed(() => {
    if (selectedLevelId.value === null) {
        return [];
    }

    return progress.sessionQuestionsFor(selectedLevelId.value);
});

const activeQuestions = computed(() => {
    const level = selectedLevel.value;
    const sessionIds = sessionQuestionIds.value;

    if (!level || sessionIds.length === 0) {
        return [];
    }

    return buildLevelSessionQuestions(level.questions, sessionIds);
});

const currentQuestion = computed(() => {
    const level = selectedLevel.value;

    if (!level) {
        return null;
    }

    const answered = progress.answeredQuestionsFor(level.id);

    return (
        activeQuestions.value.find(
            (question) => !answered.includes(question.question_id),
        ) ?? null
    );
});

watch(
    currentQuestion,
    (question) => {
        selectedOption.value = null;

        if (!question) {
            shuffledOptions.value = null;
            return;
        }

        shuffledOptions.value = shuffleQuizOptions(
            question.options,
            question.correct_index,
        );
    },
    { immediate: true },
);

const questionPosition = computed(() => {
    if (!selectedLevel.value || !currentQuestion.value) {
        return null;
    }

    return {
        current: currentQuestion.value.question_index,
        total: QUESTIONS_PER_LEVEL,
    };
});

const tierLabel: Record<string, string> = {
    basico: "Módulo Básico",
    intermedio: "Módulo Intermedio",
    avanzado: "Módulo Avanzado",
};

function formatLockoutLabel(id: number): string | null {
    return formatLockoutRemaining(progress.lockoutRemaining(id));
}

function formatPendingLabel(id: number): string | null {
    const item = progress.questionProgressFor(id);

    if (!item) {
        return null;
    }

    return `${item.correct}/${item.total}`;
}

async function handleResetTier(tier: TierInfo): Promise<void> {
    const info = progress.tierResetFor(tier.slug);
    const confirmed = await confirmResetTier({
        tierName: tier.name,
        cost: info.cost,
        resetCount: info.count,
        maxResets: info.max,
    });

    if (!confirmed) {
        return;
    }

    await progress.resetTier(tier.slug);
}

const sessionError = ref<string | null>(null);

async function selectLevel(id: number): Promise<void> {
    sessionError.value = null;

    if (!progress.isUnlocked(id) || progress.isLockedOut(id)) {
        return;
    }

    if (progress.isCompleted(id)) {
        return;
    }

    selectedLevelId.value = id;

    try {
        await progress.startSession(id);
    } catch {
        selectedLevelId.value = null;
        const errors = page.props.errors as Record<string, string> | undefined;
        sessionError.value =
            errors?.level_id ?? "No se pudo iniciar el subnivel. Inténtalo de nuevo.";

        return;
    }

    if (activeQuestions.value.length === 0) {
        selectedLevelId.value = null;
        sessionError.value =
            "No hay preguntas disponibles para este subnivel. Contacta al administrador.";

        return;
    }

    selectedOption.value = null;
    feedback.value = null;
    step.value = "quiz";
}

function viewCompletedLevel(id: number): void {
    const level = props.levels.find((item) => item.id === id);

    if (!level) {
        return;
    }

    void showCompletedQuizLevel(tierLabel[level.tier], level.phase, level.id);
}

function viewLockedLevel(id: number): void {
    const level = props.levels.find((item) => item.id === id);
    const lockedUntil = progress.lockoutRemaining(id);

    if (!level || !lockedUntil) {
        return;
    }

    void showLockoutLevel({
        moduleName: tierLabel[level.tier],
        phase: level.phase,
        lockedUntil,
        tokens: page.props.auth.user?.tokens ?? 0,
        skipCost: page.props.game.skip_lockout_cost,
        onSkip: () => progress.skipLockout(id),
    });
}

function backToMap(): void {
    step.value = "map";
    selectedLevelId.value = null;
    selectedOption.value = null;
    feedback.value = null;
}

async function submitAnswer(): Promise<void> {
    const question = currentQuestion.value;
    const options = shuffledOptions.value;
    const levelIdValue = selectedLevelId.value;

    if (!question || !options || levelIdValue === null || selectedOption.value === null) {
        return;
    }

    const isCorrect = selectedOption.value === options.correct_index;
    let lockedUntil: string | null = null;
    let result = {
        completed: false,
        correct: progress.questionProgressFor(levelIdValue)?.correct ?? 0,
        total: QUESTIONS_PER_LEVEL,
    };

    if (isCorrect) {
        result = await progress.markQuestionPassed(
            levelIdValue,
            question.question_id,
            {
                response_text: options.options[selectedOption.value],
                input_mode: "choice",
            },
        );
    } else {
        lockedUntil = await progress.markFailedWithLockout(levelIdValue, 24, {
            question_id: question.question_id,
            response_text: options.options[selectedOption.value],
            input_mode: "choice",
        });
    }

    feedback.value = {
        is_correct: isCorrect,
        correct_answer: options.options[options.correct_index],
        message: isCorrect
            ? result.completed
                ? "¡Subnivel aprobado! Respondiste correctamente las 3 preguntas."
                : `¡Correcto! Llevas ${result.correct}/${result.total} preguntas del subnivel.`
            : "Respuesta incorrecta. Este subnivel queda bloqueado por 24 horas.",
        locked_until: lockedUntil,
        level_completed: result.completed,
        questions_correct: result.correct,
        questions_total: result.total,
    };

    step.value = "feedback";
}

function continueAfterFeedback(): void {
    if (!feedback.value?.is_correct || feedback.value.level_completed) {
        backToMap();

        return;
    }

    selectedOption.value = null;
    feedback.value = null;
    step.value = "quiz";
}

useInitialLevelQuery(async (id) => {
    if (progress.isLockedOut(id)) {
        viewLockedLevel(id);

        return;
    }

    if (!progress.isUnlocked(id) || progress.isCompleted(id)) {
        return;
    }

    await selectLevel(id);
});
</script>

<template>
    <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-heading">
                        Tracks · Vocabulario
                    </h1>
                    <p class="mt-1 text-sm text-muted">
                        Cada subnivel tiene 3 preguntas (Fácil → Medio → Difícil). Debes acertar las 3 para aprobarlo.
                        {{ progress.completedCount }}/{{ progress.totalLevels }} subniveles completados.
                    </p>
                </div>
                <button
                    v-if="step !== 'map'"
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-body hover:text-heading"
                    @click="backToMap"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Mapa de módulos
                </button>
            </div>

            <div
                v-if="step === 'map'"
                class="space-y-4"
            >
                <div class="alert-warn">
                    Si fallas una pregunta, el subnivel queda bloqueado 24 horas. La dificultad sube en cada etapa del módulo.
                </div>

                <div
                    v-if="sessionError"
                    role="alert"
                    class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200"
                >
                    {{ sessionError }}
                </div>

                <LevelGrid
                    :tiers="tiers"
                    :is-unlocked="progress.isUnlocked"
                    :is-completed="progress.isCompleted"
                    :is-pending="progress.isPending"
                    :pending-label="formatPendingLabel"
                    :is-locked-out="progress.isLockedOut"
                    :lockout-label="formatLockoutLabel"
                    :can-reset-tier="progress.canResetTier"
                    :tier-reset-label="progress.tierResetLabel"
                    :tier-reset-cost="(slug) => progress.tierResetFor(slug).cost"
                    :level-id="levelId"
                    :selected-id="selectedLevelId"
                    @select="selectLevel"
                    @view-completed="viewCompletedLevel"
                    @view-locked-out="viewLockedLevel"
                    @reset-tier="handleResetTier"
                />
            </div>

            <div
                v-else-if="step === 'quiz' && selectedLevel && currentQuestion && shuffledOptions && activeQuestions.length > 0"
                class="space-y-4"
            >
                <div class="surface-card p-6">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                            {{ tierLabel[selectedLevel.tier] }} · {{ sublevelLabel(selectedLevel.phase) }}
                        </span>
                        <span
                            v-if="questionPosition"
                            class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950/60 dark:text-amber-300"
                        >
                            Pregunta {{ questionPosition.current }}/{{ questionPosition.total }}
                        </span>
                        <DifficultyBadge :difficulty="currentQuestion.step_difficulty" />
                    </div>

                    <p class="mb-1 text-sm font-medium text-muted">
                        ¿Cuál es la traducción correcta de?
                    </p>
                    <h2 class="text-2xl font-bold text-heading">
                        {{ currentQuestion.prompt }}
                    </h2>
                </div>

                <div class="grid gap-3 lg:grid-cols-3 lg:gap-4">
                    <button
                        v-for="(option, index) in shuffledOptions.options"
                        :key="`${currentQuestion.question_id}-${index}-${option}`"
                        type="button"
                        class="w-full rounded-xl border p-4 text-left text-sm font-medium transition-all"
                        :class="
                            selectedOption === index
                                ? 'border-blue-500 bg-blue-50 text-blue-900 ring-2 ring-blue-200 dark:border-blue-500 dark:bg-blue-950/50 dark:text-blue-200 dark:ring-blue-900'
                                : 'border-gray-200 bg-white text-heading hover:border-blue-300 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-blue-600 dark:hover:bg-blue-950/30'
                        "
                        @click="selectedOption = index"
                    >
                        <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-body dark:bg-gray-800">
                            {{ String.fromCharCode(65 + index) }}
                        </span>
                        {{ option }}
                    </button>
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-violet-500 dark:hover:bg-violet-600"
                    :disabled="selectedOption === null"
                    @click="submitAnswer"
                >
                    Confirmar respuesta
                </button>
            </div>

            <div
                v-else-if="step === 'feedback' && feedback"
                class="space-y-4"
            >
                <div
                    class="rounded-2xl border p-5 shadow-sm"
                    :class="
                        feedback.is_correct
                            ? 'border-emerald-100 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40'
                            : 'border-red-100 bg-red-50 dark:border-red-900 dark:bg-red-950/40'
                    "
                >
                    <div class="mb-2 flex items-center gap-2">
                        <CheckCircle2
                            v-if="feedback.is_correct"
                            class="h-5 w-5 text-emerald-600"
                        />
                        <Lock
                            v-else
                            class="h-5 w-5 text-red-600"
                        />
                        <h2 class="font-semibold text-heading">
                            {{
                                feedback.level_completed
                                    ? "¡Subnivel aprobado!"
                                    : feedback.is_correct
                                      ? "¡Pregunta correcta!"
                                      : "Subnivel bloqueado"
                            }}
                        </h2>
                    </div>
                    <p class="text-sm text-body">
                        {{ feedback.message }}
                    </p>
                    <p
                        v-if="feedback.is_correct && !feedback.level_completed"
                        class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-300"
                    >
                        Progreso del subnivel: {{ feedback.questions_correct }}/{{ feedback.questions_total }}
                    </p>
                    <p
                        v-if="!feedback.is_correct && feedback.locked_until"
                        class="mt-2 text-sm font-medium text-red-700 dark:text-red-300"
                    >
                        Podrás reintentar después de las
                        {{ new Date(feedback.locked_until).toLocaleString("es-ES") }}.
                    </p>
                </div>

                <div
                    v-if="!feedback.is_correct"
                    class="surface-card p-5"
                >
                    <p class="text-sm text-body">
                        Respuesta correcta:
                        <strong class="text-heading">{{ feedback.correct_answer }}</strong>
                    </p>
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-violet-700 dark:bg-violet-500 dark:hover:bg-violet-600"
                    @click="continueAfterFeedback"
                >
                    {{
                        feedback.is_correct && !feedback.level_completed
                            ? "Siguiente pregunta"
                            : "Volver al mapa"
                    }}
                </button>
            </div>
        </div>
</template>
