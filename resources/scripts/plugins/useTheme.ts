import { useCallback, useEffect, useState } from 'react';

export const THEME_KEYS = ['gold', 'crimson', 'ocean', 'emerald', 'purple'] as const;
export type ThemeKey = (typeof THEME_KEYS)[number];

/** Representative swatch color (the 500 shade) for each theme's picker button. */
export const THEME_SWATCHES: Record<ThemeKey, string> = {
    gold: '#c98f1b',
    crimson: '#ef4444',
    ocean: '#3b82f6',
    emerald: '#10b981',
    purple: '#a855f7',
};

const STORAGE_KEY = 'aurex-theme';
const DEFAULT_THEME: ThemeKey = 'gold';

const isThemeKey = (v: unknown): v is ThemeKey =>
    typeof v === 'string' && (THEME_KEYS as readonly string[]).includes(v);

/** Applies the theme to <html> so the CSS variables take effect instantly. */
export function applyTheme(theme: ThemeKey): void {
    document.documentElement.setAttribute('data-theme', theme);
}

function getInitialTheme(): ThemeKey {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (isThemeKey(stored)) return stored;
    } catch {
        // localStorage unavailable — fall back to default.
    }
    return DEFAULT_THEME;
}

/**
 * Runtime UI theme. Persists to localStorage['aurex-theme'] and applies
 * `data-theme` on <html> immediately — no reload needed.
 */
export const useTheme = (): { theme: ThemeKey; setTheme: (t: ThemeKey) => void } => {
    const [theme, setThemeState] = useState<ThemeKey>(getInitialTheme);

    // Apply on mount (in case pre-hydration script didn't run).
    useEffect(() => {
        applyTheme(theme);
    }, []); // eslint-disable-line react-hooks/exhaustive-deps

    const setTheme = useCallback((t: ThemeKey) => {
        if (!isThemeKey(t)) return;
        setThemeState(t);
        // Apply synchronously — don't rely on useEffect timing.
        applyTheme(t);
        try {
            localStorage.setItem(STORAGE_KEY, t);
        } catch {
            // ignore write failures
        }
    }, []);

    return { theme, setTheme };
};

export default useTheme;
