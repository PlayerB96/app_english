import { onMounted, onUnmounted, ref, type Ref } from "vue";

const DEFAULT_IDLE_MS = 60_000;
const SCROLL_DELTA = 10;
const DESKTOP_MEDIA = "(min-width: 768px)";

export function useAutoHideHeader(
    topBarRef: Ref<HTMLElement | null>,
    options?: {
        idleMs?: number;
        forceVisible?: () => boolean;
    },
) {
    const isVisible = ref(true);
    const topBarHeight = ref(0);

    let lastScrollY = 0;
    let idleTimer: ReturnType<typeof setTimeout> | null = null;
    let resizeObserver: ResizeObserver | null = null;
    let desktopMedia: MediaQueryList | null = null;

    function isDesktop(): boolean {
        return desktopMedia?.matches ?? false;
    }

    function updateTopBarHeight(): void {
        topBarHeight.value = topBarRef.value?.offsetHeight ?? 0;
    }

    function show(): void {
        isVisible.value = true;
    }

    function hide(): void {
        if (!isDesktop() || options?.forceVisible?.()) {
            return;
        }

        isVisible.value = false;
    }

    function resetIdleTimer(): void {
        if (idleTimer) {
            clearTimeout(idleTimer);
        }

        if (!isDesktop()) {
            return;
        }

        idleTimer = setTimeout(() => {
            hide();
        }, options?.idleMs ?? DEFAULT_IDLE_MS);
    }

    function onScroll(): void {
        if (!isDesktop()) {
            show();

            return;
        }

        if (options?.forceVisible?.()) {
            show();
            lastScrollY = window.scrollY;
            resetIdleTimer();

            return;
        }

        const currentY = window.scrollY;

        if (currentY <= SCROLL_DELTA) {
            show();
        } else if (currentY > lastScrollY + SCROLL_DELTA) {
            hide();
        } else if (currentY < lastScrollY - SCROLL_DELTA) {
            show();
        }

        lastScrollY = currentY;
        resetIdleTimer();
    }

    function onInteraction(): void {
        if (!isDesktop()) {
            return;
        }

        show();
        resetIdleTimer();
    }

    function onViewportChange(): void {
        if (!isDesktop()) {
            show();
        }

        updateTopBarHeight();
        resetIdleTimer();
    }

    onMounted(() => {
        desktopMedia = window.matchMedia(DESKTOP_MEDIA);
        updateTopBarHeight();
        lastScrollY = window.scrollY;

        window.addEventListener("scroll", onScroll, { passive: true });
        window.addEventListener("click", onInteraction, { passive: true });
        window.addEventListener("touchstart", onInteraction, { passive: true });
        window.addEventListener("keydown", onInteraction, { passive: true });

        desktopMedia.addEventListener("change", onViewportChange);

        if (topBarRef.value && typeof ResizeObserver !== "undefined") {
            resizeObserver = new ResizeObserver(() => updateTopBarHeight());
            resizeObserver.observe(topBarRef.value);
        }

        resetIdleTimer();
    });

    onUnmounted(() => {
        window.removeEventListener("scroll", onScroll);
        window.removeEventListener("click", onInteraction);
        window.removeEventListener("touchstart", onInteraction);
        window.removeEventListener("keydown", onInteraction);
        desktopMedia?.removeEventListener("change", onViewportChange);

        if (idleTimer) {
            clearTimeout(idleTimer);
        }

        resizeObserver?.disconnect();
    });

    return {
        isVisible,
        topBarHeight,
        show,
        updateTopBarHeight,
    };
}
