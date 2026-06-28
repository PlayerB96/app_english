<script setup lang="ts">
import PowerChip from "@/Components/PowerChip.vue";
import WorldIcon from "@/Components/WorldIcon.vue";
import type {
    WorldChallengeType,
    WorldInfo,
    WorldLevel,
    WorldTierSlug,
    WorldZone,
} from "@/types/world";
import { CheckCircle2, Clock, Lock, Play, Star } from "@lucide/vue";
import { computed } from "vue";
import { POWER_UNIT } from "@/utils/powerLabels";

const props = withDefaults(
    defineProps<{
        level: WorldLevel | null;
        world: WorldInfo | null;
        zone: WorldZone | null;
        worldNames: Record<string, string>;
        status?: "locked" | "current" | "completed" | "pending" | "lockout" | "preview";
        previewMode?: boolean;
        sublevelReward?: number;
    }>(),
    {
        status: "preview",
        previewMode: false,
        sublevelReward: 0,
    },
);

const emit = defineEmits<{
    start: [];
    viewCompleted: [];
}>();

const typeLabels: Record<WorldChallengeType, string> = {
    quest: "Misión NPC",
    command_lab: "Lab de comandos",
    puzzle: "Puzzle",
    boss_interview: "Entrevista final",
};

const zoneLabel = computed(() => {
    if (!props.level) {
        return null;
    }

    if (props.level.is_boss) {
        return props.world?.boss?.title ?? "Final Boss";
    }

    return props.zone?.name ?? null;
});

const statusLabel = computed(() => {
    switch (props.status) {
        case "completed":
            return "Completado";
        case "current":
            return "Disponible";
        case "pending":
            return "En espera";
        case "lockout":
            return "Bloqueado temporalmente";
        case "locked":
            return "Bloqueado";
        default:
            return "Vista previa";
    }
});

const statusClass = computed(() => {
    switch (props.status) {
        case "completed":
            return "bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300";
        case "current":
            return "bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300";
        case "pending":
            return "bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300";
        case "lockout":
            return "bg-violet-100 text-violet-800 dark:bg-violet-950/50 dark:text-violet-300";
        case "locked":
            return "bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400";
        default:
            return "bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300";
    }
});

const canStart = computed(
    () =>
        !props.previewMode
        && props.level
        && (props.status === "current" || props.status === "pending" || props.status === "lockout"),
);

const showCompletedAction = computed(
    () => !props.previewMode && props.level && props.status === "completed",
);
</script>

<template>
    <aside
        class="flex min-h-[20rem] flex-col rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/80"
    >
        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800 md:px-5 md:py-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-muted md:text-xs">
                Detalle de aventura
            </p>
        </div>

        <div
            v-if="!level || !world"
            class="flex flex-1 flex-col items-center justify-center gap-3 px-6 py-10 text-center"
        >
            <div class="rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                <WorldIcon
                    v-if="world"
                    :tier="world.tier"
                    size-class="h-8 w-8"
                />
                <Star
                    v-else
                    class="h-8 w-8 text-indigo-400"
                />
            </div>
            <p class="text-sm font-medium text-heading">
                Selecciona un nodo del mapa
            </p>
            <p class="max-w-[14rem] text-xs text-muted">
                Haz clic en un nivel para ver qué aprenderás, qué comandos practicarás y cuánto dura el desafío.
            </p>
        </div>

        <div
            v-else
            class="flex flex-1 flex-col"
        >
            <div class="space-y-3 px-4 py-4 md:px-5 md:py-5">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg font-display text-lg font-bold text-white"
                        :class="{
                            'bg-emerald-600': status === 'completed',
                            'bg-blue-600': status === 'current',
                            'bg-amber-500': status === 'pending',
                            'bg-violet-600': status === 'lockout' || level.is_boss,
                            'bg-slate-500': status === 'locked',
                            'bg-indigo-600': previewMode || status === 'preview',
                        }"
                    >
                        {{ level.is_boss ? "★" : level.id }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <span
                            class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                            :class="statusClass"
                        >
                            {{ statusLabel }}
                        </span>
                        <h3 class="mt-1 text-base font-bold leading-snug text-heading md:text-lg">
                            {{ level.title }}
                        </h3>
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-muted">
                            <WorldIcon
                                :tier="level.tier as WorldTierSlug"
                                size-class="h-3 w-3"
                            />
                            {{ worldNames[level.tier] ?? world.name }}
                            <template v-if="zoneLabel">
                                <span>·</span>
                                <WorldIcon
                                    v-if="level.is_boss"
                                    boss
                                    size-class="h-3 w-3"
                                />
                                <WorldIcon
                                    v-else-if="level.zone"
                                    :zone="level.zone"
                                    size-class="h-3 w-3"
                                />
                                {{ zoneLabel }}
                            </template>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] font-medium text-body dark:bg-gray-800">
                        {{ typeLabels[level.type] }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-[11px] text-muted dark:bg-gray-800">
                        <Clock class="h-3 w-3" />
                        ~{{ level.duration_minutes }} min
                    </span>
                    <PowerChip
                        v-if="sublevelReward > 0 && !level.is_boss"
                        class="!inline-flex"
                        :amount="sublevelReward"
                        sign="+"
                        size="sm"
                    />
                </div>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto px-4 pb-4 md:px-5 md:pb-5">
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                        Escenario
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-body md:text-sm">
                        {{ level.scenario }}
                    </p>
                </div>

                <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-3 dark:border-indigo-900 dark:bg-indigo-950/30">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                        Objetivo · qué aprenderás
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-body md:text-sm">
                        {{ level.objective }}
                    </p>
                </div>

                <div
                    v-if="level.gameplay"
                    class="rounded-lg border border-amber-100 bg-amber-50/60 p-3 dark:border-amber-900 dark:bg-amber-950/30"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                        Gameplay
                    </p>
                    <p class="mt-1 text-xs leading-relaxed text-body md:text-sm">
                        {{ level.gameplay }}
                    </p>
                </div>

                <div
                    v-if="zone?.curriculum?.length"
                    class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                        Temario de la zona
                    </p>
                    <ul class="mt-1.5 space-y-1 text-xs text-body">
                        <li
                            v-for="item in zone.curriculum"
                            :key="item"
                            class="flex gap-2"
                        >
                            <Star class="mt-0.5 h-3 w-3 shrink-0 text-amber-500" />
                            {{ item }}
                        </li>
                    </ul>
                </div>

                <div
                    v-if="zone?.commands?.length"
                    class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                        Comandos
                    </p>
                    <p class="mt-1 font-mono text-xs text-body">
                        {{ zone.commands.join(" · ") }}
                    </p>
                </div>

                <div
                    v-if="zone?.english?.length"
                    class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800/60"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">
                        Inglés técnico
                    </p>
                    <p class="mt-1 text-xs text-body">
                        {{ zone.english.join(" · ") }}
                    </p>
                </div>

                <div
                    v-if="level.is_boss && world.boss"
                    class="rounded-lg border border-violet-200 bg-violet-50/60 p-3 dark:border-violet-900 dark:bg-violet-950/30"
                >
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-400">
                        {{ world.boss.title }}
                    </p>
                    <p class="mt-1 text-xs text-body md:text-sm">
                        {{ world.boss.description }}
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800 md:px-5">
                <div
                    v-if="previewMode"
                    class="flex items-start gap-2 rounded-lg border border-dashed border-indigo-200 bg-indigo-50/50 px-3 py-2.5 text-xs text-indigo-800 dark:border-indigo-800 dark:bg-indigo-950/30 dark:text-indigo-300"
                >
                    <Lock class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    <span>
                        Desbloquea el mundo con {{ POWER_UNIT }} para recorrer este camino y completar cada desafío.
                    </span>
                </div>

                <button
                    v-else-if="canStart"
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                    @click="emit('start')"
                >
                    <Play class="h-4 w-4" />
                    Iniciar desafío
                </button>

                <button
                    v-else-if="showCompletedAction"
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-800 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-950/60"
                    @click="emit('viewCompleted')"
                >
                    <CheckCircle2 class="h-4 w-4" />
                    Ver desafío completado
                </button>

                <p
                    v-else-if="status === 'locked'"
                    class="flex items-center gap-2 text-xs text-muted"
                >
                    <Lock class="h-3.5 w-3.5 shrink-0" />
                    Completa el nivel anterior para desbloquear este nodo.
                </p>
            </div>
        </div>
    </aside>
</template>
