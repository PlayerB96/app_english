import { defineStore } from "pinia";
import { ref } from "vue";

/**
 * Store global de ejemplo. Los módulos de negocio (WS-005+) añadirán stores específicos aquí.
 */
export const useAppStore = defineStore("app", () => {
    const isNavOpen = ref(false);

    function toggleNav(): void {
        isNavOpen.value = !isNavOpen.value;
    }

    function closeNav(): void {
        isNavOpen.value = false;
    }

    return {
        isNavOpen,
        toggleNav,
        closeNav,
    };
});
