import type { TierSlug } from "@/types/levels";

export interface WorldMapNode {
    id: number;
    label: string;
    x: number;
    y: number;
    tier: TierSlug;
}

export interface WorldMapWorld {
    tier: TierSlug;
    name: string;
    offsetY: number;
    colorClass: "blue" | "purple" | "orange";
    castleX: number;
    castleY: number;
    nodes: WorldMapNode[];
}

export const WORLD_MAP_HEIGHT = 580;
export const WORLD_MAP_WIDTH = 592;

export const WORLD_MAP_WORLDS: WorldMapWorld[] = [
    {
        tier: "basico",
        name: "Módulo Básico",
        offsetY: 0,
        colorClass: "blue",
        castleX: 520,
        castleY: 88,
        nodes: [
            { id: 1, label: "1", x: 56, y: 120, tier: "basico" },
            { id: 2, label: "2", x: 148, y: 72, tier: "basico" },
            { id: 3, label: "3", x: 240, y: 128, tier: "basico" },
            { id: 4, label: "4", x: 332, y: 64, tier: "basico" },
            { id: 5, label: "5", x: 424, y: 108, tier: "basico" },
        ],
    },
    {
        tier: "intermedio",
        name: "Módulo Intermedio",
        offsetY: 200,
        colorClass: "purple",
        castleX: 520,
        castleY: 88,
        nodes: [
            { id: 6, label: "1", x: 56, y: 128, tier: "intermedio" },
            { id: 7, label: "2", x: 148, y: 80, tier: "intermedio" },
            { id: 8, label: "3", x: 240, y: 120, tier: "intermedio" },
            { id: 9, label: "4", x: 332, y: 68, tier: "intermedio" },
            { id: 10, label: "5", x: 424, y: 112, tier: "intermedio" },
        ],
    },
    {
        tier: "avanzado",
        name: "Módulo Avanzado",
        offsetY: 400,
        colorClass: "orange",
        castleX: 520,
        castleY: 88,
        nodes: [
            { id: 11, label: "1", x: 56, y: 124, tier: "avanzado" },
            { id: 12, label: "2", x: 148, y: 76, tier: "avanzado" },
            { id: 13, label: "3", x: 240, y: 116, tier: "avanzado" },
            { id: 14, label: "4", x: 332, y: 64, tier: "avanzado" },
            { id: 15, label: "5", x: 424, y: 108, tier: "avanzado" },
        ],
    },
];

export function worldPathD(nodes: WorldMapNode[], offsetY: number): string {
    if (nodes.length === 0) {
        return "";
    }

    const first = nodes[0];
    let d = `M ${first.x} ${first.y + offsetY}`;

    for (let i = 1; i < nodes.length; i += 1) {
        const prev = nodes[i - 1];
        const curr = nodes[i];
        const cx = (prev.x + curr.x) / 2;
        d += ` Q ${cx} ${prev.y + offsetY} ${curr.x} ${curr.y + offsetY}`;
    }

    const last = nodes[nodes.length - 1];

    if (last) {
        d += ` L ${last.x + 96} ${last.y + offsetY - 20}`;
    }

    return d;
}

export function isWorldUnlocked(
    tier: TierSlug,
    isCompleted: (id: number) => boolean,
): boolean {
    if (tier === "basico") {
        return true;
    }

    if (tier === "intermedio") {
        return isCompleted(5);
    }

    return isCompleted(10);
}

export function isWorldCompleted(
    world: WorldMapWorld,
    isCompleted: (id: number) => boolean,
): boolean {
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