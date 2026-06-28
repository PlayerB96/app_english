<script setup lang="ts">
import type { WorldTierSlug } from "@/types/world";
import {
    worldBossIcon,
    worldTierIcon,
    worldTierIconClass,
    worldZoneIcon,
    worldZoneIconClass,
} from "@/utils/worldIcons";
import { computed } from "vue";

const props = defineProps<{
    tier?: WorldTierSlug;
    zone?: string;
    boss?: boolean;
    sizeClass?: string;
    iconClass?: string;
}>();

const icon = computed(() => {
    if (props.boss) {
        return worldBossIcon();
    }

    if (props.zone) {
        return worldZoneIcon(props.zone);
    }

    if (props.tier) {
        return worldTierIcon(props.tier);
    }

    return worldTierIcon("basico");
});

const colorClass = computed(() => {
    if (props.iconClass) {
        return props.iconClass;
    }

    if (props.boss) {
        return worldZoneIconClass("final-boss");
    }

    if (props.zone) {
        return worldZoneIconClass(props.zone);
    }

    if (props.tier) {
        return worldTierIconClass(props.tier);
    }

    return "text-gray-500 dark:text-gray-400";
});
</script>

<template>
    <component
        :is="icon"
        :class="[sizeClass ?? 'h-4 w-4', 'shrink-0', colorClass]"
        aria-hidden="true"
    />
</template>
