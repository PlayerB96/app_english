import { applyTheme, getPreferredTheme, type Theme } from "@/utils/theme";
import { defineStore } from "pinia";
import { computed, ref } from "vue";

export const useThemeStore = defineStore("theme", () => {
    const theme = ref<Theme>("light");

    const isDark = computed(() => theme.value === "dark");

    function init(): void {
        theme.value = getPreferredTheme();
        applyTheme(theme.value);
    }

    function setTheme(next: Theme): void {
        theme.value = next;
        applyTheme(next);
    }

    function toggle(): void {
        setTheme(theme.value === "dark" ? "light" : "dark");
    }

    return {
        theme,
        isDark,
        init,
        setTheme,
        toggle,
    };
});
