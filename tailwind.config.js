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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#fffdf0',
                    100: '#fff9d1',
                    200: '#fff3a3',
                    300: '#ffeb70',
                    400: '#fae23d',
                    500: '#FAE500',
                    600: '#d4c200',
                    700: '#a89a00',
                    800: '#7c7200',
                    900: '#504a00',
                    950: '#2d2800',
                },
                'blue-accent': {
                    50: '#eef4fc',
                    100: '#d3e2f7',
                    200: '#aec9f1',
                    300: '#78a8e7',
                    400: '#3d82da',
                    500: '#2554a7',
                    600: '#1d4390',
                    700: '#193776',
                    800: '#182f62',
                    900: '#182953',
                    950: '#101b38',
                },
                'pink-accent': {
                    50: '#fef1f4',
                    100: '#fde4ea',
                    200: '#fccdd9',
                    300: '#faa5ba',
                    400: '#f67397',
                    500: '#ea2a5d',
                    600: '#d91d52',
                    700: '#b71243',
                    800: '#9a123e',
                    900: '#841339',
                    950: '#4a051b',
                },
                dark: {
                    50: '#f8f9fa',
                    100: '#e9eaed',
                    200: '#c2c5cc',
                    300: '#9ba0ab',
                    400: '#747b8a',
                    500: '#4d5669',
                    600: '#2d3748',
                    700: '#1a202c',
                    800: '#121620',
                    900: '#0a0d14',
                },
                cream: '#F4F4F0',
            },
        },
    },

    plugins: [forms],
};
