<script setup lang="ts">
import PowerChip from "@/Components/PowerChip.vue";
import SublevelIntensity from "@/Components/SublevelIntensity.vue";
import { useLockoutCountdown } from "@/composables/useLockoutCountdown";
import type { PageProps } from "@/types/auth";
import type { TierInfo } from "@/types/levels";
import { sublevelLabel } from "@/utils/learningLabels";
import { powerRewardTitle } from "@/utils/powerLabels";
import { usePage } from "@inertiajs/vue3";
import { Check, Clock, Lock, RotateCcw, Star } from "@lucide/vue";
import { computed } from "vue";

const page = usePage<{ game: PageProps["game"] }>();
const sublevelReward = computed(() => page.props.game.sublevel_complete_reward);

const props = defineProps<{
    tiers: TierInfo[];
    isUnlocked: (id: number) => boolean;
    isCompleted: (id: number) => boolean;
    isPending?: (id: number) => boolean;
    pendingLabel?: (id: number) => string | null;
    isLockedOut?: (id: number) => boolean;
    lockoutLabel?: (id: number) => string | null;
    canResetTier?: (tier: TierInfo["slug"]) => boolean;
    tierResetLabel?: (tier: TierInfo["slug"]) => string | null;
    tierResetCost?: (tier: TierInfo["slug"]) => number;
    levelId: (tier: TierInfo["slug"], phase: number) => number;
    selectedId?: number | null;
}>();

const emit = defineEmits<{
    select: [id: number];
    viewCompleted: [id: number];
    viewLockedOut: [id: number];
    resetTier: [tier: TierInfo];
}>();

const phases = [1, 2, 3, 4, 5];

const hasActiveLockouts = computed(() => {
    for (const tier of props.tiers) {
        for (const phase of phases) {
            if (props.isLockedOut?.(props.levelId(tier.slug, phase))) {
                return true;
            }
        }
    }

    return false;
});

const { tick } = useLockoutCountdown(hasActiveLockouts);

function displayLockoutLabel(id: number): string | null {
    void tick.value;

    return props.lockoutLabel?.(id) ?? null;
}

function isProgressionLocked(id: number): boolean {
    return (
        !props.isUnlocked(id) &&
        !(props.isLockedOut?.(id) ?? false) &&
        !props.isCompleted(id)
    );
}

function handleSelect(
    id: number,
    unlocked: boolean,
    lockedOut: boolean,
    completed: boolean,
): void {
    if (lockedOut) {
        emit("viewLockedOut", id);

        return;
    }

    if (!unlocked) {
        return;
    }

    if (completed) {
        emit("viewCompleted", id);

        return;
    }

    emit("select", id);
}
</script>

<template>
    <div class="grid gap-6 lg:gap-8 lg:grid-cols-1 xl:grid-cols-2">
        <article
            v-for="tier in tiers"
            :key="tier.slug"
            class="surface-card min-w-0 p-5 lg:p-6 xl:min-w-[22rem]"
        >
            <div class="mb-4 grid grid-cols-[minmax(0,1fr)_6.75rem] items-start gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-heading">
                        {{ tier.name }}
                    </h2>
                    <p class="mt-1 min-h-[2.5rem] text-sm leading-5 text-muted">
                        {{ tier.description }}
                    </p>
                    <p class="mt-1.5 text-xs text-muted">
                        5 subniveles · 3 preguntas c/u (Fácil → Medio → Difícil)
                    </p>
                </div>

                <div class="flex w-full shrink-0 flex-col items-stretch gap-1">
                    <button
                        v-if="canResetTier?.(tier.slug)"
                        type="button"
                        class="inline-flex w-full flex-col items-center justify-center gap-0.5 rounded-lg border border-amber-200 bg-amber-50 px-2 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-700/50 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-950/60"
                        @click="emit('resetTier', tier)"
                    >
                        <span class="inline-flex items-center gap-1">
                            <RotateCcw class="h-3.5 w-3.5 shrink-0" />
                            Reiniciar
                        </span>
                        <span
                            v-if="tierResetCost?.(tier.slug)"
                            class="inline-flex items-center"
                        >
                            <PowerChip
                                :amount="tierResetCost(tier.slug)"
                                sign="−"
                                size="md"
                            />
                        </span>
                    </button>
                    <p
                        v-if="tierResetLabel?.(tier.slug)"
                        class="text-center text-[10px] font-medium leading-tight text-muted"
                    >
                        {{ tierResetLabel(tier.slug) }}
                    </p>
                </div>
                <div
                    v-if="!canResetTier?.(tier.slug) && !tierResetLabel?.(tier.slug)"
                    class="w-full"
                    aria-hidden="true"
                />
            </div>

            <div class="overflow-x-auto pb-1 sm:overflow-visible">
                <div class="grid min-w-[29rem] grid-cols-[repeat(5,minmax(5.75rem,1fr))] gap-2 sm:min-w-0 sm:gap-2.5 lg:gap-3">
                <button
                    v-for="phase in phases"
                    :key="`${tier.slug}-${phase}`"
                    type="button"
                    :aria-label="sublevelLabel(phase)"
                    class="relative flex min-h-[8.5rem] min-w-[5.75rem] w-full flex-col items-center justify-between rounded-xl border px-1.5 py-2.5 text-center transition-all sm:px-2.5 sm:py-3 lg:px-3"
                    :title="
                        isCompleted(levelId(tier.slug, phase))
                            ? 'Ver respuestas correctas'
                            : isLockedOut?.(levelId(tier.slug, phase))
                            ? 'Ver tiempo de espera'
                              : undefined
                    "
                    :class="{
                        'border-blue-500 bg-blue-50 ring-2 ring-blue-200 dark:border-blue-500 dark:bg-blue-950/50 dark:ring-blue-900':
                            selectedId === levelId(tier.slug, phase),
                        'border-emerald-200 bg-emerald-50 hover:border-emerald-300 dark:border-emerald-800 dark:bg-emerald-950/40 dark:hover:border-emerald-700':
                            isCompleted(levelId(tier.slug, phase)) &&
                            selectedId !== levelId(tier.slug, phase),
                        'cursor-default':
                            isCompleted(levelId(tier.slug, phase)),
                        'border-amber-300 bg-amber-50 hover:border-amber-400 dark:border-amber-700 dark:bg-amber-950/50 dark:hover:border-amber-600':
                            isLockedOut?.(levelId(tier.slug, phase)) &&
                            selectedId !== levelId(tier.slug, phase),
                        'border-amber-200 bg-amber-50 hover:border-amber-300 dark:border-amber-800 dark:bg-amber-950/40 dark:hover:border-amber-700':
                            isPending?.(levelId(tier.slug, phase)) &&
                            !isLockedOut?.(levelId(tier.slug, phase)) &&
                            selectedId !== levelId(tier.slug, phase),
                        'border-gray-200 bg-gray-50 hover:border-blue-300 hover:bg-blue-50/50 dark:border-gray-700 dark:bg-gray-800/60 dark:hover:border-blue-600 dark:hover:bg-blue-950/30':
                            isUnlocked(levelId(tier.slug, phase)) &&
                            !isCompleted(levelId(tier.slug, phase)) &&
                            !isPending?.(levelId(tier.slug, phase)) &&
                            !isLockedOut?.(levelId(tier.slug, phase)) &&
                            selectedId !== levelId(tier.slug, phase),
                        'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60 dark:border-gray-800 dark:bg-gray-900/40':
                            isProgressionLocked(levelId(tier.slug, phase)),
                    }"
                    :disabled="isProgressionLocked(levelId(tier.slug, phase))"
                    @click="
                        handleSelect(
                            levelId(tier.slug, phase),
                            isUnlocked(levelId(tier.slug, phase)),
                            isLockedOut?.(levelId(tier.slug, phase)) ?? false,
                            isCompleted(levelId(tier.slug, phase)),
                        )
                    "
                >
                    <div class="flex w-full flex-col items-center">
                        <Lock
                            v-if="isProgressionLocked(levelId(tier.slug, phase))"
                            class="mb-1 h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500"
                        />
                        <Lock
                            v-else-if="isLockedOut?.(levelId(tier.slug, phase))"
                            class="mb-1 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                        />
                        <Check
                            v-else-if="isCompleted(levelId(tier.slug, phase))"
                            class="mb-1 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"
                        />
                        <Clock
                            v-else-if="isPending?.(levelId(tier.slug, phase))"
                            class="mb-1 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                        />
                        <Star
                            v-else
                            class="mb-1 h-5 w-5 shrink-0 text-blue-500 dark:text-blue-400"
                        />

                        <span class="flex flex-col items-center leading-tight">
                            <span class="text-[10px] font-medium uppercase tracking-wide text-muted sm:text-[11px]">
                                Subnivel
                            </span>
                            <span class="text-sm font-semibold text-body sm:text-base">
                                {{ phase }}
                            </span>
                        </span>

                        <SublevelIntensity
                            class="mt-1.5"
                            :intensity="phase"
                        />

                        <PowerChip
                            v-if="!isCompleted(levelId(tier.slug, phase))"
                            class="mt-1"
                            :amount="sublevelReward"
                            sign="+"
                            :title="powerRewardTitle(sublevelReward)"
                        />
                    </div>

                    <div
                        class="flex min-h-[2.25rem] w-full shrink-0 items-center justify-center px-0.5 text-center text-[10px] leading-tight sm:text-[11px]"
                    >
                        <span
                            v-if="isPending?.(levelId(tier.slug, phase)) && pendingLabel?.(levelId(tier.slug, phase))"
                            class="flex flex-col items-center gap-0.5 font-medium text-amber-700 dark:text-amber-300"
                        >
                            <span>Pendiente</span>
                            <span>{{ pendingLabel(levelId(tier.slug, phase)) }}</span>
                        </span>
                        <span
                            v-else-if="isLockedOut?.(levelId(tier.slug, phase)) && displayLockoutLabel(levelId(tier.slug, phase))"
                            class="flex flex-col items-center gap-0.5 font-medium text-amber-700 dark:text-amber-300"
                        >
                            <span>En pausa</span>
                            <span>{{ displayLockoutLabel(levelId(tier.slug, phase)) }}</span>
                        </span>
                        <span
                            v-else-if="isCompleted(levelId(tier.slug, phase))"
                            class="text-[10px] font-medium text-emerald-600/80 dark:text-emerald-400/80"
                        >
                            Completado
                        </span>
                        <span
                            v-else
                            class="invisible select-none"
                            aria-hidden="true"
                        >
                            —
                        </span>
                    </div>
                </button>
                </div>
            </div>
        </article>
    </div>
</template>
