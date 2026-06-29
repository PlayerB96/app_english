<script setup lang="ts">
import type {
    WorldNodeVisualStatus,
    ZoneLevelNodeView,
} from "@/utils/worldMapLayout";
import { Check, Lock, Star } from "@lucide/vue";

defineProps<{
    nodes: ZoneLevelNodeView[];
    pathD: string;
    pathColor: string;
}>();

const emit = defineEmits<{
    select: [id: number];
}>();

function nodeFill(status: WorldNodeVisualStatus, isBoss: boolean): string {
    switch (status) {
        case "completed":
            return "#059669";
        case "current":
            return "#2563eb";
        case "pending":
            return "#d97706";
        case "lockout":
            return "#7c3aed";
        default:
            return isBoss ? "#6d28d9" : "#475569";
    }
}

function nodeStroke(status: WorldNodeVisualStatus, isBoss: boolean): string {
    switch (status) {
        case "completed":
            return "#6ee7b7";
        case "current":
            return "#93c5fd";
        case "pending":
            return "#fbbf24";
        case "lockout":
            return "#c4b5fd";
        default:
            return isBoss ? "#c4b5fd" : "#64748b";
    }
}
</script>

<template>
    <g class="world-map-zone-levels">
        <path
            :d="pathD"
            fill="none"
            :stroke="pathColor"
            stroke-width="6"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-dasharray="2 14"
            opacity="0.55"
            filter="url(#wm-path-glow)"
        />

        <g
            v-for="node in nodes"
            :key="node.id"
            class="world-map-node"
            :class="{ 'world-map-node--current': node.isCurrent }"
            :transform="`translate(${node.x}, ${node.y})`"
            :style="{ pointerEvents: node.interactive ? 'auto' : 'none' }"
            role="button"
            :tabindex="node.interactive ? 0 : -1"
            :aria-label="`Nivel ${node.label}`"
            :aria-disabled="!node.interactive"
            @click="node.interactive && emit('select', node.id)"
            @keydown.enter="node.interactive && emit('select', node.id)"
        >
            <circle
                v-if="node.selected"
                r="30"
                fill="none"
                stroke="#ffffff"
                stroke-width="3"
                opacity="0.95"
            />
            <circle
                v-if="node.isCurrent"
                r="28"
                fill="none"
                stroke="#60a5fa"
                stroke-width="2.5"
                opacity="0.85"
            />
            <circle
                r="23"
                :fill="nodeFill(node.status, node.isBoss)"
                :stroke="nodeStroke(node.status, node.isBoss)"
                stroke-width="3"
                filter="url(#wm-shadow)"
                :opacity="node.status === 'locked' ? 0.65 : 1"
            />
            <text
                text-anchor="middle"
                dominant-baseline="central"
                :fill="node.status === 'locked' ? '#94a3b8' : '#ffffff'"
                font-family="Chakra Petch, Outfit, sans-serif"
                :font-size="node.label.length > 1 ? '14' : '16'"
                font-weight="700"
                :opacity="node.status === 'locked' ? 0.6 : 1"
            >
                {{ node.label }}
            </text>

            <foreignObject
                v-if="node.status === 'locked'"
                x="-9"
                y="-9"
                width="18"
                height="18"
            >
                <Lock class="h-4 w-4 text-slate-300" />
            </foreignObject>
            <foreignObject
                v-else-if="node.status === 'completed'"
                x="11"
                y="-20"
                width="16"
                height="16"
            >
                <Check class="h-4 w-4 text-emerald-200" />
            </foreignObject>
            <foreignObject
                v-else-if="node.isBoss"
                x="-8"
                y="-8"
                width="16"
                height="16"
            >
                <Star class="h-4 w-4 text-violet-200" />
            </foreignObject>
        </g>
    </g>
</template>

<style scoped>
.world-map-node--current > circle:nth-last-of-type(1) {
    animation: wm-node-glow 1.6s ease-in-out infinite;
}

@keyframes wm-node-glow {
    0%,
    100% {
        stroke-opacity: 1;
    }

    50% {
        stroke-opacity: 0.45;
    }
}
</style>
