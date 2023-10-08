import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                sans: ['VT323', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'cod-gray': {
                    DEFAULT: '#181818',
                    50: '#C5C5C5',
                    100: '#BBBBBB',
                    200: '#A7A7A7',
                    300: '#929292',
                    400: '#7E7E7E',
                    500: '#6A6A6A',
                    600: '#555555',
                    700: '#414141',
                    800: '#2C2C2C',
                    900: '#181818',
                    950: '#0A0A0A'
                },
            }
        },
    },

    plugins: [forms, typography],
};
