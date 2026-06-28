import type { TierSlug } from "@/types/levels";
import type { WorldStatus } from "@/types/world";

export interface WorldMapNode {
    id: number;
    label: string;
    x: number;
    y: number;
    tier: TierSlug;
    zoneSlug?: string;
}

export interface WorldMapZoneBand {
    slug: string;
    emoji: string;
    name: string;
    y: number;
    top: number;
    bottom: number;
    levelRange: string;
    fill: string;
    fillDark: string;
    accent: string;
}

export interface WorldMapWorld {
    tier: TierSlug;
    emoji: string;
    name: string;
    subtitle: string;
    status: WorldStatus;
    offsetY: number;
    height: number;
    mapWidth?: number;
    colorClass: "blue" | "purple" | "orange";
    castleX: number;
    castleY: number;
    bossLabel: string;
    zoneBands: WorldMapZoneBand[];
    nodes: WorldMapNode[];
}

export const WORLD_MAP_HEIGHT = 620;
export const WORLD_MAP_WIDTH = 680;

export const WORLD_MAP_WORLDS: WorldMapWorld[] = [
    {
        tier: "basico",
        emoji: "🐧",
        name: "Linux Kingdom",
        subtitle: "The Broken System",
        status: "available",
        offsetY: 0,
        height: 480,
        mapWidth: 680,
        colorClass: "blue",
        castleX: 520,
        castleY: 58,
        bossLabel: "INTERVIEW",
        zoneBands: [
            {
                slug: "welcome-village",
                emoji: "🪵",
                name: "Welcome Village",
                y: 418,
                top: 368,
                bottom: 468,
                levelRange: "1–3",
                fill: "#14532d",
                fillDark: "#166534",
                accent: "#86efac",
            },
            {
                slug: "directory-forest",
                emoji: "🌲",
                name: "Directory Forest",
                y: 323,
                top: 278,
                bottom: 368,
                levelRange: "4–7",
                fill: "#064e3b",
                fillDark: "#065f46",
                accent: "#6ee7b7",
            },
            {
                slug: "permission-mountains",
                emoji: "🪨",
                name: "Permission Mountains",
                y: 233,
                top: 188,
                bottom: 278,
                levelRange: "8–12",
                fill: "#334155",
                fillDark: "#475569",
                accent: "#cbd5e1",
            },
            {
                slug: "process-mines",
                emoji: "⚙️",
                name: "Process Mines",
                y: 143,
                top: 98,
                bottom: 188,
                levelRange: "13–17",
                fill: "#1e293b",
                fillDark: "#334155",
                accent: "#94a3b8",
            },
        ],
        nodes: [
            { id: 1, label: "1", x: 100, y: 418, tier: "basico", zoneSlug: "welcome-village" },
            { id: 2, label: "2", x: 220, y: 418, tier: "basico", zoneSlug: "welcome-village" },
            { id: 3, label: "3", x: 340, y: 418, tier: "basico", zoneSlug: "welcome-village" },
            { id: 4, label: "4", x: 80, y: 323, tier: "basico", zoneSlug: "directory-forest" },
            { id: 5, label: "5", x: 170, y: 323, tier: "basico", zoneSlug: "directory-forest" },
            { id: 6, label: "6", x: 260, y: 323, tier: "basico", zoneSlug: "directory-forest" },
            { id: 7, label: "7", x: 350, y: 323, tier: "basico", zoneSlug: "directory-forest" },
            { id: 8, label: "8", x: 70, y: 233, tier: "basico", zoneSlug: "permission-mountains" },
            { id: 9, label: "9", x: 140, y: 233, tier: "basico", zoneSlug: "permission-mountains" },
            { id: 10, label: "10", x: 210, y: 233, tier: "basico", zoneSlug: "permission-mountains" },
            { id: 11, label: "11", x: 280, y: 233, tier: "basico", zoneSlug: "permission-mountains" },
            { id: 12, label: "12", x: 350, y: 233, tier: "basico", zoneSlug: "permission-mountains" },
            { id: 13, label: "13", x: 70, y: 143, tier: "basico", zoneSlug: "process-mines" },
            { id: 14, label: "14", x: 140, y: 143, tier: "basico", zoneSlug: "process-mines" },
            { id: 15, label: "15", x: 210, y: 143, tier: "basico", zoneSlug: "process-mines" },
            { id: 16, label: "16", x: 280, y: 143, tier: "basico", zoneSlug: "process-mines" },
            { id: 17, label: "17", x: 350, y: 143, tier: "basico", zoneSlug: "process-mines" },
            { id: 18, label: "★", x: 520, y: 58, tier: "basico", zoneSlug: "final-boss" },
        ],
    },
    {
        tier: "intermedio",
        emoji: "🐳",
        name: "Docker Planet",
        subtitle: "Próximamente",
        status: "coming_soon",
        offsetY: 380,
        height: 88,
        colorClass: "purple",
        castleX: 520,
        castleY: 40,
        bossLabel: "BOSS",
        zoneBands: [],
        nodes: [],
    },
    {
        tier: "avanzado",
        emoji: "🧱",
        name: "Git Castle",
        subtitle: "Próximamente",
        status: "coming_soon",
        offsetY: 488,
        height: 88,
        colorClass: "orange",
        castleX: 520,
        castleY: 40,
        bossLabel: "BOSS",
        zoneBands: [],
        nodes: [],
    },
];

export function worldPathD(nodes: WorldMapNode[], offsetY: number): string {
    if (nodes.length === 0) {
        return "";
    }

    const sorted = [...nodes].sort((a, b) => a.id - b.id);
    const first = sorted[0];
    let d = `M ${first.x} ${first.y + offsetY}`;

    for (let i = 1; i < sorted.length; i += 1) {
        const prev = sorted[i - 1];
        const curr = sorted[i];
        const midX = (prev.x + curr.x) / 2;
        const midY = (prev.y + curr.y) / 2 + offsetY;
        const pull = i % 2 === 0 ? -18 : 18;
        d += ` Q ${midX} ${midY + pull} ${curr.x} ${curr.y + offsetY}`;
    }

    return d;
}

const ZONE_NODE_COLORS: Record<string, { fill: string; stroke: string }> = {
    "welcome-village": { fill: "#15803d", stroke: "#86efac" },
    "directory-forest": { fill: "#047857", stroke: "#6ee7b7" },
    "permission-mountains": { fill: "#475569", stroke: "#cbd5e1" },
    "process-mines": { fill: "#334155", stroke: "#94a3b8" },
    "final-boss": { fill: "#6d28d9", stroke: "#c4b5fd" },
};

export function overviewNodeColors(zoneSlug?: string): { fill: string; stroke: string } {
    return ZONE_NODE_COLORS[zoneSlug ?? "welcome-village"] ?? ZONE_NODE_COLORS["welcome-village"];
}

export function isWorldUnlocked(
    world: WorldMapWorld,
    isCompleted: (id: number) => boolean,
): boolean {
    if (world.status === "coming_soon") {
        return false;
    }

    if (world.tier === "basico") {
        return true;
    }

    return false;
}

export function isWorldCompleted(
    world: WorldMapWorld,
    isCompleted: (id: number) => boolean,
): boolean {
    if (world.nodes.length === 0) {
        return false;
    }

    return world.nodes.every((node) => isCompleted(node.id));
}

export type WorldNodeVisualStatus =
    | "locked"
    | "current"
    | "completed"
    | "pending"
    | "lockout";

export function resolveNodeVisualStatus(
    id: number,
    worldUnlocked: boolean,
    isCompleted: (id: number) => boolean,
    isLockedOut: (id: number) => boolean,
    isPending: (id: number) => boolean,
    isUnlocked: (id: number) => boolean,
): WorldNodeVisualStatus {
    if (!worldUnlocked) {
        return "locked";
    }

    if (isCompleted(id)) {
        return "completed";
    }

    if (isLockedOut(id)) {
        return "lockout";
    }

    if (isPending(id)) {
        return "pending";
    }

    if (isUnlocked(id)) {
        return "current";
    }

    return "locked";
}

export function zoneLabelForLevel(
    worlds: WorldMapWorld[],
    levelId: number,
): string | null {
    for (const world of worlds) {
        const node = world.nodes.find((item) => item.id === levelId);

        if (!node?.zoneSlug) {
            continue;
        }

        const band = world.zoneBands.find((zone) => zone.slug === node.zoneSlug);

        if (band) {
            return band.name;
        }

        if (node.zoneSlug === "final-boss") {
            return "Final Boss";
        }
    }

    return null;
}
