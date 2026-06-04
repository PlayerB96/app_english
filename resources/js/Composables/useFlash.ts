import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import type { PageProps } from "@/types/auth";

export function useFlash() {
    const page = usePage<{ flash: PageProps["flash"] }>();

    const status = computed(() => page.props.flash?.status ?? null);

    const hasStatus = computed(() => status.value !== null && status.value !== "");

    return {
        status,
        hasStatus,
    };
}
