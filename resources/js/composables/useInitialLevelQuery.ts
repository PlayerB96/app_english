import { onMounted } from "vue";

export function parseLevelQueryParam(): number | null {
    if (typeof window === "undefined") {
        return null;
    }

    const raw = new URLSearchParams(window.location.search).get("level");

    if (!raw) {
        return null;
    }

    const id = Number.parseInt(raw, 10);

    return Number.isNaN(id) || id < 1 ? null : id;
}

export function useInitialLevelQuery(
    tryOpenLevel: (id: number) => void | Promise<void>,
): void {
    onMounted(() => {
        const id = parseLevelQueryParam();

        if (id === null) {
            return;
        }

        void tryOpenLevel(id);
    });
}
