<script setup lang="ts">
import {
    stepDifficultyBadgeClass,
    stepDifficultyLabel,
    type StepDifficulty,
} from "@/utils/learningLabels";

defineProps<{
    difficulty: StepDifficulty;
}>();
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
        :class="stepDifficultyBadgeClass(difficulty)"
    >
        {{ stepDifficultyLabel(difficulty) }}
    </span>
</template>
