<script setup lang="ts">
import DifficultyBadge from "@/Components/DifficultyBadge.vue";
import WorldIcon from "@/Components/WorldIcon.vue";
import type { WorldLevel, WorldQuestion, WorldQuestionType } from "@/types/world";
import type { StepDifficulty } from "@/types/levels";

defineProps<{
    level: WorldLevel;
    question: WorldQuestion;
    zoneLabel: string | null;
    worldName: string;
    questionPosition: { current: number; total: number };
    shuffledOptions: [string, string, string];
    selectedOption: number | null;
}>();

const emit = defineEmits<{
    select: [index: number];
    submit: [];
}>();

const promptLabels: Record<WorldQuestionType, string> = {
    translation: "Which is the correct English word?",
    sentence_completion: "Which word completes the sentence?",
    term_meaning: "Which is the correct meaning?",
    command_context: "Which command would you use?",
    scenario: "Which is the best response?",
};

function difficultyFor(value: string): StepDifficulty {
    if (value === "medio") {
        return "medio";
    }

    if (value === "dificil") {
        return "dificil";
    }

    return "facil";
}
</script>

<template>
    <div class="space-y-4">
        <div class="surface-card p-5 sm:p-6">
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
            <h2 class="text-xl font-bold leading-snug text-heading sm:text-2xl">
                {{ question.prompt }}
            </h2>
        </div>

        <div class="grid gap-3 lg:grid-cols-3">
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
                @click="emit('select', index)"
            >
                <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-body dark:bg-gray-800">
                    {{ String.fromCharCode(65 + index) }}
                </span>
                {{ option }}
            </button>
        </div>

        <button
            type="button"
            class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            :disabled="selectedOption === null"
            @click="emit('submit')"
        >
            Confirmar respuesta
        </button>
    </div>
</template>
