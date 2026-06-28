export const POWER_UNIT = "poder";

export function powerBalanceLabel(amount: number): string {
    return `${amount} ${POWER_UNIT}`;
}

export function powerRewardTitle(amount: number): string {
    return `+${amount} ${POWER_UNIT} al completar`;
}

export function powerCostLabel(amount: number): string {
    return `${amount} ${POWER_UNIT}`;
}

export function powerDeltaLabel(amount: number): string {
    const sign = amount >= 0 ? "+" : "−";

    return `${sign}${Math.abs(amount)} ${POWER_UNIT}`;
}
