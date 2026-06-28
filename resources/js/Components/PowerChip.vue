<script setup lang="ts">
import PowerIcon from "@/Components/PowerIcon.vue";

withDefaults(
    defineProps<{
        amount: number | string;
        sign?: "+" | "−" | "-";
        title?: string;
        size?: "sm" | "md";
    }>(),
    {
        sign: undefined,
        title: undefined,
        size: "sm",
    },
);
</script>

<template>
    <span
        class="power-chip"
        :class="size === 'md' ? 'power-chip-md' : 'power-chip-sm'"
        :title="title"
    >
        <PowerIcon :size-class="size === 'md' ? 'h-3 w-3' : 'h-2.5 w-2.5'" />
        <span v-if="sign">{{ sign }}{{ amount }}</span>
        <span v-else>{{ amount }}</span>
    </span>
</template>
