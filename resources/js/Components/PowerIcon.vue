<script setup lang="ts">
import { Flame } from "@lucide/vue";
import { computed } from "vue";

const props = withDefaults(
    defineProps<{
        sizeClass?: string;
        animated?: boolean;
    }>(),
    {
        sizeClass: "h-4 w-4",
        animated: false,
    },
);

const graphicClass = computed(() => [props.sizeClass, "power-icon-graphic"]);
</script>

<template>
    <span
        class="power-icon"
        :class="{ 'power-icon-animated': animated }"
        aria-hidden="true"
    >
        <Flame :class="graphicClass" />
    </span>
</template>
