<script setup lang="ts">
defineProps<{
    intensity: number;
    max?: number;
}>();
</script>

<template>
    <div
        class="flex items-center gap-0.5"
        :title="`Intensidad ${intensity}/${max ?? 5}`"
    >
        <span
            v-for="dot in max ?? 5"
            :key="dot"
            class="h-1.5 w-1.5 rounded-full"
            :class="
                dot <= intensity
                    ? 'bg-blue-500 dark:bg-blue-400'
                    : 'bg-gray-200 dark:bg-gray-700'
            "
        />
    </div>
</template>
