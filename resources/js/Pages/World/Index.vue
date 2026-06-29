<script setup lang="ts">
import WorldHeaderBadge from "@/Components/WorldHeaderBadge.vue";
import WorldIcon from "@/Components/WorldIcon.vue";
import WorldMap from "@/Components/WorldMap.vue";
import WorldQuizFeedback from "@/Components/WorldQuizFeedback.vue";
import WorldQuizStep from "@/Components/WorldQuizStep.vue";
import PowerChip from "@/Components/PowerChip.vue";
import PowerIcon from "@/Components/PowerIcon.vue";
import { useWorldLevelProgress } from "@/composables/useWorldLevelProgress";
import { confirmWorldUnlock } from "@/utils/confirmWorldUnlock";
import { buildLevelSessionQuestions } from "@/utils/buildLevelSessionQuestions";
import { formatLockoutRemaining } from "@/utils/formatLockout";
import { shuffleQuizOptions } from "@/utils/shuffleQuizOptions";
import type {
    WorldAccessState,
    WorldInfo,
    WorldLevel,
    WorldProgressState,
    WorldQuestion,
    WorldQuizFeedback as WorldQuizFeedbackState,
    WorldTierSlug,
} from "@/types/world";
import { WORLD_QUESTIONS_PER_LEVEL, WORLD_TOTAL_LEVELS } from "@/types/world";
import type { PageProps } from "@/types/auth";
import { ArrowLeft, Hourglass, Lock } from "@lucide/vue";
import { computed, ref, toRef, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { POWER_UNIT, powerCostLabel } from "@/utils/powerLabels";

const props = defineProps<{
    worlds: WorldInfo[];
    levels: WorldLevel[];
    world_access: WorldAccessState;
    progress: WorldProgressState;
    questions_by_level: Record<string, WorldQuestion[]>;
}>();

type Step = "gate" | "map" | "quiz" | "feedback";

const page = usePage<{ auth: PageProps["auth"]; game: PageProps["game"] }>();

const worldLockoutHours = computed(
    () => page.props.game.world_lockout_hours ?? 2,
);

const step = ref<Step>(props.world_access.unlocked ? "map" : "gate");
const selectedLevelId = ref<number | null>(null);
const selectedOption = ref<number | null>(null);
const quizFeedback = ref<WorldQuizFeedbackState | null>(null);
const shuffledOptions = ref<{
    options: [string, string, string];
    correct_index: number;
} | null>(null);
const sessionError = ref<string | null>(null);
const previewWorldTier = ref<WorldTierSlug | null>(
    props.world_access.unlocked
        ? null
        : (props.worlds.find((world) => world.status === "available")?.tier ?? null),
);
const unlocking = ref(false);

const access = toRef(props, "world_access");
const progressState = toRef(props, "progress");
const worldProgress = useWorldLevelProgress(progressState);

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

const selectedLevel = computed(
    () => props.levels.find((level) => level.id === selectedLevelId.value) ?? null,
);

const sessionQuestionIds = computed(() => {
    if (selectedLevelId.value === null) {
        return [];
    }

    return worldProgress.sessionQuestionsFor(selectedLevelId.value);
});

const activeQuestions = computed(() => {
    if (selectedLevelId.value === null) {
        return [];
    }

    const pool = props.questions_by_level[String(selectedLevelId.value)] ?? [];

    return buildLevelSessionQuestions(
        pool.map((question) => ({
            question_id: question.question_id,
            level_id: question.world_level_id,
            question_index: question.question_index,
            step_difficulty: question.difficulty === "medio"
                ? "medio"
                : question.difficulty === "dificil"
                    ? "dificil"
                    : "facil",
            sublevel_intensity: 1,
            prompt: question.prompt,
            options: question.options,
            correct_index: question.correct_index,
        })),
        sessionQuestionIds.value,
    );
});

const currentQuestion = computed((): WorldQuestion | null => {
    const levelId = selectedLevelId.value;

    if (levelId === null) {
        return null;
    }

    const answered = worldProgress.answeredQuestionsFor(levelId);
    const pool = props.questions_by_level[String(levelId)] ?? [];
    const next = activeQuestions.value.find(
        (question) => !answered.includes(question.question_id),
    );

    if (!next) {
        return null;
    }

    return pool.find((item) => item.question_id === next.question_id) ?? null;
});

watch(
    currentQuestion,
    (question) => {
        selectedOption.value = null;

        if (!question) {
            shuffledOptions.value = null;

            return;
        }

        shuffledOptions.value = shuffleQuizOptions(
            question.options,
            question.correct_index,
        );
    },
    { immediate: true },
);

const questionPosition = computed(() => {
    if (!selectedLevel.value || !currentQuestion.value) {
        return null;
    }

    return {
        current: currentQuestion.value.question_index,
        total: WORLD_QUESTIONS_PER_LEVEL,
    };
});

const selectedZoneLabel = computed(() => {
    const level = selectedLevel.value;

    if (!level) {
        return null;
    }

    if (level.is_boss) {
        return activeWorld.value?.boss?.title ?? "Final Boss";
    }

    const world = props.worlds.find((item) => item.tier === level.tier);

    return world?.zones.find((zone) => zone.slug === level.zone)?.name ?? null;
});

const completedCount = computed(() => progressState.value.completed.length);

const progressPercent = computed(() =>
    Math.round((completedCount.value / WORLD_TOTAL_LEVELS) * 100),
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

function levelsForTier(tier: WorldTierSlug): WorldLevel[] {
    return props.levels.filter((level) => level.tier === tier);
}

function firstPlayableLevelInZone(slug: string): number | null {
    const ids = props.levels
        .filter((level) => level.zone === slug)
        .map((level) => level.id)
        .sort((a, b) => a - b);

    if (ids.length === 0) {
        return null;
    }

    return (
        ids.find((id) => worldProgress.isUnlocked(id) && !worldProgress.isCompleted(id) && !worldProgress.isLockedOut(id))
        ?? ids.find((id) => worldProgress.isUnlocked(id) && !worldProgress.isCompleted(id))
        ?? null
    );
}

function isUnlocked(id: number): boolean {
    return worldProgress.isUnlocked(id);
}

function isCompleted(id: number): boolean {
    return worldProgress.isCompleted(id);
}

function isPending(id: number): boolean {
    return worldProgress.isPending(id);
}

function isLockedOut(id: number): boolean {
    return worldProgress.isLockedOut(id);
}

function formatLockoutLabel(id: number): string | null {
    return formatLockoutRemaining(worldProgress.lockoutRemaining(id));
}

watch(
    () => props.world_access.unlocked,
    (unlocked) => {
        if (unlocked) {
            step.value = "map";
        }
    },
);

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

async function startWorldLevel(id: number): Promise<void> {
    sessionError.value = null;

    if (!worldProgress.isUnlocked(id) || worldProgress.isLockedOut(id)) {
        return;
    }

    if (worldProgress.isCompleted(id)) {
        return;
    }

    selectedLevelId.value = id;

    try {
        await worldProgress.startSession(id);
    } catch {
        selectedLevelId.value = null;
        const errors = page.props.errors as Record<string, string> | undefined;
        sessionError.value =
            errors?.level_id ?? "No se pudo iniciar el desafío. Inténtalo de nuevo.";

        return;
    }

    if (activeQuestions.value.length === 0) {
        selectedLevelId.value = null;
        sessionError.value = "No hay preguntas disponibles para este desafío.";

        return;
    }

    selectedOption.value = null;
    quizFeedback.value = null;
    step.value = "quiz";
}

function handleStartFromZone(slug: string): void {
    const id = firstPlayableLevelInZone(slug);

    if (id !== null) {
        void startWorldLevel(id);
    }
}

function backToMap(): void {
    step.value = "map";
    selectedLevelId.value = null;
    selectedOption.value = null;
    quizFeedback.value = null;
    shuffledOptions.value = null;
}

async function submitAnswer(): Promise<void> {
    const question = currentQuestion.value;
    const options = shuffledOptions.value;
    const levelIdValue = selectedLevelId.value;

    if (!question || !options || levelIdValue === null || selectedOption.value === null) {
        return;
    }

    const isCorrect = selectedOption.value === options.correct_index;

    if (isCorrect) {
        const result = await worldProgress.markQuestionPassed(
            levelIdValue,
            question.question_id,
            { response_text: options.options[selectedOption.value] },
        );

        quizFeedback.value = {
            is_correct: true,
            correct_answer: options.options[options.correct_index],
            message: result.completed
                ? "¡Desafío completado! Respondiste correctamente las 3 preguntas."
                : `¡Correcto! Llevas ${result.correct}/${result.total} preguntas del desafío.`,
            locked_until: null,
            level_completed: result.completed,
            questions_correct: result.correct,
            questions_total: result.total,
        };
    } else {
        const lockedUntil = await worldProgress.markFailedWithLockout(
            levelIdValue,
            worldLockoutHours.value,
            {
                question_id: question.question_id,
                response_text: options.options[selectedOption.value],
            },
        );

        quizFeedback.value = {
            is_correct: false,
            correct_answer: options.options[options.correct_index],
            message: `Respuesta incorrecta. Este desafío queda bloqueado por ${worldLockoutHours.value} horas.`,
            locked_until: lockedUntil,
        };
    }

    step.value = "feedback";
}

function continueAfterFeedback(): void {
    if (!quizFeedback.value?.is_correct || quizFeedback.value.level_completed) {
        backToMap();

        return;
    }

    selectedOption.value = null;
    quizFeedback.value = null;
    step.value = "quiz";
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
                                    {{ previewWorld.zones.length }} etapas + boss · {{ WORLD_TOTAL_LEVELS }} desafíos dentro del camino. Empiezas abajo y avanzas hasta el jefe final.
                                </p>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/60">
                                <WorldMap
                                    :world="previewWorld"
                                    :levels="levelsForTier(previewWorld.tier)"
                                    :world-names="worldNames"
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
                    v-if="step !== 'map'"
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
                class="grid gap-4 lg:grid-cols-[minmax(13rem,15rem)_minmax(0,1fr)] lg:items-start lg:gap-6 xl:gap-8"
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

                <div class="min-w-0 space-y-3">
                    <div
                        v-if="sessionError"
                        role="alert"
                        class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200"
                    >
                        {{ sessionError }}
                    </div>

                    <div class="surface-card p-3 sm:p-4 lg:p-5">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 px-1">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted md:text-xs">
                                    Mapa de aventura
                                </p>
                                <p class="text-xs text-muted md:text-sm">
                                    Toca una etapa para responder 3 preguntas técnicas y avanzar
                                </p>
                            </div>
                        </div>
                        <WorldMap
                            :world="activeWorld"
                            :levels="props.levels"
                            :world-names="worldNames"
                            :is-unlocked="isUnlocked"
                            :is-completed="isCompleted"
                            :is-pending="isPending"
                            :is-locked-out="isLockedOut"
                            :lockout-label="formatLockoutLabel"
                            @start-zone="handleStartFromZone"
                        />
                    </div>

                    <p class="text-center text-xs text-muted md:text-sm">
                        Cada desafío tiene 3 preguntas. Si fallas una, queda bloqueado {{ worldLockoutHours }} horas.
                    </p>
                </div>
            </div>

            <WorldQuizStep
                v-else-if="step === 'quiz' && selectedLevel && currentQuestion && shuffledOptions && questionPosition"
                :level="selectedLevel"
                :question="currentQuestion"
                :zone-label="selectedZoneLabel"
                :world-name="worldNames[selectedLevel.tier] ?? activeWorld?.name ?? 'Mundo'"
                :question-position="questionPosition"
                :shuffled-options="shuffledOptions.options"
                :selected-option="selectedOption"
                @select="selectedOption = $event"
                @submit="submitAnswer"
            />

            <WorldQuizFeedback
                v-else-if="step === 'feedback' && quizFeedback"
                :feedback="quizFeedback"
                @continue="continueAfterFeedback"
            />
        </template>
    </div>
</template>

