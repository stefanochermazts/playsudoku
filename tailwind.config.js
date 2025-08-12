import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // System UI stack to avoid external font dependencies (no Google Fonts)
                sans: ['ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5', // Use for buttons on light bg (AA on white)
                    700: '#4338ca',
                },
                accent: {
                    100: '#ffe4e6',
                    200: '#fecdd3',
                    300: '#fda4af',
                    600: '#e11d48', // Accessible on white for emphasis
                },
                surface: {
                    50: '#fafaf9',
                    100: '#f5f5f4',
                    900: '#0b1020',
                },
            },
        },
    },

    plugins: [forms],
};
