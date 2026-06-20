<script setup lang="ts">
import { useThemeStore } from "@/Stores/useThemeStore";
import { Moon, Sun } from "@lucide/vue";
import { storeToRefs } from "pinia";

const themeStore = useThemeStore();
const { isDark } = storeToRefs(themeStore);
</script>

<template>
    <button
        type="button"
        class="btn-theme"
        :aria-label="isDark ? 'Activar modo claro' : 'Activar modo oscuro'"
        :title="isDark ? 'Modo claro' : 'Modo oscuro'"
        @click="themeStore.toggle()"
    >
        <Sun
            v-if="isDark"
            class="h-[1.125rem] w-[1.125rem]"
        />
        <Moon
            v-else
            class="h-[1.125rem] w-[1.125rem]"
        />
    </button>
</template>
