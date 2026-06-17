<script setup lang="ts">
import type { ProgressChartPoint } from "@/types/progress";
import { computed } from "vue";

const props = defineProps<{
    points: ProgressChartPoint[];
    label?: string;
}>();

const chartHeight = 120;
const chartWidth = 320;

const path = computed(() => {
    if (props.points.length === 0) {
        return "";
    }

    const maxAccuracy = 100;
    const minAccuracy = Math.min(
        ...props.points.map((point) => point.accuracy),
        50,
    );
    const range = maxAccuracy - minAccuracy || 1;

    const coordinates = props.points.map((point, index) => {
        const x =
            (index / Math.max(props.points.length - 1, 1)) * chartWidth;
        const y =
            chartHeight -
            ((point.accuracy - minAccuracy) / range) * (chartHeight - 16);

        return `${x},${y}`;
    });

    return `M ${coordinates.join(" L ")}`;
});
</script>

<template>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="mb-4 text-sm font-medium text-gray-700">
            {{ label ?? "Precisión reciente" }}
        </p>

        <div class="overflow-x-auto">
            <svg
                :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                class="h-32 w-full min-w-[280px]"
                role="img"
                :aria-label="label ?? 'Gráfico de precisión'"
            >
                <line
                    x1="0"
                    :y1="chartHeight - 1"
                    :x2="chartWidth"
                    :y2="chartHeight - 1"
                    stroke="#e5e7eb"
                    stroke-width="1"
                />
                <path
                    v-if="path"
                    :d="path"
                    fill="none"
                    stroke="#2563eb"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </div>

        <div class="mt-3 flex justify-between text-xs text-gray-500">
            <span
                v-for="point in points"
                :key="point.date"
            >
                {{ point.date.slice(5) }}
            </span>
        </div>
    </div>
</template>
