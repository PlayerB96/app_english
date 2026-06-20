export function shuffleQuizOptions(
    options: readonly [string, string, string],
    correctIndex: number,
): { options: [string, string, string]; correct_index: number } {
    const entries = options.map((text, index) => ({
        text,
        isCorrect: index === correctIndex,
    }));

    for (let index = entries.length - 1; index > 0; index -= 1) {
        const swapIndex = Math.floor(Math.random() * (index + 1));
        const current = entries[index];
        entries[index] = entries[swapIndex];
        entries[swapIndex] = current;
    }

    return {
        options: entries.map((entry) => entry.text) as [string, string, string],
        correct_index: entries.findIndex((entry) => entry.isCorrect),
    };
}
