import { gameSwalTheme } from "@/utils/swalGameTheme";
import { lucideIconHtml, powerIconHtml } from "@/utils/lucideIconHtml";
import { POWER_UNIT } from "@/utils/powerLabels";
import Swal from "sweetalert2";

export interface ConfirmResetTierOptions {
    tierName: string;
    cost: number;
    resetCount: number;
    maxResets: number;
}

function resetTierModalHtml(
    theme: ReturnType<typeof gameSwalTheme>,
    tierName: string,
    cost: number,
    nextAttempt: number,
    maxResets: number,
): string {
    const attemptsLeft = attemptsRemaining(nextAttempt, maxResets);

    return `
        <div class="reset-tier-modal">
            <div class="reset-tier-reward">
                <span class="reset-tier-reward__coin" aria-hidden="true">
                    ${powerIconHtml(22, theme.powerUpText)}
                </span>
                <span class="reset-tier-reward__amount">−${cost}</span>
                <span class="reset-tier-reward__unit">${POWER_UNIT}</span>
            </div>

            <span
                class="reset-tier-badge"
                style="background:${theme.badgeBg};color:${theme.badgeText};"
            >
                ${tierName}
            </span>

            <ul class="reset-tier-meta" style="color:${theme.color};">
                <li class="reset-tier-meta__item">
                    <span class="reset-tier-meta__icon" style="color:${theme.waitLabel};">
                        ${lucideIconHtml("rotate-ccw", 16, theme.waitLabel)}
                    </span>
                    <span>Repasar <strong>5 subniveles</strong> completados</span>
                </li>
                <li class="reset-tier-meta__item">
                    <span class="reset-tier-meta__icon" style="color:${theme.hudLabel};">
                        ${lucideIconHtml("zap", 16, theme.hudLabel)}
                    </span>
                    <span>Reinicio <strong>${nextAttempt}/${maxResets}</strong></span>
                </li>
            </ul>

            <p class="reset-tier-footnote" style="color:${theme.muted};">
                ${attemptsLeft} oportunidad${attemptsLeft === 1 ? "" : "es"} restante${attemptsLeft === 1 ? "" : "s"} · sin deshacer
            </p>
        </div>
    `;
}

function attemptsRemaining(nextAttempt: number, maxResets: number): number {
    return Math.max(0, maxResets - nextAttempt);
}

export async function confirmResetTier(
    options: ConfirmResetTierOptions,
): Promise<boolean> {
    const { tierName, cost, resetCount: currentCount, maxResets } = options;
    const theme = gameSwalTheme();

    const result = await Swal.fire({
        title: "Repasar módulo",
        html: resetTierModalHtml(theme, tierName, cost, currentCount + 1, maxResets),
        icon: undefined,
        showCancelButton: true,
        confirmButtonText: "Reiniciar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: theme.confirmButtonColor,
        cancelButtonColor: "transparent",
        reverseButtons: true,
        focusCancel: true,
        background: theme.background,
        color: theme.color,
        customClass: {
            popup: "lockout-game-popup reset-tier-game-popup",
            closeButton: "lockout-game-close",
            title: "lockout-game-title",
            actions: "reset-tier-actions",
            confirmButton: "reset-tier-confirm-btn",
            cancelButton: "reset-tier-cancel-btn",
        },
    });

    return result.isConfirmed;
}
