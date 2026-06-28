<script setup lang="ts">
import WorldMap from "@/Components/WorldMap.vue";
import PowerChip from "@/Components/PowerChip.vue";
import PowerIcon from "@/Components/PowerIcon.vue";
import { confirmWorldUnlock } from "@/utils/confirmWorldUnlock";
import type {
    WorldAccessState,
    WorldChallengeType,
    WorldInfo,
    WorldLevel,
    WorldProgressState,
} from "@/types/world";
import type { PageProps } from "@/types/auth";
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    Globe,
    Lock,
    Sparkles,
} from "@lucide/vue";
import { router, usePage } from "@inertiajs/vue3";
import { computed, ref, toRef, watch } from "vue";
import { POWER_UNIT, powerBalanceLabel, powerCostLabel } from "@/utils/powerLabels";

const props = defineProps<{
    worlds: WorldInfo[];
    levels: WorldLevel[];
    world_access: WorldAccessState;
    progress: WorldProgressState;
}>();

type Step = "gate" | "map" | "challenge";

const page = usePage<{ auth: PageProps["auth"]; game: PageProps["game"] }>();

const step = ref<Step>(props.world_access.unlocked ? "map" : "gate");
const selectedLevelId = ref<number | null>(null);
const unlocking = ref(false);
const completing = ref(false);

const access = toRef(props, "world_access");
const progressState = toRef(props, "progress");

const tokens = computed(() => page.props.auth.user?.tokens ?? 0);
const unlockCost = computed(() => access.value.unlock_cost);
const sublevelReward = computed(
    () => page.props.game.sublevel_complete_reward,
);
const tierResetCost = computed(() => page.props.game.tier_reset_cost);

const worldNames = computed(() =>
    Object.fromEntries(props.worlds.map((world) => [world.tier, world.name])),
);

const completedSet = computed(() => new Set(progressState.value.completed));

const selectedLevel = computed(
    () => props.levels.find((level) => level.id === selectedLevelId.value) ?? null,
);

const completedCount = computed(() => progressState.value.completed.length);

const progressPercent = computed(() =>
    Math.round((completedCount.value / 15) * 100),
);

const typeLabels: Record<WorldChallengeType, string> = {
    roleplay: "Roleplay",
    writing: "Escritura",
    dialogue: "Diálogo",
    feedback: "Feedback",
    presentation: "Presentación",
};

watch(
    () => props.world_access.unlocked,
    (unlocked) => {
        if (unlocked) {
            step.value = "map";
        }
    },
);

function isUnlocked(id: number): boolean {
    return progressState.value.unlocked.includes(id);
}

function isCompleted(id: number): boolean {
    return completedSet.value.has(id);
}

function isPending(_id: number): boolean {
    return false;
}

function isLockedOut(_id: number): boolean {
    return false;
}

async function handleUnlock(): Promise<void> {
    const confirmed = await confirmWorldUnlock({
        cost: unlockCost.value,
        tokens: tokens.value,
    });

    if (!confirmed) {
        return;
    }

    unlocking.value = true;

    router.post(
        "/world/unlock",
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                unlocking.value = false;
            },
        },
    );
}

function openLevel(id: number): void {
    if (!isUnlocked(id) || isCompleted(id)) {
        return;
    }

    selectedLevelId.value = id;
    step.value = "challenge";
}

function viewCompletedLevel(id: number): void {
    selectedLevelId.value = id;
    step.value = "challenge";
}

function backToMap(): void {
    step.value = "map";
    selectedLevelId.value = null;
}

function completeChallenge(): void {
    const level = selectedLevel.value;

    if (!level || isCompleted(level.id)) {
        backToMap();

        return;
    }

    completing.value = true;

    router.post(
        `/world/levels/${level.id}/complete`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                completing.value = false;
                backToMap();
            },
        },
    );
}
</script>

<template>
    <div class="space-y-6">
        <div
            v-if="step === 'gate'"
            class="mx-auto max-w-lg"
        >
            <div class="surface-card overflow-hidden">
                <div class="border-b border-gray-100 bg-indigo-50 px-6 py-8 text-center dark:border-gray-800 dark:bg-indigo-950/40">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-600 text-white dark:bg-indigo-500">
                        <Globe class="h-8 w-8" />
                    </div>
                    <div class="mb-2 inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                        <PowerIcon size-class="h-3 w-3" />
                        Supernivel
                    </div>
                    <h1 class="text-2xl font-bold text-heading">
                        Mundo
                    </h1>
                    <p class="mt-2 text-sm text-muted">
                        Desbloquea este mapa con {{ POWER_UNIT }}. Roleplay, demos, entrevistas
                        y comunicación técnica avanzada — independiente de Práctica y Tracks.
                    </p>
                </div>

                <div class="space-y-4 p-6">
                    <ul class="space-y-2 text-sm text-body">
                        <li
                            v-for="world in worlds"
                            :key="world.tier"
                            class="flex gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60"
                        >
                            <Sparkles class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500" />
                            <span>
                                <strong class="text-heading">{{ world.name }}</strong>
                                — {{ world.description }}
                            </span>
                        </li>
                    </ul>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:bg-amber-950/40">
                        <p class="text-center text-sm font-medium text-amber-900 dark:text-amber-200">
                            Coste de desbloqueo
                        </p>
                        <p class="mt-1 text-center text-2xl font-bold text-amber-700 dark:text-amber-300">
                            {{ powerCostLabel(unlockCost) }}
                        </p>
                        <p class="mt-1 text-center text-xs text-amber-800/80 dark:text-amber-300/80">
                            Tu saldo: {{ powerBalanceLabel(tokens) }} · acceso permanente
                        </p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800/60">
                        <p class="text-sm font-medium text-heading">
                            Gana {{ POWER_UNIT }} gratis en Práctica y Tracks
                        </p>
                        <ul class="mt-2 space-y-1.5 text-xs text-body">
                            <li class="flex gap-2">
                                <PowerIcon
                                    class="mt-0.5 shrink-0"
                                    size-class="h-3.5 w-3.5"
                                />
                                <span>
                                    <PowerChip
                                        class="!inline-flex align-middle"
                                        :amount="sublevelReward"
                                        sign="+"
                                        size="md"
                                    />
                                    por cada subnivel que completes
                                </span>
                            </li>
                            <li class="flex gap-2">
                                <PowerIcon
                                    class="mt-0.5 shrink-0"
                                    size-class="h-3.5 w-3.5"
                                />
                                <span>
                                    <strong class="text-heading">−{{ tierResetCost }}</strong>
                                    {{ POWER_UNIT }} al reiniciar un módulo completado (máx. 2 veces)
                                </span>
                            </li>
                        </ul>
                    </div>

                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="unlocking || tokens < unlockCost"
                        @click="handleUnlock"
                    >
                        <Lock class="h-4 w-4" />
                        {{
                            tokens >= unlockCost
                                ? `Desbloquear por ${powerCostLabel(unlockCost)}`
                                : `Necesitas ${powerCostLabel(unlockCost - tokens)} más`
                        }}
                    </button>
                </div>
            </div>
        </div>

        <template v-else>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="mb-1 inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                        <PowerIcon size-class="h-3 w-3" />
                        Supernivel
                    </div>
                    <h1 class="text-2xl font-bold text-heading">
                        Mundo
                    </h1>
                    <p class="mt-1 text-sm text-muted">
                        {{ completedCount }}/15 desafíos completados · mapa desbloqueado con {{ POWER_UNIT }}.
                    </p>
                </div>
                <button
                    v-if="step === 'challenge'"
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-body hover:text-heading"
                    @click="backToMap"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver al mapa
                </button>
            </div>

            <div
                v-if="step === 'map'"
                class="space-y-4"
            >
                <div class="surface-card p-4 sm:p-5">
                    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Progreso
                            </p>
                            <p class="mt-0.5 text-sm font-bold text-heading">
                                {{ completedCount }}/15 · {{ progressPercent }}%
                            </p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Poder
                            </p>
                            <p class="mt-0.5 inline-flex items-center gap-1 text-sm font-bold text-orange-700 dark:text-orange-300">
                                <PowerIcon size-class="h-3.5 w-3.5" />
                                {{ tokens }}
                            </p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-gray-50 px-3 py-2 sm:col-span-1 dark:bg-gray-800/60">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Acceso
                            </p>
                            <p class="mt-0.5 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                Desbloqueado
                            </p>
                        </div>
                    </div>

                    <WorldMap
                        :world-names="worldNames"
                        :is-unlocked="isUnlocked"
                        :is-completed="isCompleted"
                        :is-pending="isPending"
                        :is-locked-out="isLockedOut"
                        :selected-id="selectedLevelId"
                        @select="openLevel"
                        @view-completed="viewCompletedLevel"
                    />
                </div>

                <p class="text-center text-xs text-muted">
                    Completa cada desafío para desbloquear el siguiente nodo del camino.
                </p>
            </div>

            <div
                v-else-if="step === 'challenge' && selectedLevel"
                class="mx-auto max-w-2xl space-y-4"
            >
                <div class="surface-card p-6">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                            {{ worldNames[selectedLevel.tier] }} · Subnivel {{ selectedLevel.phase }}
                        </span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-body dark:bg-gray-800">
                            {{ typeLabels[selectedLevel.type] }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-muted dark:bg-gray-800">
                            <Clock class="h-3 w-3" />
                            ~{{ selectedLevel.duration_minutes }} min
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-heading">
                        {{ selectedLevel.title }}
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/60">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                                Escenario
                            </p>
                            <p class="mt-1 text-sm text-body">
                                {{ selectedLevel.scenario }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                                Objetivo
                            </p>
                            <p class="mt-1 text-sm text-body">
                                {{ selectedLevel.objective }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="isCompleted(selectedLevel.id)"
                        class="mt-5 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200"
                    >
                        <CheckCircle2 class="h-5 w-5 shrink-0" />
                        Ya completaste este desafío.
                    </div>

                    <button
                        v-else
                        type="button"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                        :disabled="completing"
                        @click="completeChallenge"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        Marcar desafío completado
                    </button>

                    <p
                        v-if="!isCompleted(selectedLevel.id)"
                        class="mt-2 text-center text-xs text-muted"
                    >
                        Practica el escenario y confirma cuando lo hayas completado.
                    </p>
                </div>
            </div>
        </template>
    </div>
</template>
