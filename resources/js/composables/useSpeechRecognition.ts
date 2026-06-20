import type { SpeechRecognitionStatus } from "@/types/speech";
import { onUnmounted, ref } from "vue";

interface BrowserSpeechRecognition extends EventTarget {
    lang: string;
    continuous: boolean;
    interimResults: boolean;
    maxAlternatives: number;
    start(): void;
    stop(): void;
    abort(): void;
    onresult: ((event: SpeechRecognitionResultEvent) => void) | null;
    onerror: ((event: SpeechRecognitionErrorEvent) => void) | null;
    onend: (() => void) | null;
}

interface SpeechRecognitionResultEvent extends Event {
    resultIndex: number;
    results: SpeechRecognitionResultList;
}

interface SpeechRecognitionErrorEvent extends Event {
    error: string;
    message?: string;
}

interface SpeechRecognitionResultList {
    length: number;
    item(index: number): SpeechRecognitionResult;
    [index: number]: SpeechRecognitionResult;
}

interface SpeechRecognitionResult {
    isFinal: boolean;
    length: number;
    item(index: number): SpeechRecognitionAlternative;
    [index: number]: SpeechRecognitionAlternative;
}

interface SpeechRecognitionAlternative {
    transcript: string;
    confidence: number;
}

function getSpeechRecognitionConstructor():
    | (new () => BrowserSpeechRecognition)
    | null {
    if (typeof window === "undefined") {
        return null;
    }

    const browserWindow = window as Window & {
        SpeechRecognition?: new () => BrowserSpeechRecognition;
        webkitSpeechRecognition?: new () => BrowserSpeechRecognition;
    };

    return (
        browserWindow.SpeechRecognition
        ?? browserWindow.webkitSpeechRecognition
        ?? null
    );
}

function permissionErrorMessage(error: unknown): string {
    if (error instanceof DOMException) {
        if (error.name === "NotAllowedError") {
            return "Permiso de micrófono denegado. Actívalo en la configuración del navegador e inténtalo de nuevo.";
        }

        if (error.name === "NotFoundError") {
            return "No se detectó ningún micrófono en este dispositivo.";
        }
    }

    return "No se pudo acceder al micrófono. Comprueba los permisos del navegador.";
}

function recognitionErrorMessage(error: string): string {
    if (error === "not-allowed" || error === "service-not-allowed") {
        return "Permiso de micrófono denegado. Actívalo en la configuración del navegador.";
    }

    if (error === "no-speech") {
        return "No se detectó voz. Acerca el micrófono y vuelve a intentarlo.";
    }

    if (error === "audio-capture") {
        return "No se pudo capturar audio del micrófono.";
    }

    if (error === "network") {
        return "El reconocimiento de voz requiere conexión a internet en este navegador.";
    }

    return "Error al escuchar el micrófono. Inténtalo de nuevo.";
}

export function useSpeechRecognition() {
    const status = ref<SpeechRecognitionStatus>("idle");
    const transcript = ref("");
    const interimTranscript = ref("");
    const errorMessage = ref<string | null>(null);

    let recognition: BrowserSpeechRecognition | null = null;
    let mediaStream: MediaStream | null = null;
    let shouldListen = false;

    function isSupported(): boolean {
        return getSpeechRecognitionConstructor() !== null
            && typeof navigator !== "undefined"
            && !!navigator.mediaDevices?.getUserMedia;
    }

    function releaseMicrophone(): void {
        mediaStream?.getTracks().forEach((track) => track.stop());
        mediaStream = null;
    }

    function resetTranscript(): void {
        transcript.value = "";
        interimTranscript.value = "";
    }

    async function requestMicrophonePermission(): Promise<boolean> {
        errorMessage.value = null;
        status.value = "requesting-permission";

        try {
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            status.value = "idle";

            return true;
        } catch (error) {
            status.value = "error";
            errorMessage.value = permissionErrorMessage(error);

            return false;
        }
    }

    async function start(lang: string): Promise<boolean> {
        if (!isSupported()) {
            status.value = "unsupported";
            errorMessage.value = "Tu navegador no soporta reconocimiento de voz. Usa Chrome o Edge en HTTPS.";

            return false;
        }

        const Constructor = getSpeechRecognitionConstructor();

        if (Constructor === null) {
            status.value = "unsupported";

            return false;
        }

        const permitted = await requestMicrophonePermission();

        if (!permitted) {
            return false;
        }

        recognition?.abort();
        resetTranscript();
        errorMessage.value = null;
        shouldListen = true;

        recognition = new Constructor();
        recognition.lang = lang;
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.maxAlternatives = 1;

        recognition.onresult = (event: SpeechRecognitionResultEvent) => {
            let interim = "";
            let finalText = transcript.value;

            for (let index = event.resultIndex; index < event.results.length; index++) {
                const result = event.results[index];
                const spoken = result[0]?.transcript ?? "";

                if (result.isFinal) {
                    finalText = `${finalText} ${spoken}`.trim();
                } else {
                    interim = `${interim} ${spoken}`.trim();
                }
            }

            transcript.value = finalText.trim();
            interimTranscript.value = interim.trim();
        };

        recognition.onerror = (event: SpeechRecognitionErrorEvent) => {
            if (event.error === "aborted") {
                return;
            }

            shouldListen = false;
            status.value = "error";
            errorMessage.value = recognitionErrorMessage(event.error);
        };

        recognition.onend = () => {
            if (shouldListen && recognition !== null) {
                try {
                    recognition.start();
                } catch {
                    shouldListen = false;
                    status.value = "idle";
                }

                return;
            }

            status.value = "idle";
        };

        try {
            recognition.start();
            status.value = "listening";

            return true;
        } catch {
            status.value = "error";
            errorMessage.value = "No se pudo iniciar el reconocimiento de voz.";

            return false;
        }
    }

    function stop(): string {
        shouldListen = false;
        recognition?.stop();
        recognition = null;
        releaseMicrophone();

        const combined = `${transcript.value} ${interimTranscript.value}`.trim();

        transcript.value = combined;
        interimTranscript.value = "";

        if (status.value === "listening") {
            status.value = "idle";
        }

        return combined;
    }

    function abort(): void {
        shouldListen = false;
        recognition?.abort();
        recognition = null;
        releaseMicrophone();
        status.value = "idle";
    }

    onUnmounted(() => {
        abort();
    });

    return {
        status,
        transcript,
        interimTranscript,
        errorMessage,
        isSupported,
        requestMicrophonePermission,
        start,
        stop,
        abort,
        resetTranscript,
    };
}
