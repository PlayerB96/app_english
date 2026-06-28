<script setup lang="ts">
import type { AdminTrackRow } from "@/types/admin";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
    tracks: AdminTrackRow[];
}>();

const editingId = ref<number | null>(null);
const draftName = ref("");
const draftDescription = ref("");
const saving = ref(false);

const difficultyLabel: Record<string, string> = {
    beginner: "Principiante",
    intermediate: "Intermedio",
    advanced: "Avanzado",
};

function startEdit(track: AdminTrackRow): void {
    editingId.value = track.id;
    draftName.value = track.name;
    draftDescription.value = track.description ?? "";
}

function saveEdit(trackId: number): void {
    saving.value = true;

    router.patch(
        `/admin/tracks/${trackId}`,
        {
            name: draftName.value.trim(),
            description: draftDescription.value.trim() || null,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                saving.value = false;
                editingId.value = null;
            },
        },
    );
}

function toggleActive(track: AdminTrackRow): void {
    router.patch(
        `/admin/tracks/${track.id}`,
        { is_active: !track.is_active },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-heading">
                    Gestión de tracks
                </h1>
                <p class="mt-1 text-muted">
                    Tracks de aprendizaje en PostgreSQL. Los cambios se guardan al instante.
                </p>
            </div>

            <div class="space-y-4">
                <article
                    v-for="track in tracks"
                    :key="track.id"
                    class="surface-card p-5"
                >
                    <div
                        v-if="editingId === track.id"
                        class="space-y-3"
                    >
                        <input
                            v-model="draftName"
                            type="text"
                            class="input-field"
                        />
                        <textarea
                            v-model="draftDescription"
                            rows="3"
                            class="input-field"
                        />
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white disabled:opacity-50"
                                :disabled="saving"
                                @click="saveEdit(track.id)"
                            >
                                Guardar
                            </button>
                            <button
                                type="button"
                                class="input-field text-body"
                                :disabled="saving"
                                @click="editingId = null"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-semibold text-heading">
                                    {{ track.name }}
                                </h2>
                                <span class="text-xs text-muted">
                                    {{ track.slug }}
                                </span>
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-0.5 text-xs capitalize text-body dark:bg-gray-800"
                                >
                                    {{ difficultyLabel[track.difficulty] ?? track.difficulty }}
                                </span>
                            </div>
                            <p class="text-sm text-body">
                                {{ track.description }}
                            </p>
                            <p class="mt-2 text-xs text-muted">
                                {{ track.session_count }} sesiones completadas
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-body hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                @click="startEdit(track)"
                            >
                                Editar
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-3 py-2 text-sm font-medium"
                                :class="
                                    track.is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-gray-100 text-body dark:bg-gray-800'
                                "
                                @click="toggleActive(track)"
                            >
                                {{ track.is_active ? "Activo" : "Inactivo" }}
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
</template>
