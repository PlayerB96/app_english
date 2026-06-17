<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import type { AdminTrackReport } from "@/types/admin";

defineProps<{
    reports: AdminTrackReport[];
}>();
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Reportes por track
                </h1>
                <p class="mt-1 text-gray-500">
                    Métricas agregadas simuladas para supervisión pedagógica.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <article
                    v-for="report in reports"
                    :key="report.track_id"
                    class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ report.track_name }}
                    </h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Sesiones</dt>
                            <dd class="font-medium text-gray-900">
                                {{ report.sessions_count }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Precisión media</dt>
                            <dd class="font-medium text-gray-900">
                                {{ report.avg_accuracy }}%
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Learners activos</dt>
                            <dd class="font-medium text-gray-900">
                                {{ report.active_learners }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>
        </div>
    </AppLayout>
</template>
