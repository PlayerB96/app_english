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
        return truncate(props.milestone.name, 26);
    }

    const commands = props.milestone.commands.join(" · ");

    return commands || "Explora la zona";
});

const statusLine = computed(() => {
    if (isLockout.value) {
        const hours = page.props.game.world_lockout_hours ?? 2;
        const levelLabel = props.lockedLevelId
            ? `Nivel ${props.lockedLevelId} · `
            : "";

        return `${levelLabel}Bloqueado ${hours} h`;
    }

    return `Niveles ${props.milestone.levelRange}`;
});

function truncate(value: string, max: number): string {
    return value.length > max ? `${value.slice(0, max - 1)}…` : value;
}

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
            rx="16"
            :fill="zone.fill"
            :stroke="
                isLockout
                    ? '#fbbf24'
                    : status === 'current' && !locked
                        ? zone.accent
                        : zone.fillDark
            "
            :stroke-width="selected ? 3.5 : isLockout ? 2.5 : 2"
            filter="url(#wm-shadow)"
        />
        <rect
            :x="left + 4"
            :y="top + 4"
            :width="W - 8"
            :height="H - 8"
            rx="12"
            :fill="zone.fillDark"
            fill-opacity="0.4"
        />

        <circle
            :cx="left + 34"
            :cy="milestone.y"
            r="21"
            :fill="zone.fillDark"
            :stroke="isLockout ? '#fbbf24' : zone.accent"
            stroke-width="2"
        />
        <foreignObject
            :x="left + 22"
            :y="milestone.y - 12"
            width="24"
            height="24"
        >
            <component
                :is="icon"
                class="h-6 w-6"
                :style="{ color: isLockout ? '#fbbf24' : zone.accent }"
            />
        </foreignObject>

        <text
            :x="left + 64"
            :y="milestone.y - 12"
            fill="#f8fafc"
            font-family="Chakra Petch, Outfit, sans-serif"
            font-size="13.5"
            font-weight="700"
        >
            {{ truncate(headline, 22) }}
        </text>
        <text
            :x="left + 64"
            :y="milestone.y + 4"
            :fill="isLockout ? '#fcd34d' : zone.accent"
            font-family="Outfit, sans-serif"
            font-size="10.5"
            font-weight="600"
        >
            {{ statusLine }}
        </text>
        <text
            :x="left + 64"
            :y="milestone.y + 20"
            :fill="isLockout ? '#fde68a' : '#cbd5e1'"
            font-family="'JetBrains Mono', Outfit, monospace"
            :font-size="isLockout ? 8 : 9.5"
            :letter-spacing="isLockout ? '0.02em' : undefined"
        >
            {{ isLockout ? subline : truncate(subline, 28) }}
        </text>

        <g :transform="`translate(${left + W - 26}, ${milestone.y - 11})`">
            <foreignObject
                v-if="status === 'locked' || locked"
                x="0"
                y="0"
                width="22"
                height="22"
            >
                <Lock class="h-5 w-5 text-slate-300" />
            </foreignObject>
            <foreignObject
                v-else-if="isLockout"
                x="0"
                y="0"
                width="22"
                height="22"
            >
                <Clock class="h-5 w-5 text-amber-300" />
            </foreignObject>
            <foreignObject
                v-else-if="status === 'completed'"
                x="0"
                y="0"
                width="22"
                height="22"
            >
                <Check class="h-5 w-5 text-emerald-300" />
            </foreignObject>
            <foreignObject
                v-else
                x="0"
                y="0"
                width="22"
                height="22"
            >
                <ChevronRight class="h-5 w-5 text-white" />
            </foreignObject>
        </g>
    </g>
</template>

<style scoped>
.world-map-milestone--current > rect:first-of-type {
    animation: wm-milestone-glow 1.6s ease-in-out infinite;
}

.world-map-milestone--lockout > rect:first-of-type {
    animation: wm-milestone-lockout 2s ease-in-out infinite;
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
