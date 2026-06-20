<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue";

defineProps<{
    src: string;
    label: string;
}>();

const rootRef = ref<HTMLElement | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
const isVisible = ref(false);
const isHovered = ref(false);
const prefersReducedMotion = ref(false);

let observer: IntersectionObserver | null = null;

function syncPlayback(): void {
    const video = videoRef.value;

    if (!video) {
        return;
    }

    if (prefersReducedMotion.value) {
        video.pause();
        video.currentTime = 0;
        return;
    }

    if (isVisible.value) {
        void video.play().catch(() => {
            // Autoplay blocked; first frame remains visible.
        });
        return;
    }

    video.pause();
}

onMounted(() => {
    prefersReducedMotion.value = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    observer = new IntersectionObserver(
        ([entry]) => {
            isVisible.value = entry?.isIntersecting ?? false;
            syncPlayback();
        },
        {
            threshold: 0.35,
            rootMargin: "0px 0px -5% 0px",
        },
    );

    if (rootRef.value) {
        observer.observe(rootRef.value);
    }

    syncPlayback();
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
    videoRef.value?.pause();
});
</script>

<template>
    <div
        ref="rootRef"
        class="welcome-showcase-video"
        :class="{
            'is-visible': isVisible,
            'is-hovered': isHovered,
            'is-static': prefersReducedMotion,
        }"
        :aria-label="label"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
        @focusin="isHovered = true"
        @focusout="isHovered = false"
    >
        <div class="welcome-showcase-video__viewport">
            <video
                ref="videoRef"
                class="welcome-showcase-video__media"
                autoplay
                muted
                loop
                playsinline
                preload="metadata"
                tabindex="-1"
            >
                <source
                    :src="src"
                    type="video/mp4"
                />
            </video>
        </div>

        <div
            class="welcome-showcase-video__sheen"
            aria-hidden="true"
        />
    </div>
</template>
