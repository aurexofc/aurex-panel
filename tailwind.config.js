const colors = require('tailwindcss/colors');

const gray = {
    50: 'hsl(216, 33%, 97%)',
    100: 'hsl(214, 15%, 91%)',
    200: 'hsl(210, 16%, 82%)',
    300: 'hsl(211, 13%, 65%)',
    400: 'hsl(211, 10%, 53%)',
    500: 'hsl(211, 12%, 43%)',
    600: 'hsl(209, 14%, 37%)',
    700: 'hsl(209, 18%, 30%)',
    800: 'hsl(209, 20%, 25%)',
    900: 'hsl(210, 24%, 16%)',
};

// Aurex brand colors as CSS variables so users can switch themes at runtime.
// Each theme defines --aurex-{50..900} as RGB triplets (see
// resources/scripts/themes/themes.css). The <alpha-value> placeholder keeps
// Tailwind opacity modifiers (e.g. bg-aurex-500/50) working.
const aurex = {
    50: 'rgb(var(--aurex-50) / <alpha-value>)',
    100: 'rgb(var(--aurex-100) / <alpha-value>)',
    200: 'rgb(var(--aurex-200) / <alpha-value>)',
    300: 'rgb(var(--aurex-300) / <alpha-value>)',
    400: 'rgb(var(--aurex-400) / <alpha-value>)',
    500: 'rgb(var(--aurex-500) / <alpha-value>)',
    600: 'rgb(var(--aurex-600) / <alpha-value>)',
    700: 'rgb(var(--aurex-700) / <alpha-value>)',
    800: 'rgb(var(--aurex-800) / <alpha-value>)',
    900: 'rgb(var(--aurex-900) / <alpha-value>)',
};

// Full-panel theme chrome: page background, card surfaces, borders and text.
// Each theme defines these as RGB triplets (see resources/scripts/themes/themes.css).
// Usage: bg-panel-bg, bg-panel-surface, text-panel-text, border-panel-border, …
const panel = {
    bg: 'rgb(var(--aurex-bg) / <alpha-value>)',
    'bg-2': 'rgb(var(--aurex-bg-2) / <alpha-value>)',
    surface: 'rgb(var(--aurex-surface) / <alpha-value>)',
    'surface-2': 'rgb(var(--aurex-surface-2) / <alpha-value>)',
    border: 'rgb(var(--aurex-border) / <alpha-value>)',
    text: 'rgb(var(--aurex-text) / <alpha-value>)',
    'text-dim': 'rgb(var(--aurex-text-dim) / <alpha-value>)',
};

module.exports = {
    content: [
        './resources/scripts/**/*.{js,ts,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                header: ['"IBM Plex Sans"', '"Roboto"', 'system-ui', 'sans-serif'],
            },
            colors: {
                black: '#131a20',
                // "primary" and "neutral" are deprecated, prefer the use of "aurex" and "gray"
                // in new code.
                primary: aurex,
                aurex: aurex,
                panel: panel,
                gray: gray,
                neutral: gray,
                cyan: colors.cyan,
            },
            fontSize: {
                '2xs': '0.625rem',
            },
            transitionDuration: {
                250: '250ms',
            },
            borderColor: theme => ({
                default: theme('colors.neutral.400', 'currentColor'),
            }),
        },
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ]
};
