<script setup lang="ts">
import {
    BRAND_LOGO,
    BRAND_LOGO_ALT,
    BRAND_LOGO_ASPECT,
    BRAND_LOGO_HEIGHT,
    type BrandLogoSize,
} from "@/constants/brand";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        href?: string | null;
        size?: BrandLogoSize;
        clickable?: boolean;
    }>(),
    {
        href: "/",
        size: "md",
        clickable: true,
    },
);

const height = computed(() => BRAND_LOGO_HEIGHT[props.size]);
const width = computed(() => Math.round(height.value * BRAND_LOGO_ASPECT));
const isLink = computed(() => props.clickable && props.href);
</script>

<template>
    <component
        :is="isLink ? Link : 'span'"
        :href="isLink ? href! : undefined"
        class="app-logo inline-flex shrink-0 items-center"
        :aria-label="isLink ? `${BRAND_LOGO_ALT} — Inicio` : undefined"
    >
        <img
            :src="BRAND_LOGO.light.src"
            :srcset="BRAND_LOGO.light.srcSet"
            :alt="BRAND_LOGO_ALT"
            :height="height"
            :width="width"
            class="block h-auto w-auto max-w-none dark:hidden"
            decoding="async"
        >
        <img
            :src="BRAND_LOGO.dark.src"
            :srcset="BRAND_LOGO.dark.srcSet"
            :alt="BRAND_LOGO_ALT"
            :height="height"
            :width="width"
            class="hidden h-auto w-auto max-w-none dark:block"
            decoding="async"
        >
    </component>
</template>
