<script setup lang="ts">
import { useFlash } from "@/Composables/useFlash";

const { status, hasStatus } = useFlash();
</script>

<template>
    <div
        v-if="hasStatus"
        role="status"
        class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
    >
        {{ status }}
    </div>
</template>
