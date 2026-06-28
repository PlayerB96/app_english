<script setup lang="ts">
import WorldHeaderBadge from "@/Components/WorldHeaderBadge.vue";
import WorldIcon from "@/Components/WorldIcon.vue";
import WorldMap from "@/Components/WorldMap.vue";
import WorldMapDetailPanel from "@/Components/WorldMapDetailPanel.vue";
import PowerChip from "@/Components/PowerChip.vue";
import PowerIcon from "@/Components/PowerIcon.vue";
import { confirmWorldUnlock } from "@/utils/confirmWorldUnlock";
import type {
    WorldAccessState,
    WorldChallengeType,
    WorldInfo,
    WorldLevel,
    WorldProgressState,
    WorldTierSlug,
    WorldZone,
} from "@/types/world";
import { WORLD_TOTAL_LEVELS } from "@/types/world";
import type { PageProps } from "@/types/auth";
import {
    ArrowLeft,
    CheckCircle2,
    Clock,
    Hourglass,
    Lock,
} from "@lucide/vue";
import { computed, ref, toRef, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { resolveNodeVisualStatus } from "@/utils/worldMapLayout";
import { POWER_UNIT, powerCostLabel } from "@/utils/powerLabels";

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
const mapFocusedLevelId = ref<number>(1);
const previewWorldTier = ref<WorldTierSlug | null>(
    props.world_access.unlocked
        ? null
        : (props.worlds.find((world) => world.status === "available")?.tier ?? null),
);
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

const activeWorld = computed(
    () => props.worlds.find((world) => world.status === "available") ?? props.worlds[0],
);

const previewWorld = computed(
    () => props.worlds.find((world) => world.tier === previewWorldTier.value) ?? null,
);

function selectPreviewWorld(tier: WorldTierSlug): void {
    previewWorldTier.value = tier;
}

function previewLocked(_id: number): boolean {
    return false;
}

function worldListButtonClass(world: WorldInfo): string {
    const isSelected = previewWorldTier.value === world.tier;
    const isSoon = world.status === "coming_soon";

    if (isSoon) {
        return isSelected
            ? "border border-dashed border-slate-400 bg-slate-100/90 ring-2 ring-slate-300 dark:border-slate-600 dark:bg-slate-800/80 dark:ring-slate-600"
            : "border border-dashed border-slate-300 bg-slate-50/60 opacity-80 hover:bg-slate-100/80 dark:border-slate-700 dark:bg-slate-900/40 dark:hover:bg-slate-800/60";
    }

    return isSelected
        ? "bg-indigo-50 ring-2 ring-indigo-200 dark:bg-indigo-950/40 dark:ring-indigo-800"
        : "bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/60 dark:hover:bg-gray-800";
}

const selectedZone = computed((): WorldZone | null => {
    const level = selectedLevel.value;

    if (!level) {
        return null;
    }

    const world = props.worlds.find((item) => item.tier === level.tier);

    if (!world) {
        return null;
    }

    if (level.zone === "final-boss") {
        return null;
    }

    return world.zones.find((zone) => zone.slug === level.zone) ?? null;
});

const selectedZoneLabel = computed(() => {
    const level = selectedLevel.value;

    if (!level) {
        return null;
    }

    if (level.is_boss) {
        return "Final Boss";
    }

    const zone = selectedZone.value;

    return zone?.name ?? null;
});

const completedSet = computed(() => new Set(progressState.value.completed));

const selectedLevel = computed(
    () => props.levels.find((level) => level.id === selectedLevelId.value) ?? null,
);

const mapFocusedLevel = computed(
    () => props.levels.find((level) => level.id === mapFocusedLevelId.value) ?? null,
);

function zoneForLevel(level: WorldLevel | null): WorldZone | null {
    if (!level || level.zone === "final-boss") {
        return null;
    }

    const world = props.worlds.find((item) => item.tier === level.tier);

    return world?.zones.find((zone) => zone.slug === level.zone) ?? null;
}

const mapFocusedZone = computed(() => zoneForLevel(mapFocusedLevel.value));

function focusMapLevel(id: number): void {
    mapFocusedLevelId.value = id;
}

const completedCount = computed(() => progressState.value.completed.length);

const progressPercent = computed(() =>
    Math.round((completedCount.value / WORLD_TOTAL_LEVELS) * 100),
);

const typeLabels: Record<WorldChallengeType, string> = {
    quest: "Misión NPC",
    command_lab: "Lab de comandos",
    puzzle: "Puzzle",
    boss_interview: "Entrevista final",
};

watch(
    () => props.world_access.unlocked,
    (unlocked) => {
        if (unlocked) {
            step.value = "map";
        }
    },
);

watch(
    () => progressState.value.unlocked,
    (unlocked) => {
        const playable = unlocked.find((id) => !completedSet.value.has(id));

        if (playable && mapFocusedLevelId.value === 1) {
            mapFocusedLevelId.value = playable;
        }
    },
    { immediate: true },
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

function nodeStatusFor(id: number, preview = false): "locked" | "current" | "completed" | "pending" | "lockout" | "preview" {
    if (preview) {
        return "preview";
    }

    return resolveNodeVisualStatus(
        id,
        true,
        isCompleted,
        isLockedOut,
        isPending,
        isUnlocked,
    );
}

function startFocusedLevel(): void {
    openLevel(mapFocusedLevelId.value);
}

function viewFocusedCompleted(): void {
    viewCompletedLevel(mapFocusedLevelId.value);
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
            class="w-full"
        >
            <div class="surface-card overflow-hidden">
                <div class="border-b border-gray-100 bg-indigo-50 px-5 py-4 dark:border-gray-800 dark:bg-indigo-950/40 md:flex md:items-center md:gap-5 md:px-6 md:py-5 lg:px-8">
                    <WorldHeaderBadge class="mx-auto mb-3 md:mx-0 md:mb-0" />
                    <div class="min-w-0 flex-1 text-center md:text-left">
                        <div class="mb-1 inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                            <PowerIcon size-class="h-3 w-3" />
                            Superniveles
                        </div>
                        <h1 class="text-xl font-bold text-heading md:text-2xl">
                            Mundos
                        </h1>
                        <p class="mt-1 text-xs text-muted md:text-sm">
                            Mapas temáticos · inglés técnico · desbloqueo con {{ POWER_UNIT }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center justify-center gap-1.5 md:justify-start">
                            <span
                                v-for="world in worlds"
                                :key="world.tier"
                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-medium md:text-xs"
                                :class="
                                    world.status === 'coming_soon'
                                        ? 'border-dashed border-slate-300 bg-slate-50/80 text-slate-500 dark:border-slate-600 dark:bg-slate-900/50 dark:text-slate-400'
                                        : 'border-indigo-100 bg-white/70 text-body dark:border-indigo-900/60 dark:bg-indigo-950/30'
                                "
                            >
                                <WorldIcon
                                    :tier="world.tier"
                                    size-class="h-3 w-3"
                                />
                                {{ world.name }}
                                <span
                                    v-if="world.status === 'coming_soon'"
                                    class="text-[9px] uppercase opacity-70"
                                >
                                    · pronto
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-6 md:grid-cols-[minmax(16rem,20rem)_1fr] md:items-start md:p-8 lg:grid-cols-[minmax(17rem,21rem)_1fr] lg:gap-8 xl:grid-cols-[minmax(18rem,22rem)_1fr] xl:gap-10 xl:p-10 2xl:grid-cols-[minmax(18rem,20rem)_minmax(0,1fr)] 2xl:gap-12 2xl:p-12">
                    <div class="space-y-4 md:sticky md:top-6 lg:top-8">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted md:text-sm">
                            Elige un mundo
                        </p>
                        <ul class="space-y-1.5 text-sm text-body">
                            <li
                                v-for="world in worlds"
                                :key="world.tier"
                            >
                                <button
                                    type="button"
                                    class="flex w-full gap-2 rounded-lg px-3 text-left transition-colors"
                                    :class="[
                                        worldListButtonClass(world),
                                        world.status === 'coming_soon' ? 'py-2' : 'py-2.5 md:py-3',
                                    ]"
                                    @click="selectPreviewWorld(world.tier)"
                                >
                                    <span
                                        class="shrink-0"
                                        :class="world.status === 'coming_soon' ? 'opacity-45' : ''"
                                    >
                                        <WorldIcon
                                            :tier="world.tier"
                                            size-class="h-4 w-4"
                                        />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-1.5">
                                            <strong
                                                class="text-heading"
                                                :class="world.status === 'coming_soon' ? 'text-slate-600 dark:text-slate-400' : ''"
                                            >
                                                {{ world.name }}
                                            </strong>
                                            <span
                                                v-if="world.status === 'coming_soon'"
                                                class="inline-flex items-center gap-0.5 rounded bg-slate-200/80 px-1.5 py-px text-[9px] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-700/80 dark:text-slate-400"
                                            >
                                                <Hourglass class="h-2.5 w-2.5" />
                                                Próximamente
                                            </span>
                                        </span>
                                        <template v-if="world.status === 'available'">
                                            <span class="block text-xs text-muted">{{ world.subtitle }}</span>
                                            <span class="mt-0.5 block line-clamp-2 md:line-clamp-none">{{ world.description }}</span>
                                        </template>
                                    </span>
                                </button>
                            </li>
                        </ul>

                        <div class="rounded-xl border-2 border-orange-300 bg-gradient-to-br from-orange-50 via-amber-50 to-orange-100 px-4 py-3 shadow-sm dark:border-orange-800 dark:from-orange-950/50 dark:via-amber-950/40 dark:to-orange-950/30">
                            <p class="flex items-center gap-1.5 text-sm font-bold text-orange-900 dark:text-orange-200">
                                <PowerIcon size-class="h-4 w-4" />
                                ¿Cómo ganas {{ POWER_UNIT }}?
                            </p>
                            <p class="mt-0.5 text-[11px] text-orange-800/90 dark:text-orange-300/90">
                                Gratis en Práctica y Tracks — necesitas {{ powerCostLabel(unlockCost) }} para desbloquear un mundo.
                            </p>
                            <ul class="mt-2 space-y-1.5 text-xs text-body md:text-sm">
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
                    </div>

                    <div
                        v-if="previewWorld"
                        class="min-w-0 space-y-4 rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-800/40 md:p-5 lg:p-6 xl:p-7"
                    >
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted md:text-sm">
                                Vista previa
                            </p>
                            <h2 class="mt-1 flex items-center gap-2 text-lg font-bold text-heading md:text-xl lg:text-2xl">
                                <WorldIcon
                                    :tier="previewWorld.tier"
                                    size-class="h-5 w-5 md:h-6 md:w-6"
                                />
                                {{ previewWorld.name }}
                            </h2>
                            <p class="text-xs text-muted md:text-sm">
                                {{ previewWorld.subtitle }}
                            </p>
                            <p class="mt-2 text-sm text-body md:text-base">
                                {{ previewWorld.description }}
                            </p>
                        </div>

                        <div
                            v-if="previewWorld.status === 'available'"
                            class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-white/90 px-3 py-2 shadow-sm dark:border-gray-700 dark:bg-gray-900/70"
                        >
                            <span class="inline-flex items-center gap-1 text-xs text-body">
                                <Lock class="h-3.5 w-3.5 shrink-0 text-indigo-500 dark:text-indigo-400" />
                                <span class="font-medium text-heading">{{ WORLD_TOTAL_LEVELS }} niveles</span>
                                <span class="hidden text-muted sm:inline">· acceso permanente</span>
                            </span>
                            <span class="hidden h-3.5 w-px shrink-0 bg-gray-200 sm:block dark:bg-gray-700" />
                            <span class="inline-flex items-center gap-1 text-xs">
                                <PowerIcon size-class="h-3 w-3" />
                                <span class="text-muted">Saldo</span>
                                <strong
                                    class="tabular-nums"
                                    :class="tokens >= unlockCost ? 'text-heading' : 'text-amber-700 dark:text-amber-400'"
                                >
                                    {{ tokens }}
                                </strong>
                            </span>
                            <PowerChip
                                class="!inline-flex shrink-0"
                                :amount="unlockCost"
                                sign="−"
                                size="md"
                            />
                            <button
                                type="button"
                                class="ml-auto inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-55 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                :disabled="unlocking || tokens < unlockCost"
                                @click="handleUnlock"
                            >
                                <Lock class="h-3 w-3" />
                                {{
                                    tokens >= unlockCost
                                        ? `Desbloquear · ${unlockCost}`
                                        : `Faltan ${unlockCost - tokens}`
                                }}
                            </button>
                        </div>

                        <ul
                            v-if="previewWorld.zones.length"
                            class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4"
                        >
                            <li
                                v-for="zone in previewWorld.zones"
                                :key="zone.slug"
                                class="rounded-lg bg-white px-3 py-2 dark:bg-gray-900/60"
                            >
                                <p class="flex items-center gap-1.5 text-xs font-semibold text-heading md:text-sm">
                                    <WorldIcon
                                        :zone="zone.slug"
                                        size-class="h-3.5 w-3.5"
                                    />
                                    {{ zone.name }}
                                </p>
                                <p class="text-[10px] text-muted md:text-xs">
                                    Niveles {{ zone.level_range }}
                                </p>
                                <p
                                    v-if="zone.commands?.length"
                                    class="mt-1 font-mono text-[10px] text-body md:text-xs"
                                >
                                    {{ zone.commands.join(' · ') }}
                                </p>
                            </li>
                        </ul>

                        <div
                            v-if="previewWorld.boss"
                            class="rounded-lg border border-violet-200 bg-violet-50/60 px-3 py-2 dark:border-violet-900 dark:bg-violet-950/30 md:px-4 md:py-3"
                        >
                            <p class="flex items-center gap-1.5 text-xs font-semibold text-violet-800 dark:text-violet-300 md:text-sm">
                                <WorldIcon
                                    boss
                                    size-class="h-3.5 w-3.5"
                                />
                                {{ previewWorld.boss.title }}
                            </p>
                            <p class="mt-0.5 text-xs text-body md:text-sm">
                                {{ previewWorld.boss.description }}
                            </p>
                        </div>

                        <div
                            v-if="previewWorld.status === 'available'"
                            class="space-y-3"
                        >
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted md:text-sm">
                                    Mapa del recorrido
                                </p>
                                <p class="mt-1 text-xs text-muted md:text-sm">
                                    {{ WORLD_TOTAL_LEVELS }} niveles en 4 zonas · empiezas abajo (Welcome Village) y avanzas hasta el boss final.
                                </p>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60">
                                <WorldMap
                                    :world-names="worldNames"
                                    :focus-tier="previewWorld.tier"
                                    :is-unlocked="previewLocked"
                                    :is-completed="previewLocked"
                                    overview-mode
                                />
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50/80 px-4 py-8 text-center dark:border-slate-600 dark:bg-slate-900/40"
                        >
                            <Hourglass class="h-8 w-8 text-slate-400 dark:text-slate-500" />
                            <p class="text-sm font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                Próximamente
                            </p>
                            <p class="max-w-xs text-xs text-muted">
                                Este mundo aún no está disponible. Sigue avanzando en Linux Kingdom.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template v-else>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="mb-1 inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                        <PowerIcon size-class="h-3 w-3" />
                        Superniveles
                    </div>
                    <h1 class="text-xl font-bold text-heading md:text-2xl">
                        Mundos
                    </h1>
                    <p class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-muted md:text-sm">
                        <span class="inline-flex items-center gap-1">
                            <WorldIcon
                                v-if="activeWorld"
                                :tier="activeWorld.tier"
                                size-class="h-3.5 w-3.5"
                            />
                            {{ activeWorld?.name }}
                        </span>
                        <span>·</span>
                        <span>{{ completedCount }}/{{ WORLD_TOTAL_LEVELS }}</span>
                        <span>·</span>
                        <span>{{ progressPercent }}%</span>
                    </p>
                </div>
                <button
                    v-if="step === 'challenge'"
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1 self-start text-sm font-medium text-body hover:text-heading"
                    @click="backToMap"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Volver al mapa
                </button>
            </div>

            <div
                v-if="step === 'map'"
                class="grid gap-4 lg:grid-cols-[minmax(13rem,15rem)_minmax(0,1fr)_minmax(16rem,20rem)] lg:items-start lg:gap-6 xl:grid-cols-[minmax(14rem,16rem)_minmax(0,1fr)_minmax(17rem,22rem)] xl:gap-8 2xl:gap-10"
            >
                <aside class="space-y-3 lg:sticky lg:top-6 xl:top-8">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60 lg:rounded-xl lg:px-4 lg:py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted md:text-xs">
                                Progreso
                            </p>
                            <p class="mt-0.5 text-sm font-bold text-heading md:text-base">
                                {{ completedCount }}/{{ WORLD_TOTAL_LEVELS }} · {{ progressPercent }}%
                            </p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60 lg:rounded-xl lg:px-4 lg:py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted md:text-xs">
                                Poder
                            </p>
                            <p class="mt-0.5 inline-flex items-center gap-1 text-sm font-bold text-orange-700 dark:text-orange-300 md:text-base">
                                <PowerIcon size-class="h-3.5 w-3.5" />
                                {{ tokens }}
                            </p>
                        </div>
                        <div class="col-span-2 rounded-lg bg-gray-50 px-3 py-2 sm:col-span-1 lg:col-span-1 dark:bg-gray-800/60 lg:rounded-xl lg:px-4 lg:py-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted md:text-xs">
                                Acceso
                            </p>
                            <p class="mt-0.5 text-sm font-bold text-emerald-600 dark:text-emerald-400 md:text-base">
                                Desbloqueado
                            </p>
                        </div>
                    </div>
                </aside>

                <div class="min-w-0 space-y-3 lg:col-span-1">
                    <div class="surface-card overflow-hidden p-3 sm:p-4 lg:p-5">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 px-1">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted md:text-xs">
                                    Mapa de aventura
                                </p>
                                <p class="text-xs text-muted md:text-sm">
                                    Recorre el camino nivel a nivel · inglés técnico y comandos Linux
                                </p>
                            </div>
                        </div>
                        <WorldMap
                            :world-names="worldNames"
                            focus-tier="basico"
                            :selected-id="mapFocusedLevelId"
                            :is-unlocked="isUnlocked"
                            :is-completed="isCompleted"
                            :is-pending="isPending"
                            :is-locked-out="isLockedOut"
                            @select="focusMapLevel"
                        />
                    </div>

                    <p class="text-center text-xs text-muted md:text-sm">
                        Selecciona un nodo para ver qué aprenderás. Inicia el desafío desde el panel de detalle.
                    </p>
                </div>

                <WorldMapDetailPanel
                    class="lg:sticky lg:top-6 xl:top-8"
                    :level="mapFocusedLevel"
                    :world="activeWorld"
                    :zone="mapFocusedZone"
                    :world-names="worldNames"
                    :status="nodeStatusFor(mapFocusedLevelId)"
                    :sublevel-reward="sublevelReward"
                    @start="startFocusedLevel"
                    @view-completed="viewFocusedCompleted"
                />
            </div>

            <div
                v-else-if="step === 'challenge' && selectedLevel"
                class="mx-auto w-full max-w-2xl space-y-4 lg:max-w-none lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(17rem,20rem)] lg:items-start lg:gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)] xl:gap-8 2xl:grid-cols-[minmax(0,1fr)_minmax(20rem,24rem)] 2xl:gap-10"
            >
                <div class="surface-card p-5 sm:p-6 lg:p-7">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                            <WorldIcon
                                :tier="selectedLevel.tier"
                                size-class="h-3 w-3"
                            />
                            {{ worldNames[selectedLevel.tier] }}
                            <template v-if="selectedZoneLabel">
                                <span class="opacity-50">·</span>
                                <WorldIcon
                                    v-if="selectedLevel.is_boss"
                                    boss
                                    size-class="h-3 w-3"
                                />
                                <WorldIcon
                                    v-else-if="selectedLevel.zone"
                                    :zone="selectedLevel.zone"
                                    size-class="h-3 w-3"
                                />
                                {{ selectedZoneLabel }}
                            </template>
                        </span>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-body dark:bg-gray-800">
                            Nivel {{ selectedLevel.id }} · {{ typeLabels[selectedLevel.type] }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-muted dark:bg-gray-800">
                            <Clock class="h-3 w-3" />
                            ~{{ selectedLevel.duration_minutes }} min
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-heading md:text-2xl">
                        {{ selectedLevel.title }}
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/60 md:p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">
                                Escenario
                            </p>
                            <p class="mt-1 text-sm text-body md:text-base">
                                {{ selectedLevel.scenario }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-4 dark:border-indigo-900 dark:bg-indigo-950/30 md:p-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                                Objetivo
                            </p>
                            <p class="mt-1 text-sm text-body md:text-base">
                                {{ selectedLevel.objective }}
                            </p>
                        </div>
                        <div
                            v-if="selectedLevel.gameplay"
                            class="rounded-lg border border-amber-100 bg-amber-50/60 p-4 dark:border-amber-900 dark:bg-amber-950/30 md:p-5 lg:hidden"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                                Gameplay
                            </p>
                            <p class="mt-1 text-sm text-body md:text-base">
                                {{ selectedLevel.gameplay }}
                            </p>
                        </div>
                        <div
                            v-if="selectedZone"
                            class="grid gap-3 sm:grid-cols-3 lg:hidden"
                        >
                            <div
                                v-if="selectedZone.curriculum?.length"
                                class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                            >
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                    Temario
                                </p>
                                <ul class="mt-1 space-y-0.5 text-xs text-body">
                                    <li
                                        v-for="item in selectedZone.curriculum"
                                        :key="item"
                                    >
                                        {{ item }}
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-if="selectedZone.commands?.length"
                                class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                            >
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                    Comandos
                                </p>
                                <p class="mt-1 font-mono text-xs text-body">
                                    {{ selectedZone.commands.join(' · ') }}
                                </p>
                            </div>
                            <div
                                v-if="selectedZone.english?.length"
                                class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                            >
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                    Inglés
                                </p>
                                <p class="mt-1 text-xs text-body">
                                    {{ selectedZone.english.join(' · ') }}
                                </p>
                            </div>
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
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60 md:w-auto md:min-w-[14rem]"
                        :disabled="completing"
                        @click="completeChallenge"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        Marcar desafío completado
                    </button>

                    <p
                        v-if="!isCompleted(selectedLevel.id)"
                        class="mt-2 text-center text-xs text-muted md:text-left md:text-sm"
                    >
                        Practica el escenario y confirma cuando lo hayas completado.
                    </p>
                </div>

                <aside
                    v-if="selectedZone || selectedLevel.gameplay"
                    class="space-y-3 lg:sticky lg:top-6 xl:top-8"
                >
                    <div
                        v-if="selectedLevel.gameplay"
                        class="hidden rounded-xl border border-amber-100 bg-amber-50/60 p-4 dark:border-amber-900 dark:bg-amber-950/30 lg:block"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                            Gameplay
                        </p>
                        <p class="mt-1 text-sm text-body">
                            {{ selectedLevel.gameplay }}
                        </p>
                    </div>

                    <div
                        v-if="selectedZone"
                        class="surface-card hidden space-y-3 p-4 lg:block lg:p-5"
                    >
                        <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-muted">
                            <WorldIcon
                                v-if="selectedLevel.is_boss"
                                boss
                                size-class="h-3.5 w-3.5"
                            />
                            <WorldIcon
                                v-else
                                :zone="selectedLevel.zone"
                                size-class="h-3.5 w-3.5"
                            />
                            Zona · {{ selectedZoneLabel }}
                        </p>
                        <div
                            v-if="selectedZone.curriculum?.length"
                            class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Temario
                            </p>
                            <ul class="mt-1 space-y-0.5 text-xs text-body">
                                <li
                                    v-for="item in selectedZone.curriculum"
                                    :key="item"
                                >
                                    {{ item }}
                                </li>
                            </ul>
                        </div>
                        <div
                            v-if="selectedZone.commands?.length"
                            class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Comandos
                            </p>
                            <p class="mt-1 font-mono text-xs text-body">
                                {{ selectedZone.commands.join(' · ') }}
                            </p>
                        </div>
                        <div
                            v-if="selectedZone.english?.length"
                            class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                        >
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                                Inglés
                            </p>
                            <p class="mt-1 text-xs text-body">
                                {{ selectedZone.english.join(' · ') }}
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </template>
    </div>
</template>
