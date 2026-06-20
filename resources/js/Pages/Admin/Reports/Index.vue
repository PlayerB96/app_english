<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import type { AdminTrackReport } from "@/types/admin";

defineProps<{
    reports: AdminTrackReport[];
}>();
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-heading">
                    Reportes por track
                </h1>
                <p class="mt-1 text-muted">
                    Métricas agregadas por track desde sesiones y respuestas reales.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <article
                    v-for="report in reports"
                    :key="report.track_id"
                    class="surface-card p-5"
                >
                    <h2 class="text-lg font-semibold text-heading">
                        {{ report.track_name }}
                    </h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Sesiones</dt>
                            <dd class="font-medium text-heading">
                                {{ report.sessions_count }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Precisión media</dt>
                            <dd class="font-medium text-heading">
                                {{ report.avg_accuracy }}%
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Learners activos</dt>
                            <dd class="font-medium text-heading">
                                {{ report.active_learners }}
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>
        </div>
    </AppLayout>
</template>
