<script setup lang="ts">
import LevelGrid from "@/Components/LevelGrid.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import {
    compareTranslation,
    levelId,
    useLevelProgress,
} from "@/composables/useLevelProgress";
import type {
    SpeakingChallenge,
    SpeakingFeedback,
    TierInfo,
} from "@/types/levels";
import {
    ArrowLeft,
    CheckCircle2,
    Mic,
    MicOff,
    RotateCcw,
    Square,
    XCircle,
} from "@lucide/vue";
import { computed, ref } from "vue";

const props = defineProps<{
    tiers: TierInfo[];
    challenges: SpeakingChallenge[];
}>();

type Step = "map" | "speaking" | "feedback";

const step = ref<Step>("map");
const selectedLevelId = ref<number | null>(null);
const isRecording = ref(false);
const hasRecording = ref(false);
const transcription = ref("");
const translation = ref("");
const feedback = ref<SpeakingFeedback | null>(null);

const progress = useLevelProgress("practice-level-progress");

const selectedChallenge = computed(() =>
    props.challenges.find((c) => c.id === selectedLevelId.value) ?? null,
);

const tierLabel: Record<string, string> = {
    basico: "Básico",
    intermedio: "Intermedio",
    avanzado: "Avanzado",
};

function selectLevel(id: number): void {
    if (!progress.isUnlocked(id) || progress.isLockedOut(id)) {
        return;
    }

    selectedLevelId.value = id;
    resetSpeakingState();
    step.value = "speaking";
}

function resetSpeakingState(): void {
    isRecording.value = false;
    hasRecording.value = false;
    transcription.value = "";
    translation.value = "";
    feedback.value = null;
}

function backToMap(): void {
    step.value = "map";
    selectedLevelId.value = null;
    resetSpeakingState();
}

function toggleRecording(): void {
    if (isRecording.value) {
        stopRecording();

        return;
    }

    startRecording();
}

function startRecording(): void {
    const challenge = selectedChallenge.value;

    if (!challenge) {
        return;
    }

    isRecording.value = true;
    hasRecording.value = false;
    transcription.value = "";
    translation.value = "";
}

function stopRecording(): void {
    const challenge = selectedChallenge.value;

    if (!challenge) {
        return;
    }

    isRecording.value = false;
    hasRecording.value = true;
    transcription.value = challenge.prompt;
    translation.value = challenge.expected_translation;
}

function validateSpeaking(): void {
    const challenge = selectedChallenge.value;

    if (!challenge || !hasRecording.value) {
        return;
    }

    const isCorrect = compareTranslation(
        translation.value,
        challenge.expected_translation,
    );

    feedback.value = {
        is_correct: isCorrect,
        transcription: transcription.value,
        translation: translation.value,
        expected_translation: challenge.expected_translation,
        score: isCorrect ? 92 : 58,
        message: isCorrect
            ? "¡Excelente! Tu pronunciación y traducción coinciden con lo esperado."
            : "La traducción no coincide. Escucha el audio de referencia e inténtalo de nuevo.",
    };

    if (isCorrect && selectedLevelId.value) {
        progress.markPassed(selectedLevelId.value);
    }

    step.value = "feedback";
}

function continueAfterFeedback(): void {
    backToMap();
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Práctica · Speaking
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Graba tu voz, valida el audio y compara la traducción.
                        {{ progress.completedCount }}/{{ progress.totalLevels }} niveles completados.
                    </p>
                </div>
                <button
                    v-if="step !== 'map'"
                    type="button"
                    class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900"
                    @click="backToMap"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Mapa de niveles
                </button>
            </div>

            <div
                v-if="step === 'map'"
                class="space-y-4"
            >
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Desbloquea niveles al aprobar cada fase. Solo el Nivel 1 de Básico está disponible al inicio.
                </div>

                <LevelGrid
                    :tiers="tiers"
                    :is-unlocked="progress.isUnlocked"
                    :is-completed="progress.isCompleted"
                    :level-id="levelId"
                    :selected-id="selectedLevelId"
                    @select="selectLevel"
                />

                <button
                    type="button"
                    class="text-xs text-gray-400 underline hover:text-gray-600"
                    @click="progress.resetProgress()"
                >
                    Reiniciar progreso (mock)
                </button>
            </div>

            <div
                v-else-if="step === 'speaking' && selectedChallenge"
                class="space-y-4"
            >
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                            {{ tierLabel[selectedChallenge.tier] }} · Fase {{ selectedChallenge.phase }}
                        </span>
                        <span class="text-xs text-gray-400">
                            Modo speaking
                        </span>
                    </div>

                    <p class="mb-1 text-sm font-medium text-gray-500">
                        Di en voz alta:
                    </p>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ selectedChallenge.prompt }}
                    </h2>
                    <p
                        v-if="selectedChallenge.hint"
                        class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-600"
                    >
                        {{ selectedChallenge.hint }}
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col items-center gap-4">
                        <button
                            type="button"
                            class="flex h-20 w-20 items-center justify-center rounded-full transition-all"
                            :class="
                                isRecording
                                    ? 'animate-pulse bg-red-500 text-white hover:bg-red-600'
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            "
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
                        <p class="text-sm text-gray-600">
                            {{
                                isRecording
                                    ? "Grabando… pulsa para detener"
                                    : hasRecording
                                      ? "Audio capturado. Valida tu respuesta."
                                      : "Pulsa el micrófono para grabar"
                            }}
                        </p>
                    </div>

                    <div
                        v-if="hasRecording"
                        class="mt-6 space-y-4 border-t border-gray-100 pt-6"
                    >
                        <div>
                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">
                                Lo que dijiste (transcripción)
                            </p>
                            <p class="rounded-lg bg-gray-50 p-3 text-sm text-gray-800">
                                {{ transcription }}
                            </p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">
                                Traducción detectada
                            </p>
                            <p class="rounded-lg bg-blue-50 p-3 text-sm text-blue-900">
                                {{ translation }}
                            </p>
                        </div>
                        <div>
                            <p class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-500">
                                Traducción esperada
                            </p>
                            <p class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-900">
                                {{ selectedChallenge.expected_translation }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                            @click="validateSpeaking"
                        >
                            <MicOff class="h-4 w-4" />
                            Validar y comparar
                        </button>

                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
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
                class="space-y-4"
            >
                <div
                    class="rounded-2xl border p-5 shadow-sm"
                    :class="
                        feedback.is_correct
                            ? 'border-emerald-100 bg-emerald-50'
                            : 'border-amber-100 bg-amber-50'
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
                        <h2 class="font-semibold text-gray-900">
                            {{ feedback.is_correct ? "¡Nivel aprobado!" : "Inténtalo de nuevo" }}
                            · {{ feedback.score }}%
                        </h2>
                    </div>
                    <p class="text-sm text-gray-700">
                        {{ feedback.message }}
                    </p>
                    <p
                        v-if="feedback.is_correct"
                        class="mt-2 text-sm font-medium text-emerald-700"
                    >
                        Siguiente nivel desbloqueado.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm text-sm text-gray-600 space-y-2">
                    <p><strong>Transcripción:</strong> {{ feedback.transcription }}</p>
                    <p><strong>Tu traducción:</strong> {{ feedback.translation }}</p>
                    <p><strong>Esperada:</strong> {{ feedback.expected_translation }}</p>
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                    @click="continueAfterFeedback"
                >
                    Volver al mapa
                </button>
            </div>
        </div>
    </AppLayout>
</template>
