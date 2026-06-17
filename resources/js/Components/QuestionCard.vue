<script setup lang="ts">
import type { MockQuestion } from "@/types/practice";

defineProps<{
    question: MockQuestion;
    trackName?: string;
}>();

const difficultyLabel: Record<string, string> = {
    beginner: "Principiante",
    intermediate: "Intermedio",
    advanced: "Avanzado",
};
</script>

<template>
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <span
                v-if="trackName"
                class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700"
            >
                {{ trackName }}
            </span>
            <span
                class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium capitalize text-gray-600"
            >
                {{ difficultyLabel[question.difficulty] ?? question.difficulty }}
            </span>
            <span class="text-xs text-gray-400">
                Fuente: {{ question.source }}
            </span>
        </div>

        <h2 class="text-lg font-semibold leading-relaxed text-gray-900">
            {{ question.prompt }}
        </h2>

        <p
            v-if="question.context"
            class="mt-3 rounded-lg bg-gray-50 p-3 text-sm text-gray-600"
        >
            {{ question.context }}
        </p>
    </div>
</template>
