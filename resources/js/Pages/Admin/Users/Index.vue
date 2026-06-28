<script setup lang="ts">
import type { AdminLearnerRow } from "@/types/admin";

defineProps<{
    learners: AdminLearnerRow[];
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
        year: "numeric",
    });
}
</script>

<template>
    <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-heading">
                    Usuarios learners
                </h1>
                <p class="mt-1 text-muted">
                    Aprendices registrados con métricas reales de práctica.
                </p>
            </div>

            <div class="surface-table">
                <table class="min-w-full text-left text-sm">
                    <thead class="surface-table-head">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nombre</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Sesiones</th>
                            <th class="px-4 py-3 font-medium">Última práctica</th>
                            <th class="px-4 py-3 font-medium">Nivel</th>
                            <th class="px-4 py-3 font-medium">Precisión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr v-if="learners.length === 0">
                            <td
                                colspan="6"
                                class="px-4 py-8 text-center text-sm text-muted"
                            >
                                Aún no hay aprendices registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="learner in learners"
                            :key="learner.id"
                        >
                            <td class="px-4 py-3 font-medium text-heading">
                                {{ learner.name }}
                            </td>
                            <td class="px-4 py-3 text-body">
                                {{ learner.email }}
                            </td>
                            <td class="px-4 py-3 text-body">
                                {{ learner.sessions_completed }}
                            </td>
                            <td class="px-4 py-3 text-body">
                                {{ formatDate(learner.last_practice_at) }}
                            </td>
                            <td class="px-4 py-3 capitalize text-body">
                                {{ levelLabel[learner.level_estimated] ?? learner.level_estimated }}
                            </td>
                            <td class="px-4 py-3 text-heading">
                                {{ learner.accuracy_pct }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
</template>
