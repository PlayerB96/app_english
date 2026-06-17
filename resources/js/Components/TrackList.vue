<script setup lang="ts">
import type { LearningTrack } from "@/types/practice";
import { Link } from "@inertiajs/vue3";
import { ChevronRight } from "@lucide/vue";

defineProps<{
    tracks: LearningTrack[];
    practiceHref?: boolean;
}>();

const difficultyLabel: Record<string, string> = {
    beginner: "Principiante",
    intermediate: "Intermedio",
    advanced: "Avanzado",
};
</script>

<template>
    <div class="space-y-3">
        <article
            v-for="track in tracks"
            :key="track.id"
            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ track.name }}
                        </h3>
                        <span
                            class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium capitalize text-gray-600"
                        >
                            {{ difficultyLabel[track.difficulty] ?? track.difficulty }}
                        </span>
                        <span
                            v-if="!track.is_active"
                            class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600"
                        >
                            Inactivo
                        </span>
                    </div>
                    <p
                        v-if="track.description"
                        class="text-sm text-gray-600"
                    >
                        {{ track.description }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500">
                        {{ track.session_count }} sesiones completadas
                    </p>
                </div>

                <Link
                    v-if="practiceHref && track.is_active"
                    :href="`/practice?track=${track.slug}`"
                    class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    Practicar
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </div>
        </article>
    </div>
</template>
