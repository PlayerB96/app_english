export type SpeechRecognitionStatus =
    | "idle"
    | "requesting-permission"
    | "listening"
    | "error"
    | "unsupported";

export type SpeechCapturePhase = "idle" | "ready";
