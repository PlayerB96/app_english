<script setup lang="ts">
import {
    isWorldCompleted,
    isWorldUnlocked,
    overviewNodeColors,
    resolveNodeVisualStatus,
    worldPathD,
    WORLD_MAP_WORLDS,
    type WorldMapWorld,
    type WorldNodeVisualStatus,
} from "@/utils/worldMapLayout";
import type { WorldTierSlug } from "@/types/world";
import gsap from "gsap";
import { Check, Lock, Star } from "@lucide/vue";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = withDefaults(
    defineProps<{
        worldNames: Record<string, string>;
        isUnlocked: (id: number) => boolean;
        isCompleted: (id: number) => boolean;
        isPending?: (id: number) => boolean;
        isLockedOut?: (id: number) => boolean;
        selectedId?: number | null;
        focusTier?: WorldTierSlug | null;
        /** Vista general sin clics (preview antes de desbloquear). */
        overviewMode?: boolean;
        fillWidth?: boolean;
    }>(),
    {
        selectedId: null,
        focusTier: "basico",
        overviewMode: false,
        fillWidth: true,
    },
);

const emit = defineEmits<{
    select: [id: number];
}>();

const pathRef = ref<SVGPathElement | null>(null);
const mapRootRef = ref<HTMLElement | null>(null);
let pulseTween: gsap.core.Tween | null = null;

const worlds = WORLD_MAP_WORLDS;

const visibleWorlds = computed(() =>
    worlds.filter((world) => world.tier === props.focusTier),
);

const activeWorld = computed(() => visibleWorlds.value[0] ?? null);

const mapWidth = computed(() => activeWorld.value?.mapWidth ?? 680);
const mapHeight = computed(() => activeWorld.value?.height ?? 480);

const mapViewBox = computed(() => `0 0 ${mapWidth.value} ${mapHeight.value}`);

function worldTitle(world: WorldMapWorld): string {
    return props.worldNames[world.tier] ?? world.name;
}

function worldUnlocked(world: WorldMapWorld): boolean {
    if (props.overviewMode) {
        return true;
    }

    return isWorldUnlocked(world, props.isCompleted);
}

function nodeStatus(id: number): WorldNodeVisualStatus {
    if (props.overviewMode) {
        return "locked";
    }

    return resolveNodeVisualStatus(
        id,
        worldUnlocked(activeWorld.value!),
        props.isCompleted,
        (levelId) => props.isLockedOut?.(levelId) ?? false,
        (levelId) => props.isPending?.(levelId) ?? false,
        props.isUnlocked,
    );
}

function isInteractive(id: number): boolean {
    if (props.overviewMode || !activeWorld.value) {
        return false;
    }

    const status = nodeStatus(id);

    return (
        status === "current"
        || status === "pending"
        || status === "completed"
        || status === "lockout"
    );
}

function handleNodeClick(id: number): void {
    if (!isInteractive(id)) {
        return;
    }

    emit("select", id);
}

const primaryPlayableId = computed(() => {
    if (!activeWorld.value || props.overviewMode) {
        return null;
    }

    for (const node of activeWorld.value.nodes) {
        const status = nodeStatus(node.id);

        if (status === "current" || status === "pending") {
            return node.id;
        }
    }

    return null;
});

function nodeFill(node: { id: number; zoneSlug?: string }): string {
    if (props.overviewMode) {
        return overviewNodeColors(node.zoneSlug).fill;
    }

    const status = nodeStatus(node.id);

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
            return node.id === 18 ? "#6d28d9" : "#475569";
    }
}

function nodeStroke(node: { id: number; zoneSlug?: string }): string {
    if (props.overviewMode) {
        return overviewNodeColors(node.zoneSlug).stroke;
    }

    const status = nodeStatus(node.id);

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
            return node.id === 18 ? "#c4b5fd" : "#64748b";
    }
}

function nodeLabelFill(id: number): string {
    if (props.overviewMode || nodeStatus(id) !== "locked") {
        return "#ffffff";
    }

    return "#94a3b8";
}

function pathD(world: WorldMapWorld): string {
    return worldPathD(world.nodes, 0);
}

function runEntranceAnimation(): void {
    const path = pathRef.value;

    if (!path) {
        return;
    }

    const length = path.getTotalLength();
    gsap.set(path, {
        strokeDasharray: length,
        strokeDashoffset: length,
        opacity: 1,
    });
    gsap.to(path, {
        strokeDashoffset: 0,
        duration: 1.4,
        ease: "power2.out",
    });

    gsap.from(".world-map-node", {
        scale: 0,
        opacity: 0,
        duration: 0.45,
        stagger: 0.035,
        delay: 0.25,
        ease: "back.out(2)",
        transformOrigin: "center center",
    });
}

function runCurrentPulse(): void {
    pulseTween?.kill();

    const target = mapRootRef.value?.querySelector(".world-map-node--current");

    if (!target) {
        return;
    }

    pulseTween = gsap.to(target, {
        scale: 1.08,
        duration: 0.85,
        yoyo: true,
        repeat: -1,
        ease: "sine.inOut",
        transformOrigin: "center center",
    });
}

async function refreshAnimations(): Promise<void> {
    await nextTick();
    runEntranceAnimation();
    runCurrentPulse();
}

onMounted(() => {
    refreshAnimations();
});

onBeforeUnmount(() => {
    pulseTween?.kill();
});

watch(
    () => [props.selectedId, props.overviewMode, primaryPlayableId.value],
    () => {
        runCurrentPulse();
    },
);

watch(activeWorld, () => {
    refreshAnimations();
});
</script>

<template>
    <div
        ref="mapRootRef"
        class="world-map"
        :class="overviewMode ? 'world-map--overview' : 'world-map--interactive'"
    >
        <svg
            :viewBox="mapViewBox"
            class="world-map__svg block w-full"
            role="img"
            :aria-label="`Mapa de ${activeWorld ? worldTitle(activeWorld) : 'mundo'}`"
        >
            <defs>
                <linearGradient
                    id="world-map-bg"
                    x1="0%"
                    y1="0%"
                    x2="0%"
                    y2="100%"
                >
                    <stop
                        offset="0%"
                        stop-color="#0f172a"
                    />
                    <stop
                        offset="100%"
                        stop-color="#111827"
                    />
                </linearGradient>
                <filter
                    id="node-shadow"
                    x="-50%"
                    y="-50%"
                    width="200%"
                    height="200%"
                >
                    <feDropShadow
                        dx="0"
                        dy="3"
                        stdDeviation="3"
                        flood-color="#000000"
                        flood-opacity="0.45"
                    />
                </filter>
                <filter
                    id="path-glow"
                    x="-20%"
                    y="-20%"
                    width="140%"
                    height="140%"
                >
                    <feGaussianBlur
                        stdDeviation="4"
                        result="blur"
                    />
                    <feMerge>
                        <feMergeNode in="blur" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
            </defs>

            <rect
                width="100%"
                height="100%"
                fill="url(#world-map-bg)"
                rx="12"
            />

            <template
                v-for="world in visibleWorlds"
                :key="world.tier"
            >
                <g :opacity="world.status === 'coming_soon' ? 0.5 : 1">
                    <rect
                        x="12"
                        y="12"
                        :width="mapWidth - 24"
                        height="52"
                        rx="8"
                        fill="#0f172a"
                        fill-opacity="0.65"
                    />
                    <text
                        x="28"
                        y="34"
                        fill="#f8fafc"
                        font-family="Chakra Petch, Outfit, sans-serif"
                        font-size="15"
                        font-weight="700"
                    >
                        {{ worldTitle(world) }}
                    </text>
                    <text
                        x="28"
                        y="52"
                        fill="#94a3b8"
                        font-family="Outfit, sans-serif"
                        font-size="11"
                    >
                        {{ world.subtitle }}
                    </text>
                    <text
                        v-if="overviewMode"
                        :x="mapWidth - 28"
                        y="42"
                        text-anchor="end"
                        fill="#94a3b8"
                        font-family="Outfit, sans-serif"
                        font-size="11"
                    >
                        {{ world.nodes.length }} niveles · {{ world.zoneBands.length }} zonas
                    </text>
                    <template v-else-if="isWorldCompleted(world, isCompleted)">
                        <text
                            :x="mapWidth - 28"
                            y="42"
                            text-anchor="end"
                            fill="#34d399"
                            font-family="Outfit, sans-serif"
                            font-size="11"
                            font-weight="600"
                        >
                            Mundo completado
                        </text>
                    </template>

                    <line
                        x1="428"
                        y1="72"
                        x2="428"
                        :y2="mapHeight - 12"
                        stroke="#334155"
                        stroke-opacity="0.65"
                    />

                    <template v-if="world.status === 'available' && world.nodes.length">
                        <g
                            v-for="zone in world.zoneBands"
                            :key="zone.slug"
                        >
                            <rect
                                x="20"
                                :y="zone.top"
                                width="400"
                                :height="zone.bottom - zone.top"
                                :fill="zone.fill"
                                fill-opacity="0.92"
                                rx="8"
                            />
                            <rect
                                x="24"
                                :y="zone.top + 4"
                                width="392"
                                :height="zone.bottom - zone.top - 8"
                                :fill="zone.fillDark"
                                fill-opacity="0.35"
                                rx="6"
                            />
                            <text
                                x="560"
                                :y="zone.y"
                                text-anchor="middle"
                                :fill="zone.accent"
                                font-family="Outfit, sans-serif"
                                font-size="11"
                                font-weight="700"
                            >
                                {{ zone.name }}
                            </text>
                            <text
                                x="560"
                                :y="zone.y + 16"
                                text-anchor="middle"
                                fill="#64748b"
                                font-family="Outfit, sans-serif"
                                font-size="9"
                            >
                                Niv. {{ zone.levelRange }}
                            </text>
                        </g>

                        <rect
                            x="430"
                            y="20"
                            :width="mapWidth - 446"
                            height="108"
                            fill="#4c1d95"
                            fill-opacity="0.88"
                            rx="10"
                        />
                        <text
                            x="560"
                            y="52"
                            text-anchor="middle"
                            fill="#c4b5fd"
                            font-family="Outfit, sans-serif"
                            font-size="11"
                            font-weight="700"
                        >
                            Final Boss
                        </text>
                        <text
                            x="560"
                            y="68"
                            text-anchor="middle"
                            fill="#7c3aed"
                            font-family="Outfit, sans-serif"
                            font-size="9"
                        >
                            Niv. 18
                        </text>

                        <path
                            ref="pathRef"
                            :d="pathD(world)"
                            fill="none"
                            stroke="#fcd34d"
                            stroke-width="6"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            filter="url(#path-glow)"
                            opacity="0"
                        />

                        <text
                            v-if="overviewMode"
                            x="100"
                            y="448"
                            fill="#86efac"
                            font-family="Chakra Petch, Outfit, sans-serif"
                            font-size="10"
                            font-weight="700"
                        >
                            INICIO
                        </text>
                        <text
                            v-if="overviewMode"
                            :x="world.castleX"
                            y="36"
                            text-anchor="middle"
                            fill="#c4b5fd"
                            font-family="Chakra Petch, Outfit, sans-serif"
                            font-size="10"
                            font-weight="700"
                        >
                            META
                        </text>

                        <g
                            v-for="node in world.nodes"
                            :key="node.id"
                            class="world-map-node"
                            :class="{
                                'world-map-node--current':
                                    !overviewMode
                                    && nodeStatus(node.id) === 'current'
                                    && primaryPlayableId === node.id,
                                'world-map-node--selected': selectedId === node.id,
                            }"
                            :transform="`translate(${node.x}, ${node.y})`"
                            :style="{ pointerEvents: isInteractive(node.id) ? 'auto' : 'none' }"
                            role="button"
                            :tabindex="isInteractive(node.id) ? 0 : -1"
                            :aria-label="`Nivel ${node.label}`"
                            :aria-disabled="!isInteractive(node.id)"
                            @click="handleNodeClick(node.id)"
                            @keydown.enter="handleNodeClick(node.id)"
                        >
                            <circle
                                v-if="selectedId === node.id"
                                r="30"
                                fill="none"
                                stroke="#ffffff"
                                stroke-width="3"
                                opacity="0.95"
                            />
                            <circle
                                v-if="
                                    !overviewMode
                                    && nodeStatus(node.id) === 'current'
                                    && primaryPlayableId === node.id
                                "
                                r="28"
                                fill="none"
                                stroke="#60a5fa"
                                stroke-width="2.5"
                                opacity="0.85"
                            />
                            <circle
                                r="22"
                                :fill="nodeFill(node)"
                                :stroke="nodeStroke(node)"
                                stroke-width="3"
                                filter="url(#node-shadow)"
                                :opacity="!overviewMode && nodeStatus(node.id) === 'locked' ? 0.65 : 1"
                            />
                            <text
                                text-anchor="middle"
                                dominant-baseline="central"
                                :fill="nodeLabelFill(node.id)"
                                font-family="Chakra Petch, Outfit, sans-serif"
                                :font-size="node.label.length > 1 ? '13' : '15'"
                                font-weight="700"
                                :opacity="!overviewMode && nodeStatus(node.id) === 'locked' ? 0.55 : 1"
                            >
                                {{ node.label }}
                            </text>
                            <foreignObject
                                v-if="!overviewMode && nodeStatus(node.id) === 'locked'"
                                x="-8"
                                y="-8"
                                width="16"
                                height="16"
                            >
                                <Lock class="h-4 w-4 text-slate-400" />
                            </foreignObject>
                            <foreignObject
                                v-else-if="!overviewMode && nodeStatus(node.id) === 'completed'"
                                :x="10"
                                y="-18"
                                width="14"
                                height="14"
                            >
                                <Check class="h-3.5 w-3.5 text-emerald-200" />
                            </foreignObject>
                            <foreignObject
                                v-else-if="node.id === 18 && (overviewMode || nodeStatus(node.id) !== 'locked')"
                                x="-7"
                                y="-7"
                                width="14"
                                height="14"
                            >
                                <Star class="h-3.5 w-3.5 text-violet-200" />
                            </foreignObject>
                        </g>

                        <text
                            :x="world.castleX"
                            :y="world.castleY + 38"
                            text-anchor="middle"
                            fill="#e9d5ff"
                            font-family="Chakra Petch, Outfit, sans-serif"
                            font-size="10"
                            font-weight="700"
                        >
                            {{ world.bossLabel }}
                        </text>
                    </template>

                    <text
                        v-else-if="world.status === 'coming_soon'"
                        :x="mapWidth / 2"
                        :y="mapHeight / 2"
                        text-anchor="middle"
                        fill="#64748b"
                        font-family="Outfit, sans-serif"
                        font-size="14"
                        font-weight="700"
                        letter-spacing="0.15em"
                    >
                        PRÓXIMAMENTE
                    </text>
                </g>
            </template>
        </svg>

        <p class="world-map__hint">
            {{
                overviewMode
                    ? "Vista general · recorrido de abajo hacia arriba"
                    : "Haz clic en un nodo para ver el detalle"
            }}
        </p>
    </div>
</template>

<style scoped>
.world-map {
    position: relative;
    overflow: hidden;
    border-radius: 0.75rem;
    aspect-ratio: 680 / 480;
    min-height: 320px;
}

.world-map__svg {
    height: auto;
    max-width: 100%;
}

.world-map-node {
    cursor: default;
    outline: none;
    transform-box: fill-box;
    transform-origin: center;
}

.world-map--interactive .world-map-node[role="button"]:not([aria-disabled="true"]) {
    cursor: pointer;
}

.world-map-node:focus-visible circle:nth-of-type(2) {
    stroke: #ffffff;
    stroke-width: 4;
}

.world-map__hint {
    pointer-events: none;
    position: absolute;
    right: 0.75rem;
    bottom: 0.5rem;
    font-size: 10px;
    color: rgb(100 116 139);
}
</style>
