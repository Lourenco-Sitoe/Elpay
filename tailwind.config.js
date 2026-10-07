import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eafaf0', 100: '#d3f2dd', 200: '#a7e5bb', 300: '#6fd598',
                    400: '#3cb972', 500: '#1da455', 600: '#168a45', 700: '#12703a',
                    800: '#0f5c31', 900: '#0d4a29',
                },
                /* Re-mapeamento global azul→verde Tutora (todas as telas usam blue-*) */
                blue: {
                    50: '#eafaf0', 100: '#d3f2dd', 200: '#a7e5bb', 300: '#6fd598',
                    400: '#3cb972', 500: '#1da455', 600: '#168a45', 700: '#12703a',
                    800: '#0f5c31', 900: '#0d4a29',
                },
                indigo: {
                    50: '#eafaf0', 100: '#d3f2dd', 200: '#a7e5bb', 300: '#6fd598',
                    400: '#3cb972', 500: '#1da455', 600: '#12703a', 700: '#0d4a29',
                    800: '#0f5c31', 900: '#0d4a29',
                },
            },
        },
    },

    plugins: [forms],
};
