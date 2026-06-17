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
    blue: "bg-blue-50 text-blue-700",
    green: "bg-emerald-50 text-emerald-700",
    amber: "bg-amber-50 text-amber-700",
    violet: "bg-violet-50 text-violet-700",
};
</script>

<template>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    {{ title }}
                </p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                    {{ value }}
                </p>
                <p
                    v-if="subtitle"
                    class="mt-1 text-xs text-gray-500"
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
