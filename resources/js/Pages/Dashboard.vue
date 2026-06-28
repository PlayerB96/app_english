<script setup lang="ts">
import ProgressChart from "@/Components/ProgressChart.vue";
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
    <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-heading">
                        Tu progreso
                    </h1>
                    <p class="mt-1 text-muted">
                        Curva de aprendizaje en inglés para desarrollo de software.
                    </p>
                </div>
                <Link
                    href="/practice"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    <Mic class="h-4 w-4" />
                    Nueva práctica
                </Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="(stat, index) in [
                        { icon: TrendingUp, label: 'Precisión media', value: `${progress.summary.avg_accuracy}%` },
                        { icon: Target, label: 'Nivel estimado', value: levelLabel[progress.summary.current_level] ?? progress.summary.current_level, capitalize: true },
                        { icon: Flame, label: 'Racha', value: `${progress.summary.streak_days} días` },
                        { icon: null, label: 'Sesiones completadas', value: progress.summary.total_sessions },
                    ]"
                    :key="index"
                    class="surface-card p-5"
                >
                    <div class="flex items-center gap-2 text-sm text-muted">
                        <component
                            :is="stat.icon"
                            v-if="stat.icon"
                            class="h-4 w-4"
                        />
                        {{ stat.label }}
                    </div>
                    <p
                        class="mt-2 text-2xl font-bold text-heading"
                        :class="{ capitalize: stat.capitalize }"
                    >
                        {{ stat.value }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2 xl:gap-8">
                <ProgressChart :points="progress.chart_points" />

                <div class="surface-card p-5">
                    <h2 class="mb-4 text-sm font-medium text-body">
                        Próximo paso sugerido
                    </h2>
                    <p class="text-sm text-body">
                        Continúa en
                        <strong class="text-heading">
                            {{ progress.summary.suggested_track_name }}
                        </strong>
                        con nivel
                        <strong class="capitalize text-heading">
                            {{ levelLabel[progress.summary.suggested_level] ?? progress.summary.suggested_level }}
                        </strong>.
                    </p>
                    <p class="mt-4 text-xs text-muted">
                        Última práctica: {{ formatDate(progress.summary.last_practice_at) }}
                    </p>
                </div>
            </div>

            <div class="surface-card p-5">
                <h2 class="mb-4 text-lg font-semibold text-heading">
                    Sesiones recientes
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-gray-100 text-muted dark:border-gray-800">
                            <tr>
                                <th class="pb-3 pr-4 font-medium">Track</th>
                                <th class="pb-3 pr-4 font-medium">Fecha</th>
                                <th class="pb-3 pr-4 font-medium">Preguntas</th>
                                <th class="pb-3 font-medium">Precisión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr v-if="progress.recent_sessions.length === 0">
                                <td
                                    colspan="4"
                                    class="py-6 text-center text-sm text-muted"
                                >
                                    Aún no tienes sesiones completadas. Empieza en Práctica o Tracks.
                                </td>
                            </tr>
                            <tr
                                v-for="session in progress.recent_sessions"
                                :key="session.id"
                            >
                                <td class="py-3 pr-4 font-medium text-heading">
                                    {{ session.track_name }}
                                </td>
                                <td class="py-3 pr-4 text-body">
                                    {{ formatDate(session.completed_at) }}
                                </td>
                                <td class="py-3 pr-4 text-body">
                                    {{ session.question_count }}
                                </td>
                                <td class="py-3 text-heading">
                                    {{ session.accuracy_pct }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</template>
