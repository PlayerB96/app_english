<script setup lang="ts">
import { useFlash } from "@/Composables/useFlash";
import { onBeforeUnmount, ref, watch } from "vue";

const FLASH_DISMISS_MS = 5000;

const { status, hasStatus } = useFlash();
const visible = ref(false);

let dismissTimer: ReturnType<typeof setTimeout> | null = null;

function clearDismissTimer(): void {
    if (dismissTimer !== null) {
        clearTimeout(dismissTimer);
        dismissTimer = null;
    }
}

watch(
    hasStatus,
    (show) => {
        clearDismissTimer();

        if (!show) {
            visible.value = false;
            return;
        }

        visible.value = true;
        dismissTimer = setTimeout(() => {
            visible.value = false;
            dismissTimer = null;
        }, FLASH_DISMISS_MS);
    },
    { immediate: true },
);

onBeforeUnmount(clearDismissTimer);
</script>

<template>
    <Transition name="flash-fade">
        <div
            v-if="hasStatus && visible"
            role="status"
            class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950/50 dark:text-green-200"
        >
            {{ status }}
        </div>
    </Transition>
</template>

<style scoped>
.flash-fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.flash-fade-leave-to {
    opacity: 0;
    transform: translateY(-0.25rem);
}
</style>
