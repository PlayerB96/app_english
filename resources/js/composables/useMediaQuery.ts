import { onBeforeUnmount, onMounted, ref, type Ref } from "vue";

export function useMediaQuery(query: string): Ref<boolean> {
    const matches = ref(
        typeof window !== "undefined" && window.matchMedia(query).matches,
    );

    let media: MediaQueryList | null = null;
    let update: (() => void) | null = null;

    onMounted(() => {
        media = window.matchMedia(query);
        update = (): void => {
            matches.value = media?.matches ?? false;
        };

        update();
        media.addEventListener("change", update);
    });

    onBeforeUnmount(() => {
        if (media && update) {
            media.removeEventListener("change", update);
        }
    });

    return matches;
}
