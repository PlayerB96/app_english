import type { WorldMapLevelNode, WorldMapMilestone, WorldMapOrientation } from "@/utils/buildWorldMapModel";

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

export interface ZoneLevelNodeView {
    id: number;
    label: string;
    x: number;
    y: number;
    status: WorldNodeVisualStatus;
    interactive: boolean;
    selected: boolean;
    isBoss: boolean;
    isCurrent: boolean;
}

export type WorldMilestoneStatus = "locked" | "current" | "completed" | "lockout";

/**
 * Estado agregado de un hito a partir del progreso de sus niveles internos.
 * - completed: todos sus niveles completados
 * - lockout: algún nivel desbloqueado está bloqueado por fallo
 * - current: tiene al menos un nivel desbloqueado pendiente
 * - locked: ninguno desbloqueado todavía
 */
export function resolveMilestoneStatus(
    milestone: WorldMapMilestone,
    isCompleted: (id: number) => boolean,
    isUnlocked: (id: number) => boolean,
    isLockedOut?: (id: number) => boolean,
): WorldMilestoneStatus {
    if (milestone.levelIds.length === 0) {
        return "locked";
    }

    if (milestone.levelIds.every((id) => isCompleted(id))) {
        return "completed";
    }

    if (
        isLockedOut
        && milestone.levelIds.some(
            (id) => isLockedOut(id) && isUnlocked(id) && !isCompleted(id),
        )
    ) {
        return "lockout";
    }

    if (milestone.levelIds.some((id) => isUnlocked(id) && !isCompleted(id))) {
        return "current";
    }

    return "locked";
}

export function lockedLevelInMilestone(
    milestone: WorldMapMilestone,
    isLockedOut: (id: number) => boolean,
): number | null {
    for (const id of milestone.levelIds) {
        if (isLockedOut(id)) {
            return id;
        }
    }

    return null;
}

/** Camino serpenteante que une los centros de los hitos. */
export function milestonePathD(
    milestones: WorldMapMilestone[],
    orientation: WorldMapOrientation = "horizontal",
): string {
    if (milestones.length === 0) {
        return "";
    }

    const points = milestones.map((milestone) => ({ x: milestone.x, y: milestone.y }));
    const first = points[0];
    let d = `M ${first.x} ${first.y}`;

    for (let i = 1; i < points.length; i += 1) {
        const prev = points[i - 1];
        const curr = points[i];

        if (orientation === "vertical") {
            const midY = (prev.y + curr.y) / 2;
            d += ` C ${prev.x} ${midY} ${curr.x} ${midY} ${curr.x} ${curr.y}`;
        } else {
            const midX = (prev.x + curr.x) / 2;
            d += ` C ${midX} ${prev.y} ${midX} ${curr.y} ${curr.x} ${curr.y}`;
        }
    }

    return d;
}

/** Camino corto que une los nodos de nivel dentro de una zona expandida. */
export function levelPathD(nodes: WorldMapLevelNode[]): string {
    if (nodes.length === 0) {
        return "";
    }

    const sorted = [...nodes].sort((a, b) => a.id - b.id);
    const first = sorted[0];
    let d = `M ${first.x} ${first.y}`;

    for (let i = 1; i < sorted.length; i += 1) {
        const prev = sorted[i - 1];
        const curr = sorted[i];
        const midX = (prev.x + curr.x) / 2;
        const midY = (prev.y + curr.y) / 2;
        d += ` Q ${midX} ${midY} ${curr.x} ${curr.y}`;
    }

    return d;
}
