<script setup lang="ts">
import LevelGrid from "@/Components/LevelGrid.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { levelId, useLevelProgress } from "@/composables/useLevelProgress";
import type { QuizChallenge, QuizFeedback, TierInfo } from "@/types/levels";
import {
    ArrowLeft,
    CheckCircle2,
    Lock,
    XCircle,
} from "@lucide/vue";
import { computed, ref } from "vue";

const props = defineProps<{
    tiers: TierInfo[];
    challenges: QuizChallenge[];
}>();

type Step = "map" | "quiz" | "feedback";

const step = ref<Step>("map");
const selectedLevelId = ref<number | null>(null);
const selectedOption = ref<number | null>(null);
const feedback = ref<QuizFeedback | null>(null);

const progress = useLevelProgress("tracks-level-progress");

const selectedChallenge = computed(() =>
    props.challenges.find((c) => c.id === selectedLevelId.value) ?? null,
);

const tierLabel: Record<string, string> = {
    basico: "Básico",
    intermedio: "Intermedio",
    avanzado: "Avanzado",
};

function formatLockoutRemaining(id: number): string | null {
    const until = progress.lockoutRemaining(id);

    if (!until) {
        return null;
    }

    const remaining = new Date(until).getTime() - Date.now();
    const hours = Math.floor(remaining / (1000 * 60 * 60));
    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));

    return `Bloqueado ${hours}h ${minutes}m`;
}

function selectLevel(id: number): void {
    if (!progress.isUnlocked(id) || progress.isLockedOut(id)) {
        return;
    }

    selectedLevelId.value = id;
    selectedOption.value = null;
    feedback.value = null;
    step.value = "quiz";
}

function backToMap(): void {
    step.value = "map";
    selectedLevelId.value = null;
    selectedOption.value = null;
    feedback.value = null;
}

function submitAnswer(): void {
    const challenge = selectedChallenge.value;

    if (!challenge || selectedOption.value === null || !selectedLevelId.value) {
        return;
    }

    const isCorrect = selectedOption.value === challenge.correct_index;
    let lockedUntil: string | null = null;

    if (isCorrect) {
        progress.markPassed(selectedLevelId.value);
    } else {
        lockedUntil = progress.markFailedWithLockout(selectedLevelId.value, 24);
    }

    feedback.value = {
        is_correct: isCorrect,
        correct_answer: challenge.options[challenge.correct_index],
        message: isCorrect
            ? "¡Correcto! Has desbloqueado el siguiente nivel."
            : "Respuesta incorrecta. Este nivel queda bloqueado por 24 horas.",
        locked_until: lockedUntil,
    };

    step.value = "feedback";
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Tracks · Vocabulario
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Elige la traducción correcta entre 3 opciones.
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
                <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Si fallas un nivel, queda bloqueado 24 horas. Acierta para desbloquear el siguiente.
                </div>

                <LevelGrid
                    :tiers="tiers"
                    :is-unlocked="progress.isUnlocked"
                    :is-completed="progress.isCompleted"
                    :is-locked-out="progress.isLockedOut"
                    :lockout-label="formatLockoutRemaining"
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
                v-else-if="step === 'quiz' && selectedChallenge"
                class="space-y-4"
            >
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700">
                            {{ tierLabel[selectedChallenge.tier] }} · Fase {{ selectedChallenge.phase }}
                        </span>
                        <span class="text-xs text-gray-400">
                            Opción múltiple
                        </span>
                    </div>

                    <p class="mb-1 text-sm font-medium text-gray-500">
                        ¿Cuál es la traducción correcta de?
                    </p>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ selectedChallenge.prompt }}
                    </h2>
                </div>

                <div class="space-y-3">
                    <button
                        v-for="(option, index) in selectedChallenge.options"
                        :key="index"
                        type="button"
                        class="w-full rounded-xl border p-4 text-left text-sm font-medium transition-all"
                        :class="
                            selectedOption === index
                                ? 'border-blue-500 bg-blue-50 text-blue-900 ring-2 ring-blue-200'
                                : 'border-gray-200 bg-white text-gray-800 hover:border-blue-300 hover:bg-blue-50/40'
                        "
                        @click="selectedOption = index"
                    >
                        <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                            {{ String.fromCharCode(65 + index) }}
                        </span>
                        {{ option }}
                    </button>
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50"
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
                            ? 'border-emerald-100 bg-emerald-50'
                            : 'border-red-100 bg-red-50'
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
                        <h2 class="font-semibold text-gray-900">
                            {{ feedback.is_correct ? "¡Correcto!" : "Nivel bloqueado" }}
                        </h2>
                    </div>
                    <p class="text-sm text-gray-700">
                        {{ feedback.message }}
                    </p>
                    <p
                        v-if="!feedback.is_correct && feedback.locked_until"
                        class="mt-2 text-sm font-medium text-red-700"
                    >
                        Podrás reintentar después de las
                        {{ new Date(feedback.locked_until).toLocaleString("es-ES") }}.
                    </p>
                </div>

                <div
                    v-if="!feedback.is_correct"
                    class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-gray-600">
                        Respuesta correcta:
                        <strong class="text-gray-900">{{ feedback.correct_answer }}</strong>
                    </p>
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-violet-700"
                    @click="backToMap"
                >
                    Volver al mapa
                </button>
            </div>
        </div>
    </AppLayout>
</template>
