import type { SpeechSynthesisStatus } from "@/types/speech";
import { computed, onUnmounted, ref } from "vue";

function isBrowserSupported(): boolean {
    return typeof window !== "undefined" && "speechSynthesis" in window;
}

const MALE_VOICE_HINT =
    /fred|daniel|alex|david|james|mark|tom|aaron|nathan|lee|gordon|ralph|bruce|richard|male|compact.*en.*male/i;

const FEMININE_VOICE_RANK: Array<{ pattern: RegExp; score: number }> = [
    { pattern: /samantha/i, score: 100 },
    { pattern: /victoria/i, score: 96 },
    { pattern: /google.*english.*female|english united states.*female/i, score: 94 },
    { pattern: /microsoft.*jenny|jenny online/i, score: 92 },
    { pattern: /microsoft.*zira|zira/i, score: 90 },
    { pattern: /google uk english female|english united kingdom.*female/i, score: 88 },
    { pattern: /karen|moira|fiona|tessa|serena|kate|susan|allison|aria|sara|linda|heather|laura|sophie|emma|olivia|female|woman/i, score: 80 },
];

function voiceScore(voice: SpeechSynthesisVoice): number {
    if (MALE_VOICE_HINT.test(voice.name)) {
        return -1;
    }

    let score = 0;

    for (const { pattern, score: rank } of FEMININE_VOICE_RANK) {
        if (pattern.test(voice.name)) {
            score = Math.max(score, rank);
        }
    }

    if (voice.lang === "en-US") {
        score += 8;
    } else if (voice.lang.startsWith("en")) {
        score += 4;
    }

    if (!voice.localService) {
        score += 2;
    }

    return score;
}

function pickFeminineEnglishVoice(
    voices: SpeechSynthesisVoice[],
): SpeechSynthesisVoice | null {
    const english = voices.filter((voice) => voice.lang.startsWith("en"));

    if (english.length === 0) {
        return voices[0] ?? null;
    }

    const ranked = english
        .map((voice) => ({ voice, score: voiceScore(voice) }))
        .filter(({ score }) => score > 0)
        .sort((a, b) => b.score - a.score);

    return ranked[0]?.voice ?? english[0] ?? null;
}

function loadVoices(): SpeechSynthesisVoice[] {
    if (!isBrowserSupported()) {
        return [];
    }

    return window.speechSynthesis.getVoices();
}

export function useSpeechSynthesis() {
    const status = ref<SpeechSynthesisStatus>(
        isBrowserSupported() ? "idle" : "unsupported",
    );

    let utterance: SpeechSynthesisUtterance | null = null;

    if (isBrowserSupported() && loadVoices().length === 0) {
        window.speechSynthesis.onvoiceschanged = () => {
            loadVoices();
        };
    }

    const isSpeaking = computed(() => status.value === "speaking");

    function isSupported(): boolean {
        return isBrowserSupported();
    }

    function cancel(): void {
        if (!isBrowserSupported()) {
            return;
        }

        window.speechSynthesis.cancel();
        status.value = "idle";
        utterance = null;
    }

    function speak(text: string, lang = "en-US"): boolean {
        if (!isBrowserSupported()) {
            status.value = "unsupported";

            return false;
        }

        const trimmed = text.trim();

        if (!trimmed) {
            return false;
        }

        cancel();

        utterance = new SpeechSynthesisUtterance(trimmed);
        utterance.lang = lang;
        utterance.rate = 0.88;
        utterance.pitch = 1.05;
        utterance.volume = 1;

        const voice = pickFeminineEnglishVoice(loadVoices());

        if (voice) {
            utterance.voice = voice;
        }

        utterance.onend = () => {
            status.value = "idle";
            utterance = null;
        };

        utterance.onerror = () => {
            status.value = "idle";
            utterance = null;
        };

        status.value = "speaking";
        window.speechSynthesis.speak(utterance);

        return true;
    }

    function toggle(text: string, lang = "en-US"): void {
        if (status.value === "speaking") {
            cancel();

            return;
        }

        speak(text, lang);
    }

    onUnmounted(() => {
        cancel();
    });

    return {
        status,
        isSpeaking,
        isSupported,
        speak,
        cancel,
        toggle,
    };
}
