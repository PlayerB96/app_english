<script setup lang="ts">
import DifficultyBadge from "@/Components/DifficultyBadge.vue";
import PowerChip from "@/Components/PowerChip.vue";
import WorldIcon from "@/Components/WorldIcon.vue";
import { useLockoutCountdown } from "@/composables/useLockoutCountdown";
import { useSpeechSynthesis } from "@/composables/useSpeechSynthesis";
import type { WorldLevel, WorldQuestion, WorldQuestionType } from "@/types/world";
import type { StepDifficulty } from "@/types/levels";
import { formatLockoutGameTimer } from "@/utils/formatLockout";
import { Clock, Lock, Square, Volume2 } from "@lucide/vue";
import { computed, watch } from "vue";

const props = defineProps<{
    level: WorldLevel;
    question: WorldQuestion;
    zoneLabel: string | null;
    worldName: string;
    questionPosition: { current: number; total: number };
    shuffledOptions: [string, string, string];
    selectedOption: number | null;
    lockedOut?: boolean;
    lockoutUntil?: string | null;
    skipCost?: number;
    tokens?: number;
    skipping?: boolean;
}>();

const emit = defineEmits<{
    select: [index: number];
    submit: [];
    skip: [];
}>();

const promptLabels: Record<WorldQuestionType, string> = {
    translation: "¿Cuál es la palabra correcta en inglés?",
    sentence_completion: "¿Qué palabra completa la frase?",
    term_meaning: "¿Cuál es el significado correcto?",
    command_context: "¿Qué comando usarías?",
    scenario: "¿Cuál es la mejor respuesta?",
};

const hasLockout = computed(() => props.lockedOut === true);

const promptSpeech = useSpeechSynthesis();

watch(
    () => props.question.question_id,
    () => {
        promptSpeech.cancel();
    },
);

const { tick } = useLockoutCountdown(hasLockout);

const lockoutTimer = computed(() => {
    void tick.value;

    if (!props.lockoutUntil) {
        return null;
    }

    return formatLockoutGameTimer(props.lockoutUntil);
});

const canSkip = computed(
    () => hasLockout.value && (props.tokens ?? 0) >= (props.skipCost ?? 0),
);

const skipDisabled = computed(
    () => props.skipping || !canSkip.value,
);

function difficultyFor(value: string): StepDifficulty {
    if (value === "medio") {
        return "medio";
    }

    if (value === "dificil") {
        return "dificil";
    }

    return "facil";
}

function handleSelect(index: number): void {
    if (hasLockout.value) {
        return;
    }

    emit("select", index);
}

function togglePromptSpeech(): void {
    promptSpeech.toggle(props.question.prompt, "en-US");
}
</script>

<template>
    <div class="space-y-4">
        <div
            v-if="hasLockout"
            class="flex flex-wrap items-center gap-x-2.5 gap-y-1 rounded-lg border border-amber-200/70 bg-amber-50/60 px-3 py-2 text-xs dark:border-amber-800/50 dark:bg-amber-950/20"
        >
            <span class="inline-flex items-center gap-1 font-medium text-amber-900 dark:text-amber-200">
                <Lock class="h-3.5 w-3.5 shrink-0 opacity-80" />
                Bloqueado
            </span>
            <span
                v-if="lockoutTimer"
                class="inline-flex items-center gap-1 tabular-nums text-amber-800/90 dark:text-amber-300/90"
            >
                <Clock class="h-3 w-3 shrink-0 opacity-70" />
                {{ lockoutTimer }}
            </span>
            <span class="hidden text-amber-800/75 sm:inline dark:text-amber-300/75">
                · solo lectura
            </span>
            <button
                type="button"
                class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-md border border-amber-300/80 bg-white/80 px-2 py-1 text-[11px] font-semibold text-amber-900 transition-colors hover:bg-amber-100/80 disabled:cursor-not-allowed disabled:opacity-45 dark:border-amber-700/60 dark:bg-amber-950/40 dark:text-amber-100 dark:hover:bg-amber-950/60"
                :disabled="skipDisabled"
                :title="!canSkip && !skipping && skipCost !== undefined ? `Necesitas al menos ${skipCost} poder` : undefined"
                @click="emit('skip')"
            >
                <span>{{ skipping ? "…" : "Desbloquear" }}</span>
                <PowerChip
                    v-if="skipCost !== undefined"
                    :amount="skipCost"
                    sign="−"
                    size="sm"
                />
            </button>
        </div>

        <div
            class="surface-card p-5 sm:p-6"
            :class="{ 'pointer-events-none opacity-75': hasLockout }"
        >
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                    <WorldIcon
                        :tier="level.tier"
                        size-class="h-3 w-3"
                    />
                    {{ worldName }}
                    <template v-if="zoneLabel">
                        <span class="opacity-50">·</span>
                        <WorldIcon
                            v-if="level.is_boss"
                            boss
                            size-class="h-3 w-3"
                        />
                        <WorldIcon
                            v-else-if="level.zone"
                            :zone="level.zone"
                            size-class="h-3 w-3"
                        />
                        {{ zoneLabel }}
                    </template>
                </span>
                <span class="rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">
                    {{ level.is_boss ? `Nivel ${level.id} · Boss` : `Nivel ${level.id}` }}
                </span>
                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                    Pregunta {{ questionPosition.current }}/{{ questionPosition.total }}
                </span>
                <DifficultyBadge :difficulty="difficultyFor(question.difficulty)" />
            </div>

            <div
                v-if="question.context"
                class="mb-4 rounded-lg border border-gray-200 bg-gray-50/80 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/50"
            >
                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                    Contexto
                </p>
                <p class="mt-1 text-sm leading-relaxed text-body">
                    {{ question.context }}
                </p>
            </div>

            <p class="mb-1 text-sm font-medium text-muted">
                {{ promptLabels[question.type] }}
            </p>
            <div class="flex items-start gap-3">
                <h2 class="flex-1 text-xl font-bold leading-snug text-heading sm:text-2xl">
                    {{ question.prompt }}
                </h2>
                <button
                    type="button"
                    class="pointer-events-auto inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-indigo-600 transition-colors hover:border-indigo-200 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:bg-gray-900 dark:text-indigo-400 dark:hover:border-indigo-800 dark:hover:bg-indigo-950/40"
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
        </div>

        <div
            class="grid gap-3 lg:grid-cols-3"
            :class="{ 'pointer-events-none opacity-60': hasLockout }"
        >
            <button
                v-for="(option, index) in shuffledOptions"
                :key="`${question.question_id}-${index}-${option}`"
                type="button"
                class="w-full rounded-xl border p-4 text-left text-sm font-medium transition-all"
                :class="
                    selectedOption === index
                        ? 'border-indigo-500 bg-indigo-50 text-indigo-900 ring-2 ring-indigo-200 dark:border-indigo-500 dark:bg-indigo-950/50 dark:text-indigo-200 dark:ring-indigo-900'
                        : 'border-gray-200 bg-white text-heading hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-indigo-600 dark:hover:bg-indigo-950/30'
                "
                :disabled="hasLockout"
                @click="handleSelect(index)"
            >
                <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-body dark:bg-gray-800">
                    {{ String.fromCharCode(65 + index) }}
                </span>
                {{ option }}
            </button>
        </div>

        <button
            v-if="!hasLockout"
            type="button"
            class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            :disabled="selectedOption === null"
            @click="emit('submit')"
        >
            Confirmar respuesta
        </button>
    </div>
</template>
