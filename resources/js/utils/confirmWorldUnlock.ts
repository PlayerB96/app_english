import { gameSwalTheme } from "@/utils/swalGameTheme";
import { powerIconHtml } from "@/utils/lucideIconHtml";
import { POWER_UNIT } from "@/utils/powerLabels";
import { WORLD_TOTAL_LEVELS } from "@/types/world";
import Swal from "sweetalert2";

export interface ConfirmWorldUnlockOptions {
    cost: number;
    tokens: number;
}

export async function confirmWorldUnlock(
    options: ConfirmWorldUnlockOptions,
): Promise<boolean> {
    const { cost, tokens } = options;
    const theme = gameSwalTheme();
    const canAfford = tokens >= cost;

    const result = await Swal.fire({
        title: "Desbloquear Mundo",
        html: `
            <div class="reset-tier-modal">
                <div class="reset-tier-reward">
                    ${powerIconHtml(22, theme.powerUpText)}
                    <span class="reset-tier-reward__amount">−${cost}</span>
                    <span class="reset-tier-reward__unit">${POWER_UNIT}</span>
                </div>
                <p style="margin:0.75rem 0 0;font-size:0.8125rem;line-height:1.45;color:${theme.muted};">
                    Acceso permanente a <strong style="color:${theme.color};">${WORLD_TOTAL_LEVELS} niveles de Linux Kingdom</strong>:
                    terminal, permisos, procesos y entrevista final en inglés.
                </p>
                <p style="margin:0.5rem 0 0;font-size:0.75rem;line-height:1.45;color:${theme.muted};">
                    El ${POWER_UNIT} se gana gratis completando subniveles (+10) o reiniciando módulos en Práctica y Tracks.
                </p>
                <p style="margin:0.5rem 0 0;font-size:0.75rem;color:${theme.muted};">
                    Saldo actual: <strong style="color:${theme.color};">${tokens}</strong> ${POWER_UNIT}
                    ${canAfford ? "" : `<br>Faltan <strong>${cost - tokens}</strong> ${POWER_UNIT}`}
                </p>
            </div>
        `,
        icon: undefined,
        showCancelButton: true,
        confirmButtonText: canAfford ? `Desbloquear · −${cost}` : `${POWER_UNIT} insuficiente`,
        cancelButtonText: "Ahora no",
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

    return result.isConfirmed && canAfford;
}
