import type {
    LevelProgressMode,
    SublevelQuestionReview,
    SublevelReview,
} from "@/types/levels";
import {
    sublevelLabel,
} from "@/utils/learningLabels";
import { gameSwalTheme } from "@/utils/swalGameTheme";
import Swal from "sweetalert2";

function swalOptions(theme: ReturnType<typeof gameSwalTheme>) {
    return {
        background: theme.background,
        color: theme.color,
        confirmButtonColor: theme.confirmButtonColor,
    };
}

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

async function fetchSublevelReview(
    mode: LevelProgressMode,
    levelId: number,
): Promise<SublevelReview> {
    const response = await fetch(`/level-progress/${mode}/levels/${levelId}/review`, {
        headers: {
            Accept: "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
    });

    if (!response.ok) {
        throw new Error("review_failed");
    }

    return response.json() as Promise<SublevelReview>;
}

function attemptLabel(index: number, total: number): string {
    if (total <= 1) {
        return "Tu respuesta";
    }

    return `Intento ${index + 1}`;
}

function renderAttempts(
    question: SublevelQuestionReview,
    theme: ReturnType<typeof gameSwalTheme>,
): string {
    if (question.attempts.length === 0) {
        return `
            <p class="sublevel-review-note">
                Aprobada · sin registro detallado de intentos.
            </p>
        `;
    }

    if (question.attempts.length === 1 && question.attempts[0].is_correct) {
        const response = question.attempts[0].response_text.trim() || "—";

        return `
            <p class="sublevel-review-answer sublevel-review-answer--ok">
                Respondiste: ${escapeHtml(response)}
            </p>
        `;
    }

    return question.attempts
        .map((attempt, index) => {
            const isCorrect = attempt.is_correct;
            const statusColor = isCorrect ? theme.success : theme.errorText;
            const statusIcon = isCorrect ? "✓" : "✗";
            const statusText = isCorrect ? "Correcta" : "Incorrecta";
            const response = attempt.response_text.trim() || "—";
            const modifier = isCorrect
                ? "sublevel-review-attempt--ok"
                : "sublevel-review-attempt--bad";

            return `
                <div class="sublevel-review-attempt ${modifier}">
                    <span class="sublevel-review-attempt__icon" style="color:${statusColor};">${statusIcon}</span>
                    <div class="sublevel-review-attempt__body">
                        <span class="sublevel-review-attempt__label" style="color:${statusColor};">
                            ${statusText}${question.attempts.length > 1 ? ` · ${attemptLabel(index, question.attempts.length)}` : ""}
                        </span>
                        <span class="sublevel-review-attempt__text">${escapeHtml(response)}</span>
                    </div>
                </div>
            `;
        })
        .join("");
}

function renderQuestionCard(
    question: SublevelQuestionReview,
    mode: LevelProgressMode,
    theme: ReturnType<typeof gameSwalTheme>,
): string {
    const expected = mode === "speaking"
        ? question.expected_translation
        : question.expected_answer;
    const statusClass = question.final_correct
        ? "sublevel-review-item--ok"
        : "sublevel-review-item--bad";

    return `
        <li class="sublevel-review-item ${statusClass}">
            <div class="sublevel-review-item__head">
                <span class="sublevel-review-item__index">Pregunta ${question.question_index}</span>
                <span class="sublevel-review-item__prompt">${escapeHtml(question.prompt)}</span>
                <span class="sublevel-review-item__status">${question.final_correct ? "✓" : "…"}</span>
            </div>
            ${
                expected
                    ? `<p class="sublevel-review-item__expected">Esperado: ${escapeHtml(expected)}</p>`
                    : ""
            }
            ${renderAttempts(question, theme)}
        </li>
    `;
}

function buildReviewHtml(
    review: SublevelReview,
    moduleName: string,
    phase: number,
    mode: LevelProgressMode,
    theme: ReturnType<typeof gameSwalTheme>,
): string {
    const { summary } = review;
    const retryNote = summary.incorrect_attempts > 0
        ? `${summary.incorrect_attempts} intento${summary.incorrect_attempts === 1 ? "" : "s"} fallido${summary.incorrect_attempts === 1 ? "" : "s"}`
        : "Sin errores";

    const items = review.questions
        .map((question) => renderQuestionCard(question, mode, theme))
        .join("");

    return `
        <div class="sublevel-review-shell">
            <p class="sublevel-review-kicker">
                ${escapeHtml(moduleName)} · ${sublevelLabel(phase)}
            </p>
            <div class="sublevel-review-stats">
                <span class="sublevel-review-stat">${summary.total} preguntas</span>
                <span class="sublevel-review-stat sublevel-review-stat--ok">${summary.correct} aprobadas</span>
                <span class="sublevel-review-stat sublevel-review-stat--bad">${summary.incorrect_attempts} errores</span>
                <span class="sublevel-review-stat sublevel-review-stat--muted">${retryNote}</span>
            </div>
            <ul class="sublevel-review-list">
                ${items}
            </ul>
        </div>
    `;
}

async function showCompletedLevelReview(
    moduleName: string,
    phase: number,
    levelId: number,
    mode: LevelProgressMode,
): Promise<void> {
    const theme = gameSwalTheme();

    Swal.fire({
        title: "Cargando repaso…",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
        ...swalOptions(theme),
    });

    let review: SublevelReview;

    try {
        review = await fetchSublevelReview(mode, levelId);
    } catch {
        await Swal.fire({
            icon: "error",
            title: "No se pudo cargar el repaso",
            text: "Inténtalo de nuevo en unos segundos.",
            confirmButtonText: "Entendido",
            ...swalOptions(theme),
        });

        return;
    }

    await Swal.fire({
        title: "¡Subnivel superado!",
        html: buildReviewHtml(review, moduleName, phase, mode, theme),
        icon: "success",
        confirmButtonText: "Seguir jugando",
        width: "34rem",
        customClass: {
            popup: "sublevel-review-popup",
            htmlContainer: "sublevel-review-html",
        },
        ...swalOptions(theme),
    });
}

export async function showCompletedSpeakingLevel(
    moduleName: string,
    phase: number,
    levelId: number,
): Promise<void> {
    await showCompletedLevelReview(moduleName, phase, levelId, "speaking");
}

export async function showCompletedQuizLevel(
    moduleName: string,
    phase: number,
    levelId: number,
): Promise<void> {
    await showCompletedLevelReview(moduleName, phase, levelId, "quiz");
}
