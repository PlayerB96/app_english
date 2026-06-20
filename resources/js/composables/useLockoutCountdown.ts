import { onUnmounted, ref, watch, type WatchSource } from "vue";

export function useLockoutCountdown(active: WatchSource<boolean>) {
    const tick = ref(0);
    let timer: ReturnType<typeof setInterval> | null = null;

    function stop(): void {
        if (timer !== null) {
            clearInterval(timer);
            timer = null;
        }
    }

    function start(): void {
        if (timer !== null) {
            return;
        }

        timer = setInterval(() => {
            tick.value += 1;
        }, 1000);
    }

    watch(
        active,
        (hasActiveLockouts) => {
            if (hasActiveLockouts) {
                start();
            } else {
                stop();
            }
        },
        { immediate: true },
    );

    onUnmounted(stop);

    return { tick };
}
