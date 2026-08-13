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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  '#FFF3EE',
                    100: '#FFE4D6',
                    200: '#FFC5A8',
                    300: '#FFA07A',
                    400: '#FF7C4D',
                    500: '#F5703E',
                    600: '#E05520',
                    700: '#C04010',
                    800: '#9A3008',
                    900: '#7A2506',
                },
            },
            boxShadow: {
                'card':       '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-hover': '0 10px 25px -5px rgb(0 0 0 / 0.08), 0 8px 10px -6px rgb(0 0 0 / 0.04)',
            },
        },
    },

    plugins: [forms],
};
