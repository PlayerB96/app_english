<script setup lang="ts">
import type { Component } from "vue";

defineProps<{
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: Component;
    tone?: "blue" | "green" | "amber" | "violet";
}>();

const toneClasses: Record<string, string> = {
    blue: "bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300",
    green: "bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300",
    amber: "bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300",
    violet: "bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300",
};
</script>

<template>
    <div class="surface-card p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-muted">
                    {{ title }}
                </p>
                <p class="mt-1 text-2xl font-bold text-heading">
                    {{ value }}
                </p>
                <p
                    v-if="subtitle"
                    class="mt-1 text-xs text-muted"
                >
                    {{ subtitle }}
                </p>
            </div>
            <div
                v-if="icon"
                class="rounded-xl p-2.5"
                :class="toneClasses[tone ?? 'blue']"
            >
                <component :is="icon" class="h-5 w-5" />
            </div>
        </div>
    </div>
</template>
