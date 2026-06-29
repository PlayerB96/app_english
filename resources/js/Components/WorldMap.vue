<script setup lang="ts">
import WorldMapMilestone from "@/Components/WorldMapMilestone.vue";
import { useLockoutCountdown } from "@/composables/useLockoutCountdown";
import { buildWorldMapModel } from "@/utils/buildWorldMapModel";
import {
    lockedLevelInMilestone,
    milestonePathD,
    resolveMilestoneStatus,
} from "@/utils/worldMapLayout";
import { worldMapTheme, zoneTheme } from "@/utils/worldMapThemes";
import type { WorldInfo, WorldLevel } from "@/types/world";
import gsap from "gsap";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = withDefaults(
    defineProps<{
        world: WorldInfo | null;
        levels: WorldLevel[];
        worldNames?: Record<string, string>;
        isUnlocked: (id: number) => boolean;
        isCompleted: (id: number) => boolean;
        isPending?: (id: number) => boolean;
        isLockedOut?: (id: number) => boolean;
        lockoutLabel?: (id: number) => string | null;
        overviewMode?: boolean;
    }>(),
    {
        worldNames: () => ({}),
        overviewMode: false,
        isPending: undefined,
        isLockedOut: undefined,
        lockoutLabel: undefined,
    },
);

const emit = defineEmits<{
    startZone: [slug: string];
}>();

const worldPathRef = ref<SVGPathElement | null>(null);
let pathTween: gsap.core.Tween | null = null;

const theme = computed(() =>
    props.world ? worldMapTheme(props.world.tier) : worldMapTheme("basico"),
);

const model = computed(() =>
    props.world ? buildWorldMapModel(props.world, props.levels) : null,
);

const hasMilestones = computed(() => (model.value?.milestones.length ?? 0) > 0);

const worldWidth = computed(() => model.value?.width ?? 680);
const worldHeight = computed(() => model.value?.height ?? 480);
const worldViewBox = computed(() => `0 0 ${worldWidth.value} ${worldHeight.value}`);

const mode = computed<"preview" | "world">(() =>
    props.overviewMode ? "preview" : "world",
);

const hasActiveLockouts = computed(() => {
    if (props.overviewMode || !props.isLockedOut || !model.value) {
        return false;
    }

    return model.value.milestones.some((milestone) =>
        milestone.levelIds.some((id) => props.isLockedOut?.(id)),
    );
});

const { tick } = useLockoutCountdown(hasActiveLockouts);

function milestoneStatus(
    milestone: ReturnType<typeof buildWorldMapModel>["milestones"][number],
) {
    if (props.overviewMode) {
        return "locked" as const;
    }

    return resolveMilestoneStatus(
        milestone,
        props.isCompleted,
        props.isUnlocked,
        props.isLockedOut,
    );
}

function milestoneInteractive(
    milestone: ReturnType<typeof buildWorldMapModel>["milestones"][number],
): boolean {
    if (props.overviewMode) {
        return false;
    }

    const status = milestoneStatus(milestone);

    return status === "current" || status === "completed";
}

function lockoutTimerForMilestone(
    milestone: ReturnType<typeof buildWorldMapModel>["milestones"][number],
): string | null {
    void tick.value;

    if (!props.isLockedOut || !props.lockoutLabel) {
        return null;
    }

    const levelId = lockedLevelInMilestone(milestone, props.isLockedOut);

    if (levelId === null) {
        return null;
    }

    return props.lockoutLabel(levelId);
}

function lockedLevelForMilestone(
    milestone: ReturnType<typeof buildWorldMapModel>["milestones"][number],
): number | null {
    if (!props.isLockedOut) {
        return null;
    }

    return lockedLevelInMilestone(milestone, props.isLockedOut);
}

const primaryMilestoneSlug = computed(() => {
    if (!model.value || props.overviewMode) {
        return null;
    }

    for (const milestone of model.value.milestones) {
        const status = milestoneStatus(milestone);

        if (status === "current" || status === "lockout") {
            return milestone.slug;
        }
    }

    return null;
});

const milestoneViews = computed(() => {
    if (!model.value) {
        return [];
    }

    return model.value.milestones.map((milestone) => {
        const status = milestoneStatus(milestone);

        return {
            milestone,
            status,
            zone: zoneTheme(theme.value, milestone.slug),
            locked: props.overviewMode,
            interactive: milestoneInteractive(milestone),
            current: (status === "current" || status === "lockout")
                && milestone.slug === primaryMilestoneSlug.value,
            lockoutTimer: lockoutTimerForMilestone(milestone),
            lockedLevelId: lockedLevelForMilestone(milestone),
        };
    });
});

const worldPath = computed(() =>
    model.value ? milestonePathD(model.value.milestones) : "",
);

const allCompleted = computed(() => {
    if (!model.value || props.overviewMode || !hasMilestones.value) {
        return false;
    }

    return model.value.milestones.every(
        (milestone) => milestoneStatus(milestone) === "completed",
    );
});

const title = computed(() => {
    if (!props.world) {
        return "Mundo";
    }

    return props.worldNames[props.world.tier] ?? props.world.name;
});

const subtitle = computed(() => props.world?.subtitle ?? "");

function handleMilestoneActivate(slug: string): void {
    emit("startZone", slug);
}

function runPathDraw(): void {
    pathTween?.kill();

    const path = worldPathRef.value;

    if (!path) {
        return;
    }

    const length = path.getTotalLength();
    gsap.set(path, {
        strokeDasharray: length,
        strokeDashoffset: length,
        opacity: mode.value === "preview" ? 0.5 : 0.85,
    });
    pathTween = gsap.to(path, {
        strokeDashoffset: 0,
        duration: 0.9,
        ease: "power2.out",
    });
}

onMounted(() => {
    nextTick(() => runPathDraw());
});

onBeforeUnmount(() => {
    pathTween?.kill();
});

watch(
    () => props.world?.tier,
    () => {
        nextTick(() => runPathDraw());
    },
);
</script>

<template>
    <div
        class="world-map"
        :class="`world-map--${mode}`"
    >
        <header class="world-map__header">
            <div class="min-w-0 flex-1">
                <p class="world-map__title">
                    {{ title }}
                </p>
                <p
                    v-if="subtitle"
                    class="world-map__subtitle"
                >
                    {{ subtitle }}
                </p>
            </div>
            <span
                v-if="allCompleted"
                class="world-map__badge"
            >
                Mundo completado
            </span>
        </header>

        <div class="world-map__canvas">
            <svg
                :viewBox="worldViewBox"
                class="world-map__svg"
                preserveAspectRatio="xMidYMid meet"
                role="img"
                :aria-label="`Mapa de ${title}`"
            >
                <defs>
                    <linearGradient
                        id="wm-bg"
                        x1="0%"
                        y1="0%"
                        x2="0%"
                        y2="100%"
                    >
                        <stop
                            offset="0%"
                            :stop-color="theme.background[0]"
                        />
                        <stop
                            offset="100%"
                            :stop-color="theme.background[1]"
                        />
                    </linearGradient>
                    <filter
                        id="wm-shadow"
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
                        id="wm-path-glow"
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
                    <radialGradient
                        id="wm-fog"
                        cx="50%"
                        cy="0%"
                        r="80%"
                    >
                        <stop
                            offset="0%"
                            stop-color="#0f172a"
                            stop-opacity="0.7"
                        />
                        <stop
                            offset="100%"
                            stop-color="#0f172a"
                            stop-opacity="0"
                        />
                    </radialGradient>
                </defs>

                <rect
                    x="0"
                    y="0"
                    :width="worldWidth"
                    :height="worldHeight"
                    fill="url(#wm-bg)"
                    rx="12"
                />

                <template v-if="hasMilestones">
                    <path
                        ref="worldPathRef"
                        :d="worldPath"
                        fill="none"
                        :stroke="theme.path"
                        stroke-width="6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :stroke-dasharray="mode === 'preview' ? '2 16' : undefined"
                        filter="url(#wm-path-glow)"
                    />

                    <rect
                        v-if="mode === 'preview'"
                        x="0"
                        y="0"
                        :width="worldWidth"
                        :height="worldHeight * 0.55"
                        fill="url(#wm-fog)"
                        pointer-events="none"
                    />

                    <WorldMapMilestone
                        v-for="view in milestoneViews"
                        :key="view.milestone.slug"
                        :milestone="view.milestone"
                        :zone="view.zone"
                        :status="view.status"
                        :locked="view.locked"
                        :interactive="view.interactive"
                        :selected="false"
                        :lockout-timer="view.lockoutTimer"
                        :locked-level-id="view.lockedLevelId"
                        @activate="handleMilestoneActivate"
                    />
                </template>

                <text
                    v-else
                    :x="worldWidth / 2"
                    :y="worldHeight / 2"
                    text-anchor="middle"
                    fill="#64748b"
                    font-family="Outfit, sans-serif"
                    font-size="16"
                    font-weight="700"
                    letter-spacing="0.15em"
                >
                    PRÓXIMAMENTE
                </text>
            </svg>
        </div>

        <footer class="world-map__footer">
            {{
                mode === "preview"
                    ? "Vista previa · 5 etapas bloqueadas"
                    : "Toca una etapa para responder 3 preguntas · fallo = bloqueo 2 h"
            }}
        </footer>
    </div>
</template>

<style scoped>
.world-map {
    display: flex;
    flex-direction: column;
    border-radius: 0.75rem;
    background: #0f172a;
    overflow: visible;
}

.world-map__header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.12);
    padding: 0.55rem 0.75rem;
    background: rgba(15, 23, 42, 0.95);
    border-radius: 0.75rem 0.75rem 0 0;
}

.world-map__canvas {
    overflow: visible;
    padding: 0.35rem 0.5rem 0.25rem;
}

.world-map__svg {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
}

.world-map__title {
    font-family: "Chakra Petch", Outfit, sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #f8fafc;
    line-height: 1.1;
}

.world-map__subtitle {
    font-size: 11px;
    color: #94a3b8;
    line-height: 1.2;
}

.world-map__badge {
    border-radius: 999px;
    background: rgba(16, 185, 129, 0.18);
    padding: 0.15rem 0.55rem;
    font-size: 10px;
    font-weight: 600;
    color: #34d399;
    white-space: nowrap;
    flex-shrink: 0;
}

.world-map__footer {
    border-top: 1px solid rgba(148, 163, 184, 0.1);
    padding: 0.4rem 0.75rem;
    font-size: 10px;
    color: rgb(148 163 184);
    text-align: right;
}

.world-map-milestone {
    cursor: default;
    outline: none;
}

.world-map--world .world-map-milestone:not([aria-disabled="true"]) {
    cursor: pointer;
}

.world-map-milestone--locked {
    filter: saturate(0.55) brightness(0.85);
}

.world-map-milestone:not([aria-disabled="true"]):hover rect:first-of-type {
    stroke: #ffffff;
}

.world-map-milestone:focus-visible rect:first-of-type {
    stroke: #ffffff;
    stroke-width: 4;
}
</style>
