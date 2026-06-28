<script setup lang="ts">
import { Map, Sparkles } from "@lucide/vue";
</script>

<template>
    <div
        class="world-header-badge"
        aria-hidden="true"
    >
        <span class="world-header-badge__ring" />
        <span class="world-header-badge__ring world-header-badge__ring--delayed" />
        <span class="world-header-badge__orbit-dot" />
        <Map class="world-header-badge__map" />
        <span class="world-header-badge__spark-wrap">
            <Sparkles class="world-header-badge__spark" />
        </span>
    </div>
</template>
