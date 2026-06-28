import type { Component } from "vue";
import type { WorldTierSlug } from "@/types/world";
import {
    Briefcase,
    Container,
    Cpu,
    FolderTree,
    GitBranch,
    Home,
    Shield,
    Terminal,
} from "@lucide/vue";

const WORLD_ICONS: Record<WorldTierSlug, Component> = {
    basico: Terminal,
    intermedio: Container,
    avanzado: GitBranch,
};

const ZONE_ICONS: Record<string, Component> = {
    "welcome-village": Home,
    "directory-forest": FolderTree,
    "permission-mountains": Shield,
    "process-mines": Cpu,
    "final-boss": Briefcase,
};

const WORLD_ICON_CLASSES: Record<WorldTierSlug, string> = {
    basico: "text-blue-600 dark:text-blue-400",
    intermedio: "text-purple-600 dark:text-purple-400",
    avanzado: "text-orange-600 dark:text-orange-400",
};

const ZONE_ICON_CLASSES: Record<string, string> = {
    "welcome-village": "text-amber-600 dark:text-amber-400",
    "directory-forest": "text-emerald-600 dark:text-emerald-400",
    "permission-mountains": "text-stone-600 dark:text-stone-400",
    "process-mines": "text-slate-600 dark:text-slate-400",
    "final-boss": "text-violet-600 dark:text-violet-400",
};

export function worldTierIcon(tier: WorldTierSlug): Component {
    return WORLD_ICONS[tier];
}

export function worldZoneIcon(slug: string): Component {
    return ZONE_ICONS[slug] ?? Home;
}

export function worldBossIcon(): Component {
    return Briefcase;
}

export function worldTierIconClass(tier: WorldTierSlug): string {
    return WORLD_ICON_CLASSES[tier];
}

export function worldZoneIconClass(slug: string): string {
    return ZONE_ICON_CLASSES[slug] ?? "text-gray-500 dark:text-gray-400";
}
