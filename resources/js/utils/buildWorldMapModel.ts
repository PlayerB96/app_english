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
    milestones: WorldMapMilestone[];
}

const CANVAS_WIDTH = 680;
const MILESTONE_CARD_WIDTH = 224;
const MILESTONE_CARD_HEIGHT = 74;
/** Mitad de card + sombra SVG — margen mínimo en bordes del lienzo. */
const CARD_RADIUS = MILESTONE_CARD_HEIGHT / 2 + 14;
const MARGIN_TOP = CARD_RADIUS + 8;
const MARGIN_BOTTOM = CARD_RADIUS + 20;
const VERTICAL_STEP = 86;
const COLUMN_LEFT = 214;
const COLUMN_RIGHT = 466;
const COLUMN_CENTER = 340;
const ZONE_NODE_SPACING = 112;

function clamp(value: number, min: number, max: number): number {
    return Math.min(max, Math.max(min, value));
}

function buildZoneNodes(
    levelIds: number[],
    levels: WorldLevel[],
    slug: string,
    centerX: number,
    centerY: number,
    canvasHeight: number,
): { nodes: WorldMapLevelNode[]; viewBox: string } {
    const count = levelIds.length;
    const aspect = CANVAS_WIDTH / canvasHeight;

    if (count === 0) {
        return { nodes: [], viewBox: `0 0 ${CANVAS_WIDTH} ${canvasHeight}` };
    }

    const totalWidth = (count - 1) * ZONE_NODE_SPACING;
    const clusterCenterX = clamp(centerX, 220, CANVAS_WIDTH - 220);
    const startX = clusterCenterX - totalWidth / 2;
    const rowY = clamp(centerY, CARD_RADIUS + 24, canvasHeight - CARD_RADIUS - 24);

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

    const viewWidth = clamp(totalWidth + 260, 380, CANVAS_WIDTH);
    const viewHeight = viewWidth / aspect;
    const viewX = clamp(clusterCenterX - viewWidth / 2, 0, CANVAS_WIDTH - viewWidth);
    const viewY = clamp(rowY - viewHeight / 2, 0, canvasHeight - viewHeight);

    return { nodes, viewBox: [viewX, viewY, viewWidth, viewHeight].join(" ") };
}

export function buildWorldMapModel(
    world: WorldInfo,
    levels: WorldLevel[],
): WorldMapModel {
    const worldLevels = levels.filter((level) => level.tier === world.tier);

    const zoneMilestones = world.zones.map((zone) => ({
        slug: zone.slug,
        kind: "zone" as const,
        name: zone.name,
        emoji: zone.emoji,
        levelRange: zone.level_range,
        commands: (zone.commands ?? []).slice(0, 4),
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
    const span = count > 1 ? (count - 1) * VERTICAL_STEP : 0;
    const height = MARGIN_TOP + MARGIN_BOTTOM + span;

    const milestones: WorldMapMilestone[] = ordered.map((milestone, index) => {
        const fromBottom = index;
        const isLast = index === count - 1;
        const y = height - MARGIN_BOTTOM - fromBottom * VERTICAL_STEP;
        const x = isLast
            ? COLUMN_CENTER
            : fromBottom % 2 === 0
                ? COLUMN_LEFT
                : COLUMN_RIGHT;

        const { nodes, viewBox } = buildZoneNodes(
            milestone.levelIds,
            worldLevels,
            milestone.slug,
            x,
            y,
            height,
        );

        return { ...milestone, x, y, nodes, zoneViewBox: viewBox };
    });

    return {
        tier: world.tier,
        width: CANVAS_WIDTH,
        height,
        milestones,
    };
}

export const WORLD_MAP_CARD = {
    width: MILESTONE_CARD_WIDTH,
    height: MILESTONE_CARD_HEIGHT,
};
