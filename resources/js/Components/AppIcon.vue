<script setup lang="ts">
import {
    BRAND_ICON,
    BRAND_LOGO_ALT,
    BRAND_ICON_SIZE,
    type BrandIconSize,
} from "@/constants/brand";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        href?: string | null;
        size?: BrandIconSize;
        clickable?: boolean;
    }>(),
    {
        href: "/",
        size: "sm",
        clickable: true,
    },
);

const dimension = computed(() => BRAND_ICON_SIZE[props.size]);
const isLink = computed(() => props.clickable && props.href);
</script>

<template>
    <component
        :is="isLink ? Link : 'span'"
        :href="isLink ? href! : undefined"
        class="app-icon inline-flex shrink-0 items-center"
        :aria-label="isLink ? `${BRAND_LOGO_ALT} — Inicio` : undefined"
    >
        <img
            :src="BRAND_ICON.light[props.size]"
            :alt="BRAND_LOGO_ALT"
            :width="dimension"
            :height="dimension"
            class="block h-auto w-auto max-w-none dark:hidden"
            decoding="async"
        >
        <img
            :src="BRAND_ICON.dark[props.size]"
            :alt="BRAND_LOGO_ALT"
            :width="dimension"
            :height="dimension"
            class="hidden h-auto w-auto max-w-none dark:block"
            decoding="async"
        >
    </component>
</template>
