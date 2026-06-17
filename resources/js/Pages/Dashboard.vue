<script setup lang="ts">
import ProgressChart from "@/Components/ProgressChart.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import type { LearnerDashboardData } from "@/types/progress";
import { Link } from "@inertiajs/vue3";
import { Flame, Mic, Target, TrendingUp } from "@lucide/vue";

defineProps<{
    progress: LearnerDashboardData;
}>();

const levelLabel: Record<string, string> = {
    beginner: "Principiante",
    intermediate: "Intermedio",
    advanced: "Avanzado",
};

function formatDate(iso: string | null): string {
    if (!iso) {
        return "Sin prácticas";
    }

    return new Date(iso).toLocaleDateString("es-ES", {
        day: "numeric",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Tu progreso
                    </h1>
                    <p class="mt-1 text-gray-500">
                        Curva de aprendizaje en inglés para desarrollo de software.
                    </p>
                </div>
                <Link
                    href="/practice"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <Mic class="h-4 w-4" />
                    Nueva práctica
                </Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <TrendingUp class="h-4 w-4" />
                        Precisión media
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ progress.summary.avg_accuracy }}%
                    </p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <Target class="h-4 w-4" />
                        Nivel estimado
                    </div>
                    <p class="mt-2 text-2xl font-bold capitalize text-gray-900">
                        {{ levelLabel[progress.summary.current_level] ?? progress.summary.current_level }}
                    </p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <Flame class="h-4 w-4" />
                        Racha
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ progress.summary.streak_days }} días
                    </p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">
                        Sesiones completadas
                    </p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        {{ progress.summary.total_sessions }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <ProgressChart :points="progress.chart_points" />

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-medium text-gray-700">
                        Próximo paso sugerido
                    </h2>
                    <p class="text-sm text-gray-600">
                        Continúa con nivel
                        <strong class="capitalize text-gray-900">
                            {{ levelLabel[progress.summary.suggested_level] ?? progress.summary.suggested_level }}
                        </strong>
                        en Technical Interviews o refuerza vocabulario en Dev Vocabulary.
                    </p>
                    <p class="mt-4 text-xs text-gray-500">
                        Última práctica: {{ formatDate(progress.summary.last_practice_at) }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Sesiones recientes
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-gray-100 text-gray-500">
                            <tr>
                                <th class="pb-3 pr-4 font-medium">Track</th>
                                <th class="pb-3 pr-4 font-medium">Fecha</th>
                                <th class="pb-3 pr-4 font-medium">Preguntas</th>
                                <th class="pb-3 font-medium">Precisión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="session in progress.recent_sessions"
                                :key="session.id"
                            >
                                <td class="py-3 pr-4 font-medium text-gray-900">
                                    {{ session.track_name }}
                                </td>
                                <td class="py-3 pr-4 text-gray-600">
                                    {{ formatDate(session.completed_at) }}
                                </td>
                                <td class="py-3 pr-4 text-gray-600">
                                    {{ session.question_count }}
                                </td>
                                <td class="py-3 text-gray-900">
                                    {{ session.accuracy_pct }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
