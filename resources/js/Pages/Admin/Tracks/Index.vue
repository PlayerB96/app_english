<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import type { AdminTrackRow } from "@/types/admin";
import { ref } from "vue";

const props = defineProps<{
    tracks: AdminTrackRow[];
}>();

const localTracks = ref<AdminTrackRow[]>([...props.tracks]);
const editingId = ref<number | null>(null);
const draftName = ref("");
const draftDescription = ref("");

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

function saveEdit(): void {
    const track = localTracks.value.find((item) => item.id === editingId.value);

    if (!track) {
        return;
    }

    track.name = draftName.value.trim() || track.name;
    track.description = draftDescription.value.trim() || null;
    editingId.value = null;
}

function toggleActive(track: AdminTrackRow): void {
    track.is_active = !track.is_active;
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Gestión de tracks
                </h1>
                <p class="mt-1 text-gray-500">
                    CRUD simulado en memoria (se reinicia al recargar).
                </p>
            </div>

            <div class="space-y-4">
                <article
                    v-for="track in localTracks"
                    :key="track.id"
                    class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
                >
                    <div
                        v-if="editingId === track.id"
                        class="space-y-3"
                    >
                        <input
                            v-model="draftName"
                            type="text"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        />
                        <textarea
                            v-model="draftDescription"
                            rows="3"
                            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        />
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white"
                                @click="saveEdit"
                            >
                                Guardar (mock)
                            </button>
                            <button
                                type="button"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600"
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
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ track.name }}
                                </h2>
                                <span class="text-xs text-gray-400">
                                    {{ track.slug }}
                                </span>
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-0.5 text-xs capitalize text-gray-600"
                                >
                                    {{ difficultyLabel[track.difficulty] ?? track.difficulty }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ track.description }}
                            </p>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ track.session_count }} sesiones
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button
                                type="button"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
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
                                        : 'bg-gray-100 text-gray-600'
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
    </AppLayout>
</template>
