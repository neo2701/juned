import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Hanken Grotesk', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                juned: {
                    900: '#002113',
                    800: '#003527',
                    700: '#064e3b',
                    600: '#006c49',
                    500: '#00714d',
                    400: '#6cf8bb',
                    300: '#6ffbbe',
                    200: '#bfc9c3',
                    100: '#f3f3f4',
                    text: '#404944',
                    dark: '#1a1c1c',
                },
            },
        },
    },

    plugins: [forms],
};
