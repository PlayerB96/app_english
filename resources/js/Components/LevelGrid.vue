<script setup lang="ts">
import type { TierInfo } from "@/types/levels";
import { Check, Lock, Star } from "@lucide/vue";

defineProps<{
    tiers: TierInfo[];
    isUnlocked: (id: number) => boolean;
    isCompleted: (id: number) => boolean;
    isLockedOut?: (id: number) => boolean;
    lockoutLabel?: (id: number) => string | null;
    levelId: (tier: TierInfo["slug"], phase: number) => number;
    selectedId?: number | null;
}>();

const emit = defineEmits<{
    select: [id: number];
}>();

const phases = [1, 2, 3, 4, 5];

function handleSelect(
    id: number,
    unlocked: boolean,
    lockedOut: boolean,
): void {
    if (!unlocked || lockedOut) {
        return;
    }

    emit("select", id);
}
</script>

<template>
    <div class="space-y-6">
        <article
            v-for="tier in tiers"
            :key="tier.slug"
            class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm"
        >
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ tier.name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ tier.description }}
                </p>
            </div>

            <div class="grid grid-cols-5 gap-2 sm:gap-3">
                <button
                    v-for="phase in phases"
                    :key="`${tier.slug}-${phase}`"
                    type="button"
                    class="relative flex flex-col items-center justify-center rounded-xl border p-3 text-center transition-all sm:p-4"
                    :class="{
                        'border-blue-500 bg-blue-50 ring-2 ring-blue-200':
                            selectedId === levelId(tier.slug, phase),
                        'border-emerald-200 bg-emerald-50 hover:border-emerald-300':
                            isCompleted(levelId(tier.slug, phase)) &&
                            selectedId !== levelId(tier.slug, phase),
                        'border-gray-200 bg-gray-50 hover:border-blue-300 hover:bg-blue-50/50':
                            isUnlocked(levelId(tier.slug, phase)) &&
                            !isCompleted(levelId(tier.slug, phase)) &&
                            !isLockedOut?.(levelId(tier.slug, phase)) &&
                            selectedId !== levelId(tier.slug, phase),
                        'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60':
                            !isUnlocked(levelId(tier.slug, phase)) ||
                            isLockedOut?.(levelId(tier.slug, phase)),
                    }"
                    :disabled="
                        !isUnlocked(levelId(tier.slug, phase)) ||
                        isLockedOut?.(levelId(tier.slug, phase))
                    "
                    @click="
                        handleSelect(
                            levelId(tier.slug, phase),
                            isUnlocked(levelId(tier.slug, phase)),
                            isLockedOut?.(levelId(tier.slug, phase)) ?? false,
                        )
                    "
                >
                    <Lock
                        v-if="!isUnlocked(levelId(tier.slug, phase))"
                        class="mb-1 h-5 w-5 text-gray-400"
                    />
                    <Lock
                        v-else-if="isLockedOut?.(levelId(tier.slug, phase))"
                        class="mb-1 h-5 w-5 text-amber-500"
                    />
                    <Check
                        v-else-if="isCompleted(levelId(tier.slug, phase))"
                        class="mb-1 h-5 w-5 text-emerald-600"
                    />
                    <Star
                        v-else
                        class="mb-1 h-5 w-5 text-blue-500"
                    />

                    <span class="text-xs font-semibold text-gray-700 sm:text-sm">
                        Nivel {{ phase }}
                    </span>

                    <span
                        v-if="isLockedOut?.(levelId(tier.slug, phase)) && lockoutLabel?.(levelId(tier.slug, phase))"
                        class="mt-1 text-[10px] leading-tight text-amber-600"
                    >
                        {{ lockoutLabel(levelId(tier.slug, phase)) }}
                    </span>
                </button>
            </div>
        </article>
    </div>
</template>
