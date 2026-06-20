<script setup lang="ts">
import KpiCard from "@/Components/KpiCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import type { AdminDashboardData } from "@/types/admin";
import { Link } from "@inertiajs/vue3";
import { BarChart3, BookOpen, CheckCircle2, Users } from "@lucide/vue";

defineProps<{
    dashboard: AdminDashboardData;
}>();

const levelLabel: Record<string, string> = {
    beginner: "Principiante",
    intermediate: "Intermedio",
    advanced: "Avanzado",
};

function formatDate(iso: string | null): string {
    if (!iso) {
        return "—";
    }

    return new Date(iso).toLocaleDateString("es-ES", {
        day: "numeric",
        month: "short",
    });
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-heading">
                    Panel de administración
                </h1>
                <p class="mt-1 text-muted">
                    KPIs y actividad reciente desde PostgreSQL.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <KpiCard
                    title="Learners"
                    :value="dashboard.kpis.total_learners"
                    :icon="Users"
                    tone="blue"
                />
                <KpiCard
                    title="Prácticas completadas"
                    :value="dashboard.kpis.completed_sessions"
                    :icon="CheckCircle2"
                    tone="green"
                />
                <KpiCard
                    title="Tracks activos"
                    :value="dashboard.kpis.active_tracks"
                    :icon="BookOpen"
                    tone="violet"
                />
                <KpiCard
                    title="Precisión media"
                    :value="`${dashboard.kpis.avg_accuracy}%`"
                    :subtitle="`${dashboard.kpis.active_sessions} sesiones activas`"
                    :icon="BarChart3"
                    tone="amber"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="surface-card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-heading">
                            Learners recientes
                        </h2>
                        <Link
                            href="/admin/users"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Ver todos
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <p
                            v-if="dashboard.recent_learners.length === 0"
                            class="rounded-xl bg-gray-50 px-3 py-4 text-sm text-muted dark:bg-gray-800/60"
                        >
                            Aún no hay actividad de aprendices.
                        </p>
                        <div
                            v-for="learner in dashboard.recent_learners"
                            :key="learner.id"
                            class="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-gray-800/60"
                        >
                            <div>
                                <p class="font-medium text-heading">
                                    {{ learner.name }}
                                </p>
                                <p class="text-xs text-muted">
                                    {{ learner.sessions_completed }} sesiones
                                </p>
                            </div>
                            <div class="text-right text-sm">
                                <p class="font-medium text-heading">
                                    {{ learner.accuracy_pct }}%
                                </p>
                                <p class="text-xs capitalize text-gray-500">
                                    {{ levelLabel[learner.level_estimated] ?? learner.level_estimated }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-heading">
                            Por track
                        </h2>
                        <Link
                            href="/admin/reports"
                            class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Reportes
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="report in dashboard.track_reports"
                            :key="report.track_id"
                            class="rounded-xl border border-gray-100 px-3 py-3 dark:border-gray-800"
                        >
                            <p class="font-medium text-heading">
                                {{ report.track_name }}
                            </p>
                            <p class="mt-1 text-sm text-body">
                                {{ report.sessions_count }} sesiones ·
                                {{ report.avg_accuracy }}% precisión ·
                                {{ report.active_learners }} learners
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
