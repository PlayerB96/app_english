<script setup lang="ts">
import type { WorldMapMilestone } from "@/utils/buildWorldMapModel";
import { WORLD_MAP_CARD } from "@/utils/buildWorldMapModel";
import type { WorldMilestoneStatus } from "@/utils/worldMapLayout";
import type { WorldMapZoneTheme } from "@/utils/worldMapThemes";
import { worldBossIcon, worldZoneIcon } from "@/utils/worldIcons";
import type { PageProps } from "@/types/auth";
import { Check, ChevronRight, Clock, Lock } from "@lucide/vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage<{ game: PageProps["game"] }>();

const props = defineProps<{
    milestone: WorldMapMilestone;
    zone: WorldMapZoneTheme;
    status: WorldMilestoneStatus;
    locked: boolean;
    interactive: boolean;
    selected: boolean;
    lockoutTimer?: string | null;
    lockedLevelId?: number | null;
}>();

const emit = defineEmits<{
    activate: [slug: string];
}>();

const W = WORLD_MAP_CARD.width;
const H = WORLD_MAP_CARD.height;
const ICON_X = 24;
const TEXT_X = 42;
const TEXT_PAD_RIGHT = 26;
const ICON_R = 12;
const ICON_SIZE = 18;
const BADGE_SIZE = 18;

const textBlockWidth = W - TEXT_X - TEXT_PAD_RIGHT;

const left = computed(() => props.milestone.x - W / 2);
const top = computed(() => props.milestone.y - H / 2);

const isBoss = computed(() => props.milestone.kind === "boss");
const isLockout = computed(() => props.status === "lockout" && props.lockoutTimer);

const icon = computed(() =>
    isBoss.value ? worldBossIcon() : worldZoneIcon(props.milestone.slug),
);

const headline = computed(() =>
    isBoss.value ? "Final Boss" : props.milestone.name,
);

const subline = computed(() => {
    if (isLockout.value && props.lockoutTimer) {
        return props.lockoutTimer;
    }

    if (isBoss.value) {
        return props.milestone.name;
    }

    const commands = props.milestone.commands.join(" · ");

    return commands || "Explora la zona";
});

const statusLine = computed(() => {
    if (isLockout.value) {
        const hours = page.props.game.world_lockout_hours ?? 4;
        const levelLabel = props.lockedLevelId
            ? `Nivel ${props.lockedLevelId} · `
            : "";

        return `${levelLabel}Bloqueado ${hours} h`;
    }

    return `Niveles ${props.milestone.levelRange}`;
});

const lockoutSubPrefix = computed(() =>
    isLockout.value && props.lockoutTimer ? "Reintenta en " : "",
);

function handleActivate(): void {
    if (!props.interactive) {
        return;
    }

    emit("activate", props.milestone.slug);
}
</script>

<template>
    <g
        class="world-map-milestone"
        :class="{
            'world-map-milestone--locked': status === 'locked' || locked,
            'world-map-milestone--current': status === 'current' && !locked,
            'world-map-milestone--completed': status === 'completed' && !locked,
            'world-map-milestone--lockout': isLockout,
            'world-map-milestone--lockout-clickable': isLockout && interactive,
            'world-map-milestone--boss': isBoss,
            'world-map-milestone--selected': selected,
        }"
        :style="{ pointerEvents: interactive ? 'auto' : 'none' }"
        role="button"
        :tabindex="interactive ? 0 : -1"
        :aria-label="
            isLockout
                ? `${headline}, bloqueado, reintenta en ${lockoutTimer}`
                : `${headline}, niveles ${milestone.levelRange}`
        "
        :aria-disabled="!interactive"
        @click="handleActivate"
        @keydown.enter="handleActivate"
        @keydown.space.prevent="handleActivate"
    >
        <rect
            :x="left"
            :y="top"
            :width="W"
            :height="H"
            rx="12"
            :fill="zone.fill"
            :stroke="
                isLockout
                    ? '#fbbf24'
                    : status === 'current' && !locked
                        ? zone.accent
                        : zone.fillDark
            "
            :stroke-width="selected ? 3 : isLockout ? 2.5 : 1.5"
            filter="url(#wm-shadow)"
        />
        <rect
            :x="left + 3"
            :y="top + 3"
            :width="W - 6"
            :height="H - 6"
            rx="9"
            :fill="zone.fillDark"
            fill-opacity="0.35"
        />

        <circle
            :cx="left + ICON_X"
            :cy="milestone.y"
            :r="ICON_R"
            :fill="zone.fillDark"
            :stroke="isLockout ? '#fbbf24' : zone.accent"
            stroke-width="1.5"
        />
        <foreignObject
            :x="left + ICON_X - ICON_SIZE / 2"
            :y="milestone.y - ICON_SIZE / 2"
            :width="ICON_SIZE"
            :height="ICON_SIZE"
        >
            <component
                :is="icon"
                class="h-full w-full"
                :style="{ color: isLockout ? '#fbbf24' : zone.accent }"
            />
        </foreignObject>

        <foreignObject
            :x="left + TEXT_X"
            :y="top + 4"
            :width="textBlockWidth"
            :height="H - 8"
        >
            <div
                xmlns="http://www.w3.org/1999/xhtml"
                class="world-map-milestone__copy"
            >
                <p class="world-map-milestone__headline">
                    {{ headline }}
                </p>
                <p
                    class="world-map-milestone__status"
                    :style="{ color: isLockout ? '#fcd34d' : zone.accent }"
                >
                    {{ statusLine }}
                </p>
                <p
                    class="world-map-milestone__subline"
                    :class="{ 'world-map-milestone__subline--lockout': isLockout }"
                >
                    <template v-if="lockoutSubPrefix">{{ lockoutSubPrefix }}</template>{{ subline }}
                </p>
            </div>
        </foreignObject>

        <g :transform="`translate(${left + W - 24}, ${milestone.y - 9})`">
            <foreignObject
                v-if="status === 'locked' || locked"
                x="0"
                y="0"
                :width="BADGE_SIZE"
                :height="BADGE_SIZE"
            >
                <Lock class="h-[18px] w-[18px] text-slate-300" />
            </foreignObject>
            <foreignObject
                v-else-if="isLockout"
                x="0"
                y="0"
                :width="BADGE_SIZE"
                :height="BADGE_SIZE"
            >
                <Clock class="h-[18px] w-[18px] text-amber-300" />
            </foreignObject>
            <foreignObject
                v-else-if="status === 'completed'"
                x="0"
                y="0"
                :width="BADGE_SIZE"
                :height="BADGE_SIZE"
            >
                <Check class="h-[18px] w-[18px] text-emerald-300" />
            </foreignObject>
            <foreignObject
                v-else
                x="0"
                y="0"
                :width="BADGE_SIZE"
                :height="BADGE_SIZE"
            >
                <ChevronRight class="h-[18px] w-[18px] text-white" />
            </foreignObject>
        </g>
    </g>
</template>

<style scoped>
.world-map-milestone__copy {
    display: flex;
    height: 100%;
    flex-direction: column;
    justify-content: center;
    gap: 1px;
    overflow: hidden;
    line-height: 1.2;
}

.world-map-milestone__headline {
    margin: 0;
    font-family: "Chakra Petch", Outfit, sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #f8fafc;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.world-map-milestone__status {
    margin: 0;
    font-family: Outfit, sans-serif;
    font-size: 10.5px;
    font-weight: 600;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.world-map-milestone__subline {
    margin: 0;
    font-family: "JetBrains Mono", Outfit, monospace;
    font-size: 9.5px;
    color: #cbd5e1;
    word-break: break-word;
    overflow-wrap: anywhere;
}

.world-map-milestone__subline--lockout {
    color: #fde68a;
    letter-spacing: 0.03em;
}

.world-map-milestone--current > rect:first-of-type {
    animation: wm-milestone-glow 1.6s ease-in-out infinite;
}

.world-map-milestone--lockout > rect:first-of-type {
    animation: wm-milestone-lockout 2s ease-in-out infinite;
}

.world-map-milestone--lockout-clickable {
    cursor: pointer;
}

@keyframes wm-milestone-glow {
    0%,
    100% {
        stroke-opacity: 1;
    }

    50% {
        stroke-opacity: 0.55;
    }
}

@keyframes wm-milestone-lockout {
    0%,
    100% {
        stroke-opacity: 1;
    }

    50% {
        stroke-opacity: 0.65;
    }
}
</style>
