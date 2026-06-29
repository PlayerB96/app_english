import type { TierSlug } from "@/types/levels";

/**
 * Capa de PRESENTACIÓN del mapa. El contenido (zonas, niveles, boss) llega del
 * backend; aquí solo viven colores y estilos para que un mismo componente
 * `WorldMap` pueda renderizar cualquier mundo cambiando únicamente el tema.
 */
export interface WorldMapZoneTheme {
    /** Relleno principal de la card del hito. */
    fill: string;
    /** Relleno interior / sombra de la card. */
    fillDark: string;
    /** Color de acento (texto, borde, glow). */
    accent: string;
    /** Relleno del nodo de nivel dentro de la zona. */
    node: string;
    /** Borde del nodo de nivel. */
    nodeStroke: string;
}

export interface WorldMapTheme {
    /** Stops del degradado de fondo del SVG (de arriba a abajo). */
    background: [string, string];
    /** Color del camino que une los hitos. */
    path: string;
    /** Tema visual del hito final (boss). */
    boss: WorldMapZoneTheme;
    /** Temas por slug de zona. */
    zones: Record<string, WorldMapZoneTheme>;
    /** Fallback cuando un slug de zona no tiene tema propio. */
    defaultZone: WorldMapZoneTheme;
}

const NEUTRAL_ZONE: WorldMapZoneTheme = {
    fill: "#1e293b",
    fillDark: "#334155",
    accent: "#94a3b8",
    node: "#475569",
    nodeStroke: "#64748b",
};

const BOSS_ZONE: WorldMapZoneTheme = {
    fill: "#4c1d95",
    fillDark: "#6d28d9",
    accent: "#c4b5fd",
    node: "#6d28d9",
    nodeStroke: "#c4b5fd",
};

const LINUX_KINGDOM_THEME: WorldMapTheme = {
    background: ["#0f172a", "#111827"],
    path: "#fcd34d",
    boss: BOSS_ZONE,
    defaultZone: NEUTRAL_ZONE,
    zones: {
        "welcome-village": {
            fill: "#14532d",
            fillDark: "#166534",
            accent: "#86efac",
            node: "#15803d",
            nodeStroke: "#86efac",
        },
        "directory-forest": {
            fill: "#064e3b",
            fillDark: "#065f46",
            accent: "#6ee7b7",
            node: "#047857",
            nodeStroke: "#6ee7b7",
        },
        "permission-mountains": {
            fill: "#334155",
            fillDark: "#475569",
            accent: "#cbd5e1",
            node: "#475569",
            nodeStroke: "#cbd5e1",
        },
        "process-mines": {
            fill: "#1e293b",
            fillDark: "#334155",
            accent: "#94a3b8",
            node: "#334155",
            nodeStroke: "#94a3b8",
        },
    },
};

const DOCKER_PLANET_THEME: WorldMapTheme = {
    background: ["#0c1a2b", "#0a1422"],
    path: "#38bdf8",
    boss: BOSS_ZONE,
    defaultZone: {
        fill: "#0e3a5f",
        fillDark: "#155e96",
        accent: "#7dd3fc",
        node: "#0369a1",
        nodeStroke: "#7dd3fc",
    },
    zones: {},
};

const GIT_CASTLE_THEME: WorldMapTheme = {
    background: ["#2a1407", "#1c0d04"],
    path: "#fb923c",
    boss: BOSS_ZONE,
    defaultZone: {
        fill: "#7c2d12",
        fillDark: "#9a3412",
        accent: "#fdba74",
        node: "#9a3412",
        nodeStroke: "#fdba74",
    },
    zones: {},
};

const WORLD_MAP_THEMES: Record<TierSlug, WorldMapTheme> = {
    basico: LINUX_KINGDOM_THEME,
    intermedio: DOCKER_PLANET_THEME,
    avanzado: GIT_CASTLE_THEME,
};

const DEFAULT_THEME: WorldMapTheme = LINUX_KINGDOM_THEME;

export function worldMapTheme(tier: TierSlug): WorldMapTheme {
    return WORLD_MAP_THEMES[tier] ?? DEFAULT_THEME;
}

export function zoneTheme(theme: WorldMapTheme, slug: string): WorldMapZoneTheme {
    if (slug === "final-boss") {
        return theme.boss;
    }

    return theme.zones[slug] ?? theme.defaultZone;
}
