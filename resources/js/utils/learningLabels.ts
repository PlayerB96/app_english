export type StepDifficulty = "facil" | "medio" | "dificil";

export type TierSlug = "basico" | "intermedio" | "avanzado";

export const MODULE_LABELS: Record<TierSlug, string> = {
    basico: "Módulo Básico",
    intermedio: "Módulo Intermedio",
    avanzado: "Módulo Avanzado",
};

export function sublevelLabel(phase: number): string {
    return `Subnivel ${phase}`;
}

export function stepDifficultyFromIndex(questionIndex: number): StepDifficulty {
    if (questionIndex <= 1) {
        return "facil";
    }

    if (questionIndex === 2) {
        return "medio";
    }

    return "dificil";
}

export function stepDifficultyLabel(difficulty: StepDifficulty): string {
    return {
        facil: "Fácil",
        medio: "Medio",
        dificil: "Difícil",
    }[difficulty];
}

export function stepDifficultyBadgeClass(difficulty: StepDifficulty): string {
    return {
        facil: "bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-800",
        medio: "bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-800",
        dificil: "bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-800",
    }[difficulty];
}

export function sublevelIntensityHint(phase: number): string {
    return `Intensidad ${phase}/5 dentro del módulo`;
}
