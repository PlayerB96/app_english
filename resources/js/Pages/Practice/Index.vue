<script setup lang="ts">
import DifficultyBadge from "@/Components/DifficultyBadge.vue";
import LevelGrid from "@/Components/LevelGrid.vue";
import {
    compareSpokenPhrase,
    levelId,
    scoreSpokenPhrase,
    useLevelProgress,
} from "@/composables/useLevelProgress";
import { useInitialLevelQuery } from "@/composables/useInitialLevelQuery";
import { useSpeechRecognition } from "@/composables/useSpeechRecognition";
import { useSpeechSynthesis } from "@/composables/useSpeechSynthesis";
import { confirmResetTier } from "@/utils/confirmResetTier";
import { formatLockoutRemaining } from "@/utils/formatLockout";
import { showCompletedSpeakingLevel } from "@/utils/showCompletedLevel";
import { showLockoutLevel } from "@/utils/showLockoutLevel";
import { sublevelLabel } from "@/utils/learningLabels";
import type {
    LevelProgressState,
    SpeakingFeedback,
    SpeakingLevel,
    TierInfo,
} from "@/types/levels";
import type { SpeechCapturePhase } from "@/types/speech";
import {
    ArrowLeft,
    CheckCircle2,
    Mic,
    MicOff,
    RotateCcw,
    Square,
    Volume2,
    XCircle,
} from "@lucide/vue";
import { usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref, toRef } from "vue";
import type { PageProps } from "@/types/auth";

const props = defineProps<{
    tiers: TierInfo[];
    levels: SpeakingLevel[];
    progress: LevelProgressState;
}>();

type Step = "map" | "speaking" | "feedback";

const step = ref<Step>("map");
const selectedLevelId = ref<number | null>(null);
const isRecording = ref(false);
const capturePhase = ref<SpeechCapturePhase>("idle");
const transcription = ref("");
const feedback = ref<SpeakingFeedback | null>(null);

const speech = useSpeechRecognition();
const promptSpeech = useSpeechSynthesis();

const page = usePage<{ auth: PageProps["auth"]; game: PageProps["game"] }>();

const progress = useLevelProgress("speaking", toRef(props, "progress"));

const hasRecording = computed(() => capturePhase.value === "ready");

const speechSupported = computed(() => speech.isSupported());

const recordingHint = computed(() => {
    if (speech.status.value === "requesting-permission") {
        return "Esperando permiso del micrófono…";
    }

    if (!speechSupported.value) {
        return "Reconocimiento de voz no disponible. Escribe la frase en inglés abajo.";
    }

    if (isRecording.value) {
        return "Escuchando… repite la frase en inglés y pulsa detener";
    }

    if (hasRecording.value) {
        return "Listo. Revisa lo que se escuchó y valida.";
    }

    return "Pulsa el micrófono y di la frase en inglés";
});

const liveTranscript = computed(() => {
    if (!isRecording.value) {
        return "";
    }

    return `${speech.transcript.value} ${speech.interimTranscript.value}`.trim();
});

const selectedLevel = computed(
    () => props.levels.find((level) => level.id === selectedLevelId.value) ?? null,
);

const currentQuestion = computed(() => {
    const level = selectedLevel.value;

    if (!level) {
        return null;
    }

    const answered = progress.answeredQuestionsFor(level.id);

    return (
        level.questions.find(
            (question) => !answered.includes(question.question_id),
        ) ?? null
    );
});

const questionPosition = computed(() => {
    if (!selectedLevel.value || !currentQuestion.value) {
        return null;
    }

    return {
        current: currentQuestion.value.question_index,
        total: selectedLevel.value.questions.length,
    };
});

const tierLabel: Record<string, string> = {
    basico: "Módulo Básico",
    intermedio: "Módulo Intermedio",
    avanzado: "Módulo Avanzado",
};

function formatPendingLabel(id: number): string | null {
    const item = progress.questionProgressFor(id);

    if (!item) {
        return null;
    }

    return `${item.correct}/${item.total}`;
}

function formatLockoutLabel(id: number): string | null {
    return formatLockoutRemaining(progress.lockoutRemaining(id));
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

function selectLevel(id: number): void {
    if (!progress.isUnlocked(id) || progress.isLockedOut(id)) {
        return;
    }

    if (progress.isCompleted(id)) {
        return;
    }

    selectedLevelId.value = id;
    resetSpeakingState();
    step.value = "speaking";
}

function viewCompletedLevel(id: number): void {
    const level = props.levels.find((item) => item.id === id);

    if (!level) {
        return;
    }

    void showCompletedSpeakingLevel(tierLabel[level.tier], level.phase, level.id);
}

function resetSpeakingState(): void {
    speech.abort();
    promptSpeech.cancel();
    isRecording.value = false;
    capturePhase.value = "idle";
    transcription.value = "";
    feedback.value = null;
    speech.resetTranscript();
}

onBeforeUnmount(() => {
    speech.abort();
    promptSpeech.cancel();
});

function backToMap(): void {
    step.value = "map";
    selectedLevelId.value = null;
    resetSpeakingState();
}

async function toggleRecording(): Promise<void> {
    if (isRecording.value) {
        stopRecording();

        return;
    }

    await startRecording();
}

async function startRecording(): Promise<void> {
    if (!currentQuestion.value) {
        return;
    }

    promptSpeech.cancel();
    speech.errorMessage.value = null;
    speech.resetTranscript();
    isRecording.value = true;

    const started = await speech.start("en-US");

    if (!started) {
        isRecording.value = false;
    }
}

function stopRecording(): void {
    transcription.value = speech.stop();
    isRecording.value = false;
    capturePhase.value = transcription.value.trim() ? "ready" : "idle";
}

async function validateSpeaking(): Promise<void> {
    const question = currentQuestion.value;
    const levelIdValue = selectedLevelId.value;

    if (!question || !levelIdValue) {
        return;
    }

    const canValidate = hasRecording.value
        || (!speechSupported.value && transcription.value.trim() !== "");

    if (!canValidate) {
        return;
    }

    const isCorrect = compareSpokenPhrase(transcription.value, question.prompt);
    const score = scoreSpokenPhrase(transcription.value, question.prompt);

    let result = {
        completed: false,
        correct: progress.questionProgressFor(levelIdValue)?.correct ?? 0,
        total: selectedLevel.value?.questions.length ?? 3,
    };

    if (isCorrect) {
        result = await progress.markQuestionPassed(
            levelIdValue,
            question.question_id,
            {
                response_text: transcription.value,
                input_mode: "voice",
            },
        );
    } else {
        await progress.recordPracticeAttempt(
            levelIdValue,
            question.question_id,
            false,
            {
                response_text: transcription.value,
                input_mode: "voice",
            },
        );
    }

    feedback.value = {
        is_correct: isCorrect,
        transcription: transcription.value,
        expected_prompt: question.prompt,
        expected_translation: question.expected_translation,
        score,
        message: isCorrect
            ? result.completed
                ? "¡Subnivel aprobado! Respondiste correctamente las 3 preguntas."
                : `¡Correcto! Llevas ${result.correct}/${result.total} preguntas del subnivel.`
            : `No coincide con «${question.prompt}». Repite la frase en inglés e inténtalo de nuevo.`,
        level_completed: result.completed,
        questions_correct: result.correct,
        questions_total: result.total,
    };

    step.value = "feedback";
}

function continueAfterFeedback(): void {
    if (feedback.value?.level_completed) {
        backToMap();

        return;
    }

    if (feedback.value?.is_correct) {
        resetSpeakingState();
        step.value = "speaking";

        return;
    }

    resetSpeakingState();
    step.value = "speaking";
}

function togglePromptSpeech(): void {
    const prompt = currentQuestion.value?.prompt;

    if (!prompt) {
        return;
    }

    promptSpeech.toggle(prompt, "en-US");
}

useInitialLevelQuery(async (id) => {
    if (progress.isLockedOut(id)) {
        viewLockedLevel(id);

        return;
    }

    if (!progress.isUnlocked(id) || progress.isCompleted(id)) {
        return;
    }

    selectLevel(id);
});
</script>

<template>
    <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-heading">
                        Práctica · Speaking
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
                <div class="alert-info">
                    Desbloquea subniveles al aprobar las 3 preguntas de cada etapa. La dificultad sube dentro de cada módulo.
                </div>

                <LevelGrid
                    :tiers="tiers"
                    :is-unlocked="progress.isUnlocked"
                    :is-completed="progress.isCompleted"
                    :is-pending="progress.isPending"
                    :is-locked-out="progress.isLockedOut"
                    :pending-label="formatPendingLabel"
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
                v-else-if="step === 'speaking' && selectedLevel && currentQuestion"
                class="grid gap-6 xl:grid-cols-2 xl:items-start"
            >
                <div class="surface-card p-6">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
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
                        Di en voz alta:
                    </p>
                    <div class="flex items-start gap-3">
                        <h2 class="flex-1 text-2xl font-bold text-heading">
                            {{ currentQuestion.prompt }}
                        </h2>
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-blue-600 transition-colors hover:border-blue-200 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-900 dark:text-blue-400 dark:hover:border-blue-800 dark:hover:bg-blue-950/40"
                            :title="promptSpeech.isSpeaking.value ? 'Detener audio' : 'Escuchar pronunciación'"
                            :aria-label="promptSpeech.isSpeaking.value ? 'Detener audio' : 'Escuchar pronunciación'"
                            :disabled="!promptSpeech.isSupported()"
                            @click="togglePromptSpeech"
                        >
                            <Square
                                v-if="promptSpeech.isSpeaking.value"
                                class="h-4 w-4"
                            />
                            <Volume2
                                v-else
                                class="h-5 w-5"
                            />
                        </button>
                    </div>
                    <p
                        v-if="currentQuestion.hint"
                        class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-body dark:bg-gray-800/60"
                    >
                        {{ currentQuestion.hint }}
                    </p>
                    <p class="mt-3 text-xs text-muted">
                        Significado en español: {{ currentQuestion.expected_translation }}
                    </p>
                </div>

                <div class="surface-card p-6">
                    <div class="flex flex-col items-center gap-4">
                        <button
                            type="button"
                            class="flex h-20 w-20 items-center justify-center rounded-full transition-all disabled:cursor-not-allowed disabled:opacity-60"
                            :class="
                                isRecording
                                    ? 'animate-pulse bg-red-500 text-white hover:bg-red-600'
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            "
                            :disabled="speech.status.value === 'requesting-permission'"
                            @click="toggleRecording"
                        >
                            <Square
                                v-if="isRecording"
                                class="h-8 w-8"
                            />
                            <Mic
                                v-else
                                class="h-8 w-8"
                            />
                        </button>
                        <p class="text-center text-sm text-body">
                            {{ recordingHint }}
                        </p>
                        <p
                            v-if="speech.errorMessage.value"
                            class="rounded-lg bg-red-50 px-3 py-2 text-center text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300"
                        >
                            {{ speech.errorMessage.value }}
                        </p>
                        <p
                            v-if="isRecording && liveTranscript"
                            class="w-full rounded-lg bg-gray-50 p-3 text-center text-sm text-heading dark:bg-gray-800/60"
                        >
                            {{ liveTranscript }}
                        </p>
                    </div>

                    <div
                        v-if="hasRecording || isRecording || !speechSupported"
                        class="mt-6 space-y-4 border-t border-gray-100 pt-6 dark:border-gray-800"
                    >
                        <div>
                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-muted">
                                Lo que se escuchó (inglés)
                            </p>
                            <textarea
                                v-if="!speechSupported || isRecording"
                                v-model="transcription"
                                rows="2"
                                class="w-full rounded-lg border border-gray-200 bg-white p-3 text-sm text-heading dark:border-gray-700 dark:bg-gray-900"
                                :readonly="speechSupported && isRecording"
                                placeholder="Aparecerá aquí lo que digas en inglés"
                            />
                            <p
                                v-else
                                class="rounded-lg bg-gray-50 p-3 text-sm text-heading dark:bg-gray-800/60"
                            >
                                {{ transcription || "—" }}
                            </p>
                            <p class="mt-1 text-xs text-muted">
                                No importan mayúsculas ni tildes. Debe coincidir con la frase en inglés de la izquierda.
                            </p>
                        </div>

                        <button
                            v-if="hasRecording || (!speechSupported && transcription.trim())"
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                            @click="validateSpeaking"
                        >
                            <MicOff class="h-4 w-4" />
                            Validar respuesta
                        </button>

                        <button
                            v-if="hasRecording || isRecording"
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-body hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                            @click="resetSpeakingState"
                        >
                            <RotateCcw class="h-4 w-4" />
                            Volver a grabar
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-else-if="step === 'feedback' && feedback"
                class="mx-auto w-full max-w-4xl space-y-4 xl:max-w-5xl"
            >
                <div
                    class="rounded-2xl border p-5 shadow-sm"
                    :class="
                        feedback.is_correct
                            ? 'border-emerald-100 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40'
                            : 'border-amber-100 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40'
                    "
                >
                    <div class="mb-2 flex items-center gap-2">
                        <CheckCircle2
                            v-if="feedback.is_correct"
                            class="h-5 w-5 text-emerald-600"
                        />
                        <XCircle
                            v-else
                            class="h-5 w-5 text-amber-600"
                        />
                        <h2 class="font-semibold text-heading">
                            {{
                                feedback.level_completed
                                    ? "¡Subnivel aprobado!"
                                    : feedback.is_correct
                                      ? "¡Pregunta correcta!"
                                      : "Inténtalo de nuevo"
                            }}
                            · {{ feedback.score }}%
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
                        v-if="feedback.level_completed"
                        class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-300"
                    >
                        Siguiente subnivel desbloqueado.
                    </p>
                </div>

                <div class="surface-card space-y-2 p-5 text-sm text-body">
                    <p><strong>Lo que dijiste:</strong> {{ feedback.transcription }}</p>
                    <p><strong>Frase esperada (inglés):</strong> {{ feedback.expected_prompt }}</p>
                    <p><strong>Significado (español):</strong> {{ feedback.expected_translation }}</p>
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                    @click="continueAfterFeedback"
                >
                    {{
                        feedback.level_completed
                            ? "Volver al mapa"
                            : feedback.is_correct
                              ? "Siguiente pregunta"
                              : "Reintentar pregunta"
                    }}
                </button>
            </div>
        </div>
</template>
