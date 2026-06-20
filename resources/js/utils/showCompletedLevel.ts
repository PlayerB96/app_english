import type { QuizLevel, SpeakingLevel } from "@/types/levels";
import {
    stepDifficultyLabel,
    sublevelLabel,
    type StepDifficulty,
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

function difficultyBadge(difficulty: StepDifficulty): string {
    const colors: Record<StepDifficulty, string> = {
        facil: "background:#ecfdf5;color:#047857;",
        medio: "background:#fffbeb;color:#b45309;",
        dificil: "background:#fef2f2;color:#b91c1c;",
    };

    return `<span style="display:inline-block;margin-left:0.375rem;border-radius:9999px;padding:0.125rem 0.5rem;font-size:0.65rem;font-weight:600;${colors[difficulty]}">${stepDifficultyLabel(difficulty)}</span>`;
}

export async function showCompletedSpeakingLevel(
    moduleName: string,
    level: SpeakingLevel,
): Promise<void> {
    const theme = gameSwalTheme();

    const items = level.questions
        .map(
            (question) => `
            <li style="text-align:left;padding:0.75rem;border-radius:0.75rem;background:${theme.background === "#111827" ? "#1f2937" : "#f9fafb"};">
                <div style="font-size:0.875rem;font-weight:600;margin-bottom:0.25rem;">
                    ${escapeHtml(question.prompt)}${difficultyBadge(question.step_difficulty)}
                </div>
                <div style="font-size:0.875rem;opacity:0.85;">
                    ${escapeHtml(question.expected_translation)}
                </div>
            </li>
        `,
        )
        .join("");

    await Swal.fire({
        title: "¡Subnivel superado!",
        html: `
            <p style="margin-bottom:0.75rem;font-size:0.75rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:${theme.badgeText};">
                ${moduleName} · ${sublevelLabel(level.phase)}
            </p>
            <p style="margin-bottom:0.75rem;font-size:0.875rem;opacity:0.85;text-align:left;">
                Subnivel conquistado. Repaso de tus respuestas clave:
            </p>
            <ul style="display:flex;flex-direction:column;gap:0.5rem;max-height:20rem;overflow-y:auto;padding:0;margin:0;list-style:none;">
                ${items}
            </ul>
        `,
        icon: "success",
        confirmButtonText: "Seguir jugando",
        ...swalOptions(theme),
    });
}

export async function showCompletedQuizLevel(
    moduleName: string,
    level: QuizLevel,
): Promise<void> {
    const theme = gameSwalTheme();

    const items = level.questions
        .map((question) => {
            const answer = question.options[question.correct_index];

            return `
            <li style="text-align:left;padding:0.75rem;border-radius:0.75rem;background:${theme.background === "#111827" ? "#1f2937" : "#f9fafb"};">
                <div style="font-size:0.875rem;font-weight:600;margin-bottom:0.25rem;">
                    ${escapeHtml(question.prompt)}${difficultyBadge(question.step_difficulty)}
                </div>
                <div style="font-size:0.875rem;opacity:0.85;">
                    ${escapeHtml(answer)}
                </div>
            </li>
        `;
        })
        .join("");

    await Swal.fire({
        title: "¡Subnivel superado!",
        html: `
            <p style="margin-bottom:0.75rem;font-size:0.75rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:${theme.badgeText};">
                ${moduleName} · ${sublevelLabel(level.phase)}
            </p>
            <p style="margin-bottom:0.75rem;font-size:0.875rem;opacity:0.85;text-align:left;">
                Subnivel conquistado. Repaso de vocabulario clave:
            </p>
            <ul style="display:flex;flex-direction:column;gap:0.5rem;max-height:20rem;overflow-y:auto;padding:0;margin:0;list-style:none;">
                ${items}
            </ul>
        `,
        icon: "success",
        confirmButtonText: "Seguir jugando",
        ...swalOptions(theme),
    });
}
