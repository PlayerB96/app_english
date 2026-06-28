<script setup lang="ts">
import {
    isWorldCompleted,
    isWorldUnlocked,
    resolveNodeVisualStatus,
    worldPathD,
    WORLD_MAP_HEIGHT,
    WORLD_MAP_WIDTH,
    WORLD_MAP_WORLDS,
    type WorldMapWorld,
    type WorldNodeVisualStatus,
} from "@/utils/worldMapLayout";
import { Lock, Star } from "@lucide/vue";
import { computed } from "vue";

const props = defineProps<{
    worldNames: Record<string, string>;
    isUnlocked: (id: number) => boolean;
    isCompleted: (id: number) => boolean;
    isPending?: (id: number) => boolean;
    isLockedOut?: (id: number) => boolean;
    selectedId?: number | null;
}>();

const emit = defineEmits<{
    select: [id: number];
    viewCompleted: [id: number];
    viewLockedOut: [id: number];
}>();

const worlds = WORLD_MAP_WORLDS;

const pathColorClass: Record<WorldMapWorld["colorClass"], string> = {
    blue: "stroke-blue-500 dark:stroke-blue-400",
    purple: "stroke-purple-500 dark:stroke-purple-400",
    orange: "stroke-orange-500 dark:stroke-orange-400",
};

const nodeStrokeClass: Record<WorldMapWorld["colorClass"], string> = {
    blue: "stroke-blue-500 dark:stroke-blue-400",
    purple: "stroke-purple-500 dark:stroke-purple-400",
    orange: "stroke-orange-500 dark:stroke-orange-400",
};

function worldUnlocked(world: WorldMapWorld): boolean {
    return isWorldUnlocked(world.tier, props.isCompleted);
}

function nodeStatus(id: number, unlocked: boolean): WorldNodeVisualStatus {
    return resolveNodeVisualStatus(
        id,
        unlocked,
        props.isCompleted,
        (levelId) => props.isLockedOut?.(levelId) ?? false,
        (levelId) => props.isPending?.(levelId) ?? false,
        props.isUnlocked,
    );
}

function isInteractive(status: WorldNodeVisualStatus, unlocked: boolean): boolean {
    if (!unlocked) {
        return false;
    }

    return status === "current" || status === "pending" || status === "completed" || status === "lockout";
}

function handleNodeClick(id: number, status: WorldNodeVisualStatus, unlocked: boolean): void {
    if (!isInteractive(status, unlocked)) {
        return;
    }

    if (status === "completed") {
        emit("viewCompleted", id);

        return;
    }

    if (status === "lockout") {
        emit("viewLockedOut", id);

        return;
    }

    emit("select", id);
}

const primaryPlayableId = computed(() => {
    for (const world of worlds) {
        if (!worldUnlocked(world)) {
            continue;
        }

        for (const node of world.nodes) {
            const status = nodeStatus(node.id, true);

            if (status === "current" || status === "pending") {
                return node.id;
            }
        }
    }

    return null;
});
</script>

<template>
    <div class="world-map-scroll -mx-1 overflow-x-auto px-1 pb-1">
        <svg
            :viewBox="`0 0 ${WORLD_MAP_WIDTH} ${WORLD_MAP_HEIGHT}`"
            class="world-map-svg mx-auto block min-w-[36rem] max-w-full select-none"
            role="img"
            aria-label="Mapa de superniveles"
        >
            <rect
                width="100%"
                height="100%"
                class="fill-gray-50 dark:fill-gray-900/40"
                rx="12"
            />

            <template
                v-for="world in worlds"
                :key="world.tier"
            >
                <g
                    :opacity="worldUnlocked(world) ? 1 : 0.42"
                    class="transition-opacity"
                >
                    <rect
                        x="16"
                        :y="world.offsetY + 24"
                        width="560"
                        height="148"
                        rx="12"
                        class="fill-white stroke-gray-200 dark:fill-gray-900/60 dark:stroke-gray-700"
                    />
                    <rect
                        x="16"
                        :y="world.offsetY + 148"
                        width="560"
                        height="24"
                        class="fill-gray-100 stroke-gray-200 dark:fill-gray-800/80 dark:stroke-gray-700"
                    />

                    <text
                        x="32"
                        :y="world.offsetY + 52"
                        class="fill-gray-600 text-[11px] font-semibold dark:fill-gray-300"
                    >
                        {{ worldNames[world.tier] ?? world.tier }}
                    </text>
                    <text
                        v-if="!worldUnlocked(world)"
                        x="32"
                        :y="world.offsetY + 68"
                        class="fill-gray-400 text-[10px] dark:fill-gray-500"
                    >
                        Completa el mundo anterior
                    </text>
                    <text
                        v-else-if="isWorldCompleted(world, isCompleted)"
                        x="32"
                        :y="world.offsetY + 68"
                        class="fill-emerald-600 text-[10px] font-medium dark:fill-emerald-400"
                    >
                        Módulo completado
                    </text>

                    <path
                        :d="worldPathD(world.nodes, world.offsetY)"
                        fill="none"
                        class="opacity-70"
                        :class="
                            worldUnlocked(world)
                                ? pathColorClass[world.colorClass]
                                : 'stroke-gray-300 dark:stroke-gray-600'
                        "
                        stroke-width="3"
                        :stroke-dasharray="worldUnlocked(world) ? undefined : '6 6'"
                        stroke-linecap="round"
                    />

                    <g
                        v-for="node in world.nodes"
                        :key="node.id"
                    >
                        <g
                            :transform="`translate(${node.x}, ${node.y + world.offsetY})`"
                            class="outline-none"
                            :class="
                                isInteractive(nodeStatus(node.id, worldUnlocked(world)), worldUnlocked(world))
                                    ? 'cursor-pointer'
                                    : 'cursor-default'
                            "
                            role="button"
                            :aria-label="`Subnivel ${node.label} · ${worldNames[world.tier] ?? world.tier}`"
                            :aria-disabled="
                                !isInteractive(nodeStatus(node.id, worldUnlocked(world)), worldUnlocked(world))
                            "
                            @click="
                                handleNodeClick(
                                    node.id,
                                    nodeStatus(node.id, worldUnlocked(world)),
                                    worldUnlocked(world),
                                )
                            "
                        >
                            <circle
                                v-if="
                                    nodeStatus(node.id, worldUnlocked(world)) === 'current'
                                    && primaryPlayableId === node.id
                                "
                                r="22"
                                class="fill-none stroke-blue-500 opacity-35 dark:stroke-blue-400"
                                stroke-width="2"
                            />
                            <circle
                                v-if="selectedId === node.id"
                                r="24"
                                class="fill-none stroke-gray-900 dark:stroke-gray-100"
                                stroke-width="2"
                            />

                            <circle
                                r="18"
                                class="transition-colors"
                                :class="{
                                    'fill-gray-100 stroke-gray-300 dark:fill-gray-800 dark:stroke-gray-600':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'locked',
                                    'fill-blue-600 stroke-blue-600 dark:fill-blue-500 dark:stroke-blue-500':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'current',
                                    'fill-emerald-50 stroke-emerald-400 dark:fill-emerald-950/50 dark:stroke-emerald-500':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'completed',
                                    'fill-amber-50 stroke-amber-400 dark:fill-amber-950/40 dark:stroke-amber-500':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'pending',
                                    'fill-gray-200 stroke-gray-400 dark:fill-gray-800 dark:stroke-gray-500':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'lockout',
                                }"
                                stroke-width="2"
                            />

                            <foreignObject
                                v-if="nodeStatus(node.id, worldUnlocked(world)) === 'locked'"
                                x="-7"
                                y="-7"
                                width="14"
                                height="14"
                            >
                                <Lock class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                            </foreignObject>

                            <foreignObject
                                v-else-if="nodeStatus(node.id, worldUnlocked(world)) === 'completed'"
                                x="-6"
                                y="-14"
                                width="12"
                                height="12"
                            >
                                <Star
                                    class="h-3 w-3"
                                    :class="{
                                        'text-blue-500': world.colorClass === 'blue',
                                        'text-purple-500': world.colorClass === 'purple',
                                        'text-orange-500': world.colorClass === 'orange',
                                    }"
                                    fill="currentColor"
                                />
                            </foreignObject>

                            <text
                                text-anchor="middle"
                                :y="nodeStatus(node.id, worldUnlocked(world)) === 'completed' ? 6 : 5"
                                class="text-xs font-bold"
                                :class="{
                                    'fill-gray-400 dark:fill-gray-500':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'locked',
                                    'fill-white':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'current',
                                    'fill-gray-800 dark:fill-gray-100':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'completed'
                                        || nodeStatus(node.id, worldUnlocked(world)) === 'pending',
                                    'fill-gray-600 dark:fill-gray-300':
                                        nodeStatus(node.id, worldUnlocked(world)) === 'lockout',
                                }"
                            >
                                {{ node.label }}
                            </text>

                            <text
                                v-if="nodeStatus(node.id, worldUnlocked(world)) === 'lockout'"
                                y="32"
                                text-anchor="middle"
                                class="fill-amber-600 text-[9px] font-medium dark:fill-amber-400"
                            >
                                pausa
                            </text>
                        </g>
                    </g>

                    <g :transform="`translate(${world.castleX}, ${world.castleY + world.offsetY})`">
                        <path
                            d="M8 40V22l4-8 4 6 4-10 4 8 4-6 4 8v18H8z"
                            class="transition-colors"
                            :class="
                                isWorldCompleted(world, isCompleted) && worldUnlocked(world)
                                    ? {
                                        'fill-blue-500/35 stroke-blue-500 dark:fill-blue-400/30 dark:stroke-blue-400':
                                            world.colorClass === 'blue',
                                        'fill-purple-500/35 stroke-purple-500 dark:fill-purple-400/30 dark:stroke-purple-400':
                                            world.colorClass === 'purple',
                                        'fill-orange-500/35 stroke-orange-500 dark:fill-orange-400/30 dark:stroke-orange-400':
                                            world.colorClass === 'orange',
                                    }
                                    : 'fill-gray-300/40 stroke-gray-400 dark:fill-gray-700/40 dark:stroke-gray-500'
                            "
                            stroke-width="2"
                        />
                        <rect
                            x="20"
                            y="28"
                            width="8"
                            height="12"
                            rx="1"
                            class="opacity-50"
                            :class="
                                isWorldCompleted(world, isCompleted) && worldUnlocked(world)
                                    ? nodeStrokeClass[world.colorClass]
                                    : 'fill-gray-400 stroke-gray-500 dark:fill-gray-600 dark:stroke-gray-500'
                            "
                            stroke-width="1.5"
                        />
                        <line
                            x1="4"
                            y1="40"
                            x2="44"
                            y2="40"
                            class="stroke-gray-500 dark:stroke-gray-400"
                            stroke-width="2"
                        />
                        <text
                            x="24"
                            y="52"
                            text-anchor="middle"
                            class="fill-gray-400 text-[9px] font-semibold dark:fill-gray-500"
                        >
                            BOSS
                        </text>
                    </g>
                </g>

                <line
                    v-if="world.tier !== 'avanzado'"
                    x1="296"
                    :y1="world.offsetY + 188"
                    x2="296"
                    :y2="world.offsetY + 212"
                    class="stroke-gray-300 dark:stroke-gray-600"
                    stroke-width="2"
                    stroke-dasharray="4 4"
                />
            </template>
        </svg>
    </div>
</template>

<style scoped>
.world-map-scroll {
    scrollbar-width: thin;
}

.world-map-svg {
    width: 100%;
    height: auto;
}
</style>
