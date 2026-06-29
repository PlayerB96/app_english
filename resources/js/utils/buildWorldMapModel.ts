import type { TierSlug } from "@/types/levels";
import type { WorldInfo, WorldLevel } from "@/types/world";

export interface WorldMapLevelNode {
    id: number;
    label: string;
    x: number;
    y: number;
    zoneSlug: string;
    isBoss: boolean;
}

export type WorldMilestoneKind = "zone" | "boss";

export interface WorldMapMilestone {
    slug: string;
    kind: WorldMilestoneKind;
    name: string;
    emoji: string;
    levelRange: string;
    commands: string[];
    gameplay: string | null;
    x: number;
    y: number;
    levelIds: number[];
    nodes: WorldMapLevelNode[];
    zoneViewBox: string;
}

export interface WorldMapModel {
    tier: TierSlug;
    width: number;
    height: number;
    orientation: WorldMapOrientation;
    milestones: WorldMapMilestone[];
}

export type WorldMapOrientation = "horizontal" | "vertical";

/** Lienzo de referencia; el SVG escala al 100% del contenedor sin scroll. */
const CANVAS_WIDTH = 1000;
const CANVAS_WIDTH_VERTICAL = 360;
const MILESTONE_CARD_WIDTH = 172;
const MILESTONE_CARD_HEIGHT = 92;
const HALF_CARD_W = MILESTONE_CARD_WIDTH / 2;
const HALF_CARD_H = MILESTONE_CARD_HEIGHT / 2;
const CANVAS_HEIGHT = 204;
const ROW_WAVE = 10;
const COL_WAVE = 12;
const MILESTONE_VERTICAL_GAP = 108;
const ZONE_NODE_SPACING = 112;

function clamp(value: number, min: number, max: number): number {
    return Math.min(max, Math.max(min, value));
}

function milestoneCenterX(index: number, count: number, canvasWidth: number): number {
    if (count <= 1) {
        return canvasWidth / 2;
    }

    const innerLeft = HALF_CARD_W + 4;
    const innerRight = canvasWidth - HALF_CARD_W - 4;
    const t = index / (count - 1);

    return innerLeft + t * (innerRight - innerLeft);
}

function milestoneCenterY(index: number, count: number, canvasHeight: number): number {
    if (count <= 1) {
        return canvasHeight / 2;
    }

    const innerTop = HALF_CARD_H + 8;
    const innerBottom = canvasHeight - HALF_CARD_H - 8;
    const t = index / (count - 1);

    return innerTop + t * (innerBottom - innerTop);
}

function verticalCanvasHeight(count: number): number {
    if (count <= 1) {
        return MILESTONE_CARD_HEIGHT + 24;
    }

    return MILESTONE_CARD_HEIGHT + (count - 1) * MILESTONE_VERTICAL_GAP + 16;
}

function buildZoneNodes(
    levelIds: number[],
    levels: WorldLevel[],
    slug: string,
    centerX: number,
    centerY: number,
    canvasWidth: number,
    canvasHeight: number,
): { nodes: WorldMapLevelNode[]; viewBox: string } {
    const count = levelIds.length;
    const aspect = canvasWidth / canvasHeight;

    if (count === 0) {
        return { nodes: [], viewBox: `0 0 ${canvasWidth} ${canvasHeight}` };
    }

    const totalWidth = (count - 1) * ZONE_NODE_SPACING;
    const clusterCenterX = clamp(centerX, 220, canvasWidth - 220);
    const startX = clusterCenterX - totalWidth / 2;
    const rowY = clamp(centerY, HALF_CARD_W, canvasHeight - HALF_CARD_W);

    const nodes: WorldMapLevelNode[] = levelIds.map((id, index) => {
        const level = levels.find((item) => item.id === id);
        const isBoss = level?.is_boss === true;

        return {
            id,
            label: isBoss ? "★" : String(id),
            x: startX + index * ZONE_NODE_SPACING,
            y: rowY + (index % 2 === 0 ? -22 : 22),
            zoneSlug: slug,
            isBoss,
        };
    });

    const viewWidth = clamp(totalWidth + 260, 380, canvasWidth);
    const viewHeight = viewWidth / aspect;
    const viewX = clamp(clusterCenterX - viewWidth / 2, 0, canvasWidth - viewWidth);
    const viewY = clamp(rowY - viewHeight / 2, 0, canvasHeight - viewHeight);

    return { nodes, viewBox: [viewX, viewY, viewWidth, viewHeight].join(" ") };
}

export function buildWorldMapModel(
    world: WorldInfo,
    levels: WorldLevel[],
    orientation: WorldMapOrientation = "horizontal",
): WorldMapModel {
    const worldLevels = levels.filter((level) => level.tier === world.tier);

    const zoneMilestones = world.zones.map((zone) => ({
        slug: zone.slug,
        kind: "zone" as const,
        name: zone.name,
        emoji: zone.emoji,
        levelRange: zone.level_range,
        commands: (zone.commands ?? []).slice(0, 3),
        gameplay: zone.gameplay ?? null,
        levelIds: worldLevels
            .filter((level) => level.zone === zone.slug && !level.is_boss)
            .map((level) => level.id)
            .sort((a, b) => a - b),
    }));

    const bossLevelIds = worldLevels
        .filter((level) => level.is_boss || level.zone === "final-boss")
        .map((level) => level.id)
        .sort((a, b) => a - b);

    const ordered: Array<Omit<WorldMapMilestone, "x" | "y" | "nodes" | "zoneViewBox">> = [
        ...zoneMilestones,
    ];

    if (bossLevelIds.length > 0) {
        ordered.push({
            slug: "final-boss",
            kind: "boss",
            name: world.boss?.title ?? "Final Boss",
            emoji: world.boss?.emoji ?? "★",
            levelRange: bossLevelIds.join("-"),
            commands: [],
            gameplay: world.boss?.description ?? null,
            levelIds: bossLevelIds,
        });
    }

    const count = ordered.length;
    const isVertical = orientation === "vertical";
    const canvasWidth = isVertical ? CANVAS_WIDTH_VERTICAL : CANVAS_WIDTH;
    const canvasHeight = isVertical ? verticalCanvasHeight(count) : CANVAS_HEIGHT;
    const centerX = canvasWidth / 2;
    const centerY = canvasHeight / 2;

    const milestones: WorldMapMilestone[] = ordered.map((milestone, index) => {
        const x = isVertical
            ? centerX + (index % 2 === 0 ? COL_WAVE : -COL_WAVE)
            : milestoneCenterX(index, count, canvasWidth);
        const y = isVertical
            ? milestoneCenterY(index, count, canvasHeight)
            : index % 2 === 0
                ? centerY + ROW_WAVE
                : centerY - ROW_WAVE;

        const { nodes, viewBox } = buildZoneNodes(
            milestone.levelIds,
            worldLevels,
            milestone.slug,
            x,
            y,
            canvasWidth,
            canvasHeight,
        );

        return { ...milestone, x, y, nodes, zoneViewBox: viewBox };
    });

    return {
        tier: world.tier,
        width: canvasWidth,
        height: canvasHeight,
        orientation,
        milestones,
    };
}

export const WORLD_MAP_CARD = {
    width: MILESTONE_CARD_WIDTH,
    height: MILESTONE_CARD_HEIGHT,
};
