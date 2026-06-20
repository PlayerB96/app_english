export type Theme = "light" | "dark";

const STORAGE_KEY = "theme";

export function getPreferredTheme(): Theme {
    if (typeof window === "undefined") {
        return "light";
    }

    const stored = localStorage.getItem(STORAGE_KEY);

    if (stored === "light" || stored === "dark") {
        return stored;
    }

    return window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light";
}

export function applyTheme(theme: Theme): void {
    if (typeof document === "undefined") {
        return;
    }

    document.documentElement.classList.toggle("dark", theme === "dark");
    localStorage.setItem(STORAGE_KEY, theme);

    const meta = document.querySelector('meta[name="theme-color"]');

    if (meta) {
        meta.setAttribute("content", theme === "dark" ? "#030712" : "#2563eb");
    }
}

export function initThemeFromStorage(): Theme {
    const theme = getPreferredTheme();
    applyTheme(theme);

    return theme;
}
