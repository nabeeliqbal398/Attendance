import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['"Source Serif 4"', 'Georgia', ...defaultTheme.fontFamily.serif],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            fontFeatureSettings: {
                'cv': '"ss01", "cv11"',
                'tnum': '"tnum", "ss01"',
            },
            letterSpacing: {
                tightest: '-0.025em',
            },
            colors: {
                // Warm neutrals from design system (Notion-soft direction)
                ink: {
                    DEFAULT: '#2b2926',
                    2: '#57544f',
                    3: '#8b8780',
                },
                paper: {
                    DEFAULT: '#faf9f7',
                    2: '#f4f3ef',
                    panel: '#ffffff',
                    line: '#ece9e3',
                    'line-2': '#e3dfd8',
                },
                // Primary = purple accent (oklch 0.56 0.20 295 ≈ Tailwind violet)
                primary: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                    950: '#2e1065',
                },
                brand: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                    950: '#2e1065',
                },
            },
            borderRadius: {
                'sm': '6px',
                'md': '10px',
                'lg': '14px',
                'xl': '20px',
            },
            boxShadow: {
                'soft-1': '0 1px 0 rgba(20,16,10,0.04), 0 1px 2px rgba(20,16,10,0.04)',
                'soft-2': '0 4px 24px -8px rgba(20,16,10,0.10), 0 2px 8px -4px rgba(20,16,10,0.06)',
            },
        },
    },

    plugins: [forms, typography],
};
