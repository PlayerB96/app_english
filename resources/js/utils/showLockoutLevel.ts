import { sublevelLabel } from "@/utils/learningLabels";
import {
    formatLockoutGameTimer,
    formatLockoutUnlockAt,
} from "@/utils/formatLockout";
import { gameSwalTheme } from "@/utils/swalGameTheme";
import { lucideIconHtml, powerIconHtml } from "@/utils/lucideIconHtml";
import { POWER_UNIT } from "@/utils/powerLabels";
import Swal from "sweetalert2";

const COUNTDOWN_ID = "lockout-countdown";
const SKIP_BUTTON_ID = "skip-lockout-btn";
const CONFETTI_ID = "lockout-confetti";
const TOKEN_VALUE_ID = "lockout-token-value";
const TOKEN_DELTA_ID = "lockout-token-delta";

const CONFETTI_COLORS = ["#f59e0b", "#6366f1", "#22c55e", "#ec4899", "#fcd34d", "#38bdf8"];
const UNLOCK_CLOSE_DELAY_MS = 2800;

export interface ShowLockoutLevelOptions {
    moduleName: string;
    phase: number;
    lockedUntil: string;
    tokens: number;
    skipCost: number;
    onSkip: () => Promise<void>;
}

function actionCardStyle(
    theme: ReturnType<typeof gameSwalTheme>,
    variant: "wait" | "skip" | "skip-disabled",
): string {
    if (variant === "wait") {
        return `
            flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:0.25rem;min-width:0;padding:0.75rem 0.5rem;border-radius:0.625rem;
            border:1px solid ${theme.waitBorder};background:${theme.waitBg};
        `;
    }

    if (variant === "skip") {
        return `
            flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:0.25rem;min-width:0;padding:0.75rem 0.5rem;border-radius:0.625rem;
            border:1px solid ${theme.powerUpBorder};background:${theme.powerUpBg};
            color:${theme.powerUpText};cursor:pointer;
            box-shadow:inset 0 1px 0 rgba(255,255,255,0.08);
            transition:transform 0.12s ease,opacity 0.12s ease;
        `;
    }

    return `
        flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
        gap:0.25rem;min-width:0;padding:0.75rem 0.5rem;border-radius:0.625rem;
        border:1px dashed ${theme.powerUpDisabledBorder};background:${theme.powerUpDisabledBg};
        color:${theme.powerUpDisabledText};opacity:0.75;
    `;
}

function actionLabelStyle(
    theme: ReturnType<typeof gameSwalTheme>,
    variant: "wait" | "skip" | "skip-disabled",
): string {
    const color = variant === "wait"
        ? theme.waitLabel
        : variant === "skip"
            ? theme.powerUpText
            : theme.powerUpDisabledText;

    return `
        font-size:0.625rem;font-weight:800;letter-spacing:0.14em;text-transform:uppercase;
        color:${color};
    `;
}

function skipActionHtml(
    theme: ReturnType<typeof gameSwalTheme>,
    skipCost: number,
    tokens: number,
): string {
    const canAfford = tokens >= skipCost;

    if (canAfford) {
        return `
            <button
                id="${SKIP_BUTTON_ID}"
                type="button"
                style="${actionCardStyle(theme, "skip")}"
            >
                <span style="${actionLabelStyle(theme, "skip")}">Saltar</span>
                ${powerIconHtml(18, theme.powerUpText)}
                <span style="
                    font-size:0.6875rem;font-weight:700;padding:0.125rem 0.4375rem;border-radius:0.25rem;
                    background:${theme.powerUpChipBg};color:${theme.powerUpText};
                ">−${skipCost}</span>
            </button>
        `;
    }

    return `
        <div style="${actionCardStyle(theme, "skip-disabled")}">
            <span style="${actionLabelStyle(theme, "skip-disabled")}">Saltar</span>
            ${powerIconHtml(18, theme.powerUpDisabledText)}
            <span style="font-size:0.6875rem;font-weight:600;">−${skipCost}</span>
        </div>
    `;
}

function lockoutModalHtml(
    theme: ReturnType<typeof gameSwalTheme>,
    moduleName: string,
    phase: number,
    lockedUntil: string,
    skipCost: number,
    tokens: number,
): string {
    const unlockAt = formatLockoutUnlockAt(lockedUntil);
    const canAfford = tokens >= skipCost;

    return `
        <div style="margin:-0.25rem 0 0;">
            <div style="
                display:inline-block;margin:0 0 0.75rem;padding:0.25rem 0.625rem;border-radius:9999px;
                background:${theme.badgeBg};font-size:0.625rem;font-weight:700;letter-spacing:0.1em;
                text-transform:uppercase;color:${theme.badgeText};
            ">
                ${moduleName} · ${sublevelLabel(phase)}
            </div>

            <p style="margin:0 0 1rem;font-size:0.8125rem;line-height:1.45;color:${theme.muted};">
                Intenta fallido. Elige cómo continuar:
            </p>

            <div style="display:flex;align-items:stretch;gap:0.5rem;">
                <div style="${actionCardStyle(theme, "wait")}">
                    <span style="${actionLabelStyle(theme, "wait")}">Esperar</span>
                    <div
                        id="${COUNTDOWN_ID}"
                        style="
                            font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
                            font-size:1.125rem;font-weight:700;letter-spacing:0.05em;color:${theme.timer};
                        "
                    >
                        ${formatLockoutGameTimer(lockedUntil) ?? "00:00"}
                    </div>
                    <span style="font-size:0.625rem;font-weight:600;color:${theme.muted};">Gratis</span>
                </div>

                <div style="
                    display:flex;align-items:center;justify-content:center;
                    font-size:0.6875rem;font-weight:800;letter-spacing:0.08em;
                    color:${theme.divider};padding:0 0.125rem;
                ">O</div>

                ${skipActionHtml(theme, skipCost, tokens)}
            </div>

            <div style="
                display:flex;align-items:center;justify-content:space-between;gap:0.75rem;
                margin-top:0.75rem;padding:0.5rem 0.625rem;border-radius:0.5rem;
                background:${theme.footerBg};border:1px solid ${theme.footerBorder};
                font-size:0.6875rem;color:${theme.muted};
            ">
                <span>Saldo <strong style="color:${theme.color};">${tokens}</strong> ${POWER_UNIT}</span>
                <span>Auto <strong style="color:${theme.color};">${unlockAt}</strong></span>
            </div>

            ${canAfford ? "" : `
                <p style="margin:0.625rem 0 0;font-size:0.6875rem;text-align:center;color:${theme.powerUpDisabledText};">
                    Faltan ${skipCost - tokens} ${POWER_UNIT} para saltar
                </p>
            `}
        </div>
    `;
}

function skipProcessingHtml(
    theme: ReturnType<typeof gameSwalTheme>,
    skipCost: number,
): string {
    return `
        <div style="padding:1.25rem 0 0.75rem;text-align:center;">
            <div style="display:flex;justify-content:center;margin:0 auto 0.875rem;color:${theme.powerUpText};">
                ${lucideIconHtml("loader-2", 28, theme.powerUpText, "lockout-skip-spinner")}
            </div>
            <p style="margin:0 0 0.375rem;font-size:0.6875rem;font-weight:800;letter-spacing:0.14em;text-transform:uppercase;color:${theme.powerUpText};">
                Activando power-up
            </p>
            <p style="margin:0;font-size:0.8125rem;color:${theme.muted};">
                −${skipCost} ${POWER_UNIT}
            </p>
        </div>
    `;
}

function unlockSuccessHtml(
    theme: ReturnType<typeof gameSwalTheme>,
    moduleName: string,
    phase: number,
    tokens: number,
    skipCost: number,
): string {
    return `
        <div class="lockout-unlock-stage">
            <div id="${CONFETTI_ID}" class="lockout-confetti-burst" aria-hidden="true"></div>

            <div class="lockout-unlock-body">
                <div
                    class="lockout-unlock-icon"
                    style="background:${theme.successBg};border:2px solid ${theme.successBorder};color:${theme.success};"
                >
                    ${lucideIconHtml("unlock", 24, theme.success)}
                </div>

                <span
                    class="lockout-unlock-badge"
                    style="background:${theme.badgeBg};color:${theme.badgeText};"
                >
                    ${moduleName} · ${sublevelLabel(phase)}
                </span>

                <p class="lockout-unlock-message" style="color:${theme.success};">
                    ¡Subnivel desbloqueado!
                </p>

                <div class="lockout-token-balance">
                    <div class="lockout-token-row">
                        <span
                            id="${TOKEN_VALUE_ID}"
                            class="lockout-token-value"
                            style="color:${theme.powerUpText};"
                        >${tokens}</span>
                        <span class="lockout-token-label" style="color:${theme.muted};">${POWER_UNIT}</span>
                    </div>
                    <span
                        id="${TOKEN_DELTA_ID}"
                        class="lockout-token-delta"
                        style="color:${theme.powerUpText};"
                    >
                        −${skipCost}
                    </span>
                </div>
            </div>
        </div>
    `;
}

function skipErrorHtml(
    theme: ReturnType<typeof gameSwalTheme>,
    moduleName: string,
    phase: number,
    lockedUntil: string,
    skipCost: number,
    tokens: number,
): string {
    return `
        <div style="margin:-0.25rem 0 0;">
            <div style="
                margin:0 0 0.875rem;padding:0.625rem 0.75rem;border-radius:0.625rem;
                background:${theme.errorBg};border:1px solid ${theme.errorBorder};
                font-size:0.8125rem;color:${theme.errorText};text-align:center;
            ">
                No se pudo activar el power-up. Revisa tu saldo e inténtalo de nuevo.
            </div>
            ${lockoutModalHtml(theme, moduleName, phase, lockedUntil, skipCost, tokens)}
        </div>
    `;
}

function spawnConfetti(container: HTMLElement): void {
    for (let i = 0; i < 28; i += 1) {
        const piece = document.createElement("span");
        piece.className = "lockout-confetti-piece";
        piece.style.left = `${8 + Math.random() * 84}%`;
        piece.style.background = CONFETTI_COLORS[i % CONFETTI_COLORS.length] ?? "#f59e0b";
        piece.style.animationDelay = `${Math.random() * 0.4}s`;
        piece.style.animationDuration = `${0.9 + Math.random() * 0.6}s`;
        container.appendChild(piece);
    }
}

function animateTokenDeduction(
    valueEl: HTMLElement,
    deltaEl: HTMLElement,
    from: number,
    to: number,
): void {
    const duration = 700;
    const start = performance.now();

    deltaEl.style.opacity = "1";
    deltaEl.classList.add("lockout-token-delta-pop");

    const tick = (now: number): void => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - (1 - progress) ** 3;
        const current = Math.round(from + (to - from) * eased);

        valueEl.textContent = String(current);

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
}

function playUnlockAnimation(
    theme: ReturnType<typeof gameSwalTheme>,
    moduleName: string,
    phase: number,
    tokens: number,
    skipCost: number,
): void {
    Swal.update({
        title: "¡Desbloqueado!",
        html: unlockSuccessHtml(theme, moduleName, phase, tokens, skipCost),
        showCloseButton: false,
    });

    const confettiEl = document.getElementById(CONFETTI_ID);
    const tokenValueEl = document.getElementById(TOKEN_VALUE_ID);
    const tokenDeltaEl = document.getElementById(TOKEN_DELTA_ID);

    if (confettiEl) {
        spawnConfetti(confettiEl);
    }

    if (tokenValueEl && tokenDeltaEl) {
        animateTokenDeduction(tokenValueEl, tokenDeltaEl, tokens, tokens - skipCost);
    }
}

function bindSkipHandler(options: {
    theme: ReturnType<typeof gameSwalTheme>;
    moduleName: string;
    phase: number;
    lockedUntil: string;
    tokens: number;
    skipCost: number;
    onSkip: () => Promise<void>;
    clearCountdown: () => void;
    startCountdown: () => void;
    onSkipped: () => void;
    onRebind: () => void;
}): void {
    const skipButton = document.getElementById(SKIP_BUTTON_ID);

    skipButton?.addEventListener("click", () => {
        void (async () => {
            skipButton.setAttribute("disabled", "true");
            skipButton.style.opacity = "0.65";
            skipButton.style.pointerEvents = "none";

            Swal.update({
                html: skipProcessingHtml(options.theme, options.skipCost),
            });

            try {
                await options.onSkip();
                options.onSkipped();
                options.clearCountdown();
                playUnlockAnimation(
                    options.theme,
                    options.moduleName,
                    options.phase,
                    options.tokens,
                    options.skipCost,
                );

                window.setTimeout(() => {
                    Swal.close();
                }, UNLOCK_CLOSE_DELAY_MS);
            } catch {
                Swal.update({
                    title: "Pausa",
                    html: skipErrorHtml(
                        options.theme,
                        options.moduleName,
                        options.phase,
                        options.lockedUntil,
                        options.skipCost,
                        options.tokens,
                    ),
                    showCloseButton: true,
                });
                options.startCountdown();
                options.onRebind();
            }
        })();
    });
}

export async function showLockoutLevel(
    options: ShowLockoutLevelOptions,
): Promise<"skipped" | "dismissed"> {
    const {
        moduleName,
        phase,
        lockedUntil,
        tokens,
        skipCost,
        onSkip,
    } = options;

    const theme = gameSwalTheme();
    let intervalId: ReturnType<typeof setInterval> | null = null;
    let skipped = false;

    const clearCountdown = (): void => {
        if (intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    const startCountdown = (): void => {
        clearCountdown();

        const countdownEl = document.getElementById(COUNTDOWN_ID);

        if (!countdownEl) {
            return;
        }

        const tick = (): void => {
            const remaining = formatLockoutGameTimer(lockedUntil);

            if (!remaining) {
                countdownEl.textContent = "00:00";
                countdownEl.style.color = theme.success;
                clearCountdown();
                return;
            }

            countdownEl.textContent = remaining;
        };

        tick();
        intervalId = setInterval(tick, 1000);
    };

    const bindHandlers = (): void => {
        bindSkipHandler({
            theme,
            moduleName,
            phase,
            lockedUntil,
            tokens,
            skipCost,
            onSkip,
            clearCountdown,
            startCountdown,
            onSkipped: () => {
                skipped = true;
            },
            onRebind: bindHandlers,
        });
    };

    await Swal.fire({
        title: "Pausa",
        html: lockoutModalHtml(theme, moduleName, phase, lockedUntil, skipCost, tokens),
        showConfirmButton: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        background: theme.background,
        color: theme.color,
        customClass: {
            popup: "lockout-game-popup",
            closeButton: "lockout-game-close",
            title: "lockout-game-title",
        },
        didOpen: () => {
            startCountdown();
            bindHandlers();
        },
        willClose: clearCountdown,
    });

    return skipped ? "skipped" : "dismissed";
}
