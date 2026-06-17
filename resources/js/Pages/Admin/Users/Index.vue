<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
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
    <AppLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Usuarios learners
                </h1>
                <p class="mt-1 text-gray-500">
                    Listado simulado de developers en la plataforma.
                </p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nombre</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Sesiones</th>
                            <th class="px-4 py-3 font-medium">Última práctica</th>
                            <th class="px-4 py-3 font-medium">Nivel</th>
                            <th class="px-4 py-3 font-medium">Precisión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="learner in learners"
                            :key="learner.id"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ learner.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ learner.email }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ learner.sessions_completed }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ formatDate(learner.last_practice_at) }}
                            </td>
                            <td class="px-4 py-3 capitalize text-gray-600">
                                {{ levelLabel[learner.level_estimated] ?? learner.level_estimated }}
                            </td>
                            <td class="px-4 py-3 text-gray-900">
                                {{ learner.accuracy_pct }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
