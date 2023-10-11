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
                    
                    // blue-gray
                    // DEFAULT: '#64748B',
                    // 50: '#E4E7EC',
                    // 100: '#D8DDE3',
                    // 200: '#C0C8D2',
                    // 300: '#A9B3C1',
                    // 400: '#919EB0',
                    // 500: '#79899F',
                    // 600: '#64748B',
                    // 700: '#4D596A',
                    // 800: '#353E4A',
                    // 900: '#1E2229',
                    // 950: '#121519'


                    // dark-blue

                    // DEFAULT: '#2700D0',
                    // 50: '#C0B1FF',
                    // 100: '#AF9DFF',
                    // 200: '#8E74FF',
                    // 300: '#6D4BFF',
                    // 400: '#4C23FF',
                    // 500: '#2F00F9',
                    // 600: '#2700D0',
                    // 700: '#1C0098',
                    // 800: '#120060',
                    // 900: '#070028',
                    // 950: '#02000C'

                    // cod-gray

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
